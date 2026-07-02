# Feature Spec — Authentication, Accounts & KYC

**Stack:** Blade for auth screens (or lightweight Inertia); session auth; Socialite for Google.
**Goal:** secure sign-up/sign-in and identity verification gating investment. Built in
[Phase 1](../03-roadmap.md).

## Registration

- Fields: name, **email** (unique), **phone** (unique), password (confirmed).
- Validation via `FormRequest`: email format + uniqueness, phone format + uniqueness, password
  strength, terms acceptance.
- **Global audience:** phone validation is **country-agnostic** (international / E.164 — optional
  leading `+` then 7–15 digits, separators stripped). Do **not** add country-specific rules.
- On success: create `User` (`kyc_status = pending`, `status = active`), send email verification,
  log in.
- Rate-limit registration attempts.

## Login / logout

- Email **or** phone + password; "remember me"; secure session cookie.
- Throttle failed attempts (Laravel rate limiter) — lockout with backoff.
- Logout invalidates session + regenerates token.

## Password reset

- Standard Laravel flow: request link by email → tokened reset form → set new password → invalidate
  other sessions.

## Google OAuth (Socialite)

- Routes: `/auth/google/redirect`, `/auth/google/callback`.
- Callback logic:
  - Match by `google_id` → log in.
  - Else match by verified `email` → link `google_id` to existing account.
  - Else create a new user (mark email verified; `password` null; prompt to add phone if required).
- Handle denial/errors gracefully back to `/login` with a message.

## Intended-URL redirect (the "seamless" requirement)

- When a guest hits an auth-required action (e.g. Invest on `/listings/{slug}`), store the intended
  URL (Laravel `redirect()->intended()` / session) **before** redirecting to login.
- After login **or** registration **or** OAuth, send the user back to the intended URL; otherwise to
  `/dashboard`.
- Must survive the register-instead-of-login branch and the OAuth round-trip.

## Profile management (Inertia, in dashboard)

- Edit name, NID/passport number, bank details (account name/number, bank, branch, routing).
- Sensitive fields (NID, passport, bank account number) are **encrypted at rest** (Eloquent
  `encrypted` casts).
- Changing bank details after a payout is scheduled should warn/require re-confirmation.

## KYC upload & verification

- **Documents** (`kyc_documents`): type (NID / passport), document number, front image, optional
  back image, optional selfie.
- Uploads go to the **private disk** (never public); served to admins via **signed, expiring URLs**.
- On submit: `user.kyc_status = submitted`; investor notified it's under review.
- Admin review (see [admin spec](05-admin-panel.md)) sets `approved` or `rejected` (+ reason).
- **Gating:** investing is blocked unless `kyc_status = approved`. Assumed policy: KYC required
  **before first investment**, not before account creation (confirm — see roadmap open questions).
- Re-submission allowed after rejection.

## Email verification

- Verify email before investing (and before KYC submission, ideally). Unverified users can browse
  but not invest.

## Security requirements

- CSRF on all forms; HTTPS-only cookies in prod; `SameSite` cookies.
- Rate limiting on register/login/reset/OAuth.
- No sensitive data in logs; mask NID/bank in any admin list views (show last 4).
- Passwords hashed (bcrypt/argon2 per Laravel default).
- Authorization: a user may only view/edit **their own** profile and KYC.

## Acceptance criteria

- [ ] Register with email + phone; duplicate email/phone rejected; verification email sent.
- [ ] Login by email or phone; failed attempts throttled; logout clears session.
- [ ] Password reset works end to end.
- [ ] Google login creates a new account or links to an existing one by email.
- [ ] Intended-URL redirect returns the user to the originating listing after any auth path.
- [ ] Profile edits persist; sensitive fields encrypted at rest.
- [ ] KYC upload stores to private disk, sets `submitted`, and is admin-reviewable via signed URL.
- [ ] Investing is blocked until `kyc_status = approved`.
