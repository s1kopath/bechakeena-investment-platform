# 03 — Development Roadmap (Step by Step)

This is the build order. Work **phase by phase, top to bottom**. Each phase lists its goal,
concrete steps, deliverables, and a **Definition of Done (DoD)**. Do not start a phase until its
dependencies are green.

> Legend: 🎯 goal · ✅ deliverable · 🔑 DoD (acceptance) · 🔗 depends on

Estimates are relative effort (S / M / L), not calendar promises. Adjust once the team size is known.

---

## Phase 0 — Foundation & Tooling  · size L · 🔗 none

🎯 Turn the empty skeleton into a working hybrid app with both rendering stacks wired, DB
connected, conventions enforced, and CI running.

**Steps**
1. Configure `.env` for MySQL; create local + test databases; verify `php artisan migrate`.
2. Install and wire **Livewire 3** (publish config, add `@livewireStyles/@livewireScripts` or v3 auto-injection).
3. Install and wire **Inertia 2 + React 18**: `inertiajs/inertia-laravel`, `@inertiajs/react`,
   `@vitejs/plugin-react`; create `resources/views/app.blade.php` root, `resources/js/app.jsx`
   bootstrap, and a smoke-test Inertia page.
4. Configure **Tailwind 4** design tokens (colors, fonts, spacing) — define the Bechakeena brand palette once.
5. Set up tooling: **Pint** (PHP), **ESLint + Prettier** (JS/React), `.editorconfig` (present),
   pre-commit hook or CI check.
6. Split routes into `web.php`, `auth.php`, `admin.php`; load them in `bootstrap/app.php`.
7. Base layouts: public Blade layout, Inertia `DashboardLayout`, `AdminLayout` shells.
8. Set up **queue** (database driver), **mail** (Mailpit locally), **storage** disks (public + private).
9. Establish `app/Enums`, `app/Services`, `app/Actions` folders (empty, with a README each).
10. **CI pipeline**: install deps, run migrations, `pint --test`, `eslint`, `php artisan test`.
11. Seeders scaffold + `DatabaseSeeder` wiring; base factories for `User`.

✅ Running app serving a Blade public route AND an Inertia React route; green CI; documented
`README`/`docs` setup steps.
🔑 A fresh clone runs `composer setup && npm run build && php artisan serve` and shows both a Blade
page and an Inertia page; CI passes on a trivial PR.

---

## Phase 1 — Authentication & Accounts  · size L · 🔗 Phase 0

🎯 Investors can register, log in, reset passwords, use Google OAuth, manage their profile, and
submit KYC.

**Steps**
1. Migrations + `User` model updates per [data model](02-data-model.md) (KYC/bank/status fields, enums).
2. Registration (email + phone) with `FormRequest` validation; hash password; email verification.
3. Login / logout with session auth; "remember me"; rate limiting on auth routes.
4. Password reset flow (email link) using Laravel's built-in tokens.
5. **Google OAuth** via Socialite: `/auth/google/redirect`, `/auth/google/callback`; link/create
   user by `google_id` / email.
6. **Intended-URL redirect**: store the pre-auth target so a visitor coming from a listing detail
   page returns to it after login/registration.
7. Profile management (Inertia page in dashboard): edit name, NID/passport, bank details.
8. **KYC upload**: `kyc_documents` migration/model; upload to **private disk**; set
   `user.kyc_status = submitted`; block investing until `approved`.
9. Notifications scaffolding (`notifications` table) for later use.
10. Tests: registration, login, password reset, OAuth callback (mocked), KYC upload authorization.

✅ Full auth surface + profile + KYC submission.
🔑 A new user registers, verifies email, logs in, sets bank details, uploads KYC, and sees
`kyc_status = submitted`; Google login creates/links an account; unverified/rejected users are
correctly gated. See [auth spec](04-features/02-auth-accounts-kyc.md).

---

## Phase 2 — Admin Foundation & Listing Management  · size L · 🔗 Phase 0 (parallelizable with Phase 1)

🎯 Admins can log in with role-based access and fully manage listings, rebate rates, and media.

**Steps**
1. `admins` table + `admin` guard; separate `/admin/login`; session auth for the guard.
2. **spatie/laravel-permission** on the admin guard: `super_admin`, `manager`, `finance` roles;
   seed a super admin from env.
3. Admin Inertia shell (`AdminLayout`, nav, role-aware menu).
4. `categories` CRUD.
5. **Listings CRUD** (Inertia + controllers + FormRequests): create/edit/delete, publish/pause/close,
   funding cap, min/max investment.
6. **rebate_rates** management: add multiple tenures (6/12 mo) per listing with percentages.
7. Media: cover image, gallery, `listing_documents` upload.
8. Policies/role middleware: only `super_admin`/`manager` manage listings.
9. Tests: listing lifecycle, rebate-rate validation (unique tenure per listing), authorization by role.

✅ Working admin panel for listings & rebate configuration.
🔑 A manager creates a published listing with 6- and 12-month rebate rates, images, and a funding
cap; a finance-only admin is denied listing edits. See [admin spec](04-features/05-admin-panel.md).

---

## Phase 3 — Public Site & SEO  · size L · 🔗 Phase 2 (needs real listings)

🎯 Every public page is server-rendered, interactive where needed, and fully SEO-optimised.

**Steps**
1. **Homepage** (Blade): platform overview, featured listings (rebate/tenure/target/progress),
   "How It Works," trust signals, CTAs.
2. **Listings browse** (Livewire): category filter, tenure filter, search, sort, funding-progress
   bars, pagination.
3. **Listing detail** (Blade + Livewire): full batch info, rebate table, live funding progress,
   "Invest" CTA (routes to auth if guest, storing intended URL).
4. **About / FAQ / Contact** pages; contact form → `contact_messages` + admin email; office
   addresses (Dhaka / Sharjah / Dubai) from `settings`.
5. **SEO layer**: per-page `<title>`/meta, Open Graph/Twitter cards, canonical URLs, JSON-LD
   structured data (Organization + Product/Offer for listings), semantic HTML.
6. **XML sitemap** (`/sitemap.xml`) + `robots.txt`; auto-include published listings.
7. **Performance**: fragment/response caching for public pages, image optimization, CDN-ready
   asset URLs, lazy loading; verify no JS is required to render/index content.
8. Mobile-responsive pass across all breakpoints.
9. Tests: pages render 200 with expected meta; sitemap lists published listings only; guest
   "Invest" redirects to login with intended URL.

✅ Complete, indexable public marketing site.
🔑 Google-crawlable HTML for landing/listings/detail (verified via view-source with JS disabled);
sitemap valid; Lighthouse SEO ≥ 95, Core Web Vitals "good." See [public site spec](04-features/01-public-site.md).

---

## Phase 4 — Investment Flow  · size L · 🔗 Phases 1, 2, 3

🎯 A KYC-approved investor can invest end-to-end: choose tenure, see live rebate, accept a digital
agreement, submit manual payment, and get a certificate once confirmed.

**Steps**
1. `investments`, `agreements`, `payments` migrations/models/enums.
2. **RebateCalculator** service: given amount + tenure → rebate + total, with live preview
   (Inertia page, client mirrors server formula; server is authoritative).
3. Investment start (Inertia, in dashboard): select tenure, enter amount with min/max & funding-cap
   validation; block if KYC not approved or listing not `published`.
4. **AgreementGenerator** service: render the versioned agreement with investor + investment data;
   store `content_snapshot`.
5. Digital acceptance: record `accepted_at`, `accepted_ip`, `accepted_user_agent`; create the
   `investment` in `pending_payment` inside a DB transaction.
6. **Manual payment** capture: method (MFS/bank/card), reference, optional proof upload →
   `payments.status = pending`.
7. Admin confirmation path (finish in Phase 6): confirming a payment flips investment → `active`,
   sets `invested_at` + `maturity_date`, recomputes listing caches.
8. **PDF generation** (queued): signed agreement PDF + investment certificate; store to disk.
9. Idempotency + concurrency: guard funding-cap overshoot and double submits.
10. Tests: rebate math, min/max/cap validation, KYC gating, agreement snapshot, status transition,
    PDF generation.

✅ Full invest flow through payment submission + certificate.
🔑 An approved investor invests within min/max and cap, accepts the agreement, submits a payment,
and — once a Finance admin confirms — the investment is `active` with a certificate PDF and correct
maturity date. **Resolve the repayment model** (principal+rebate vs rebate-only) here. See
[investment flow spec](04-features/03-investment-flow.md).

---

## Phase 5 — Investor Dashboard  · size M · 🔗 Phase 4

🎯 Investors see and manage their portfolio.

**Steps**
1. **Portfolio summary**: total invested, expected rebate, active count, upcoming maturities.
2. **Investment history**: filterable list with status (active/matured/paid_out/cancelled).
3. Re-download signed agreement and certificate per investment.
4. **Payout tracker**: scheduled date + actual status per investment (reads `payouts`).
5. **Notifications** center (database channel): maturity alerts, payout confirmations, new-listing
   alerts; unread badges.
6. Ownership authorization on every query (investor sees only their own data).
7. Tests: portfolio aggregates correct; cross-user access forbidden; downloads authorized.

✅ Complete investor dashboard.
🔑 An investor with multiple investments sees accurate totals, filters history, downloads their
documents, and cannot access another user's records. See [dashboard spec](04-features/04-investor-dashboard.md).

---

## Phase 6 — Payout & Admin Operations  · size L · 🔗 Phases 4, 5

🎯 Admins run day-to-day operations: KYC review, payment confirmation, investor management, payout
processing, exports, analytics.

**Steps**
1. **Investor management**: list/search/filter users; view full per-investor portfolio; edit
   details; flag/deactivate.
2. **KYC review**: view documents (signed private URLs), approve/reject with reason → updates
   `user.kyc_status` + notifies investor.
3. **Payment confirmation**: confirm/reject `payments`; confirming activates the investment
   (transaction, cache recompute) — completes the Phase 4 handoff.
4. **Investment management**: view all investments; filter by listing/tenure/status/date range.
5. **Payout processing**: mark payouts paid (single); **bulk payout** with a confirmation step;
   status transitions guarded.
6. **Exports**: investments and payouts to **CSV/Excel** (maatwebsite/excel).
7. **Analytics dashboard**: total funds raised, active investors, upcoming maturities & payout
   liability, per-listing performance.
8. Role gating: Finance owns payments/payouts/exports; Manager owns listings/investors/KYC;
   Super Admin owns everything + admin user management.
9. Tests: KYC approve/reject flow, payment→activation, bulk payout idempotency, export contents,
   analytics totals, role authorization matrix.

✅ Full operational admin panel.
🔑 A Finance admin confirms a payment (investment goes active), later marks its payout paid (single
+ bulk), and exports a correct CSV; a Manager approves KYC; analytics numbers reconcile with raw
data. See [admin spec](04-features/05-admin-panel.md).

---

## Phase 7 — Notifications & Communication  · size M · 🔗 Phases 4–6

🎯 Automated + manual email keeps investors and admins informed.

**Steps**
1. Transactional emails: investment confirmation (with agreement summary), payout processed,
   **maturity reminder (7 days before)**, new-listing launch alert.
2. Scheduler jobs: nightly job to (a) transition matured investments, (b) create/schedule payouts,
   (c) send maturity reminders; register in `routes/console.php` / scheduler.
3. Admin alert emails: large investment, KYC pending, payout due.
4. **Manual email** from admin to investors (individual + bulk), queued.
5. Wire the client SMTP provider (SendGrid / Resend); verify SPF/DKIM.
6. Tests (Mail::fake): each notification triggers on the right event; reminder window correct;
   bulk send queues per recipient.

✅ Complete notification system.
🔑 Investing sends a confirmation; a payout marks-paid sends a receipt; a 7-day-out maturity fires
a reminder; an admin sends a bulk announcement — all via the client SMTP with passing SPF/DKIM. See
[notifications spec](04-features/06-notifications.md).

---

## Phase 8 — Hardening, QA & Launch  · size M · 🔗 all

🎯 Ship it safely.

**Steps**
1. **Security review**: authz matrix (user vs admin vs role), private-file access via signed URLs,
   encryption of sensitive fields, CSRF, rate limits, input validation, dependency audit. Run
   `/security-review`.
2. **Financial correctness audit**: reconcile `amount_raised`/`investor_count` caches vs raw sums;
   verify no double payouts; status-transition guards.
3. **Performance**: cache config/routes/views, queue tuning, DB indexes on hot columns
   (`investments.user_id/status`, `listings.status/slug`, `payments.status`), CDN, image sizing.
4. **QA pass**: end-to-end investor journey + admin journey on staging; cross-browser + mobile.
5. **Backups & monitoring**: automated DB + file backups, error tracking, uptime, log retention.
6. **SEO verification**: sitemap submitted, structured data valid, robots correct, canonical URLs.
7. **Docs**: update deployment runbook, admin how-to, and this roadmap's status.

✅ Production-ready platform.
🔑 Security review clean (or risks accepted in writing), backups restore-tested, monitoring live,
full journeys pass on staging, then production deploy. See [deployment](06-deployment.md).

---

## Dependency graph

```
Phase 0 ─┬─► Phase 1 (auth) ───────────┐
         └─► Phase 2 (admin+listings) ─┼─► Phase 3 (public+SEO) ─► Phase 4 (invest) ─► Phase 5 (dashboard)
                                       │                                    │
                                       └────────────────────────────────────┴─► Phase 6 (ops) ─► Phase 7 (notify) ─► Phase 8 (launch)
```

Phases 1 and 2 can run in parallel with two developers. Everything downstream of Phase 4 depends on
the investment schema being stable — freeze [02-data-model.md](02-data-model.md) before Phase 4.

## Open questions to resolve before the phase that needs them

| Question | Needed by | Owner |
|----------|-----------|-------|
| Repayment model: principal+rebate vs rebate-only | Phase 4 | Bechakeena |
| Exact agreement legal template + versioning policy | Phase 4 | Bechakeena legal |
| KYC required before *investing* vs before *account creation* (assumed: before investing) | Phase 1/4 | Bechakeena |
| Card payment: truly manual, or a gateway in v1? (assumed manual) | Phase 4 | Bechakeena |
| Rebate percentage semantics: per-tenure total vs annualized | Phase 2/4 | Bechakeena |
| SMS/OTP in scope? (assumed no, email only) | Phase 1/7 | Bechakeena |
| Hosting target (cloud vs shared) — affects queue/storage/cron | Phase 0/8 | Client |
