# Feature Spec — Admin Management Panel

**Stack:** React + Inertia under `/admin`. **Auth:** separate `admin` guard + role middleware
(spatie/laravel-permission). **Goal:** full operational control of the platform. Built across
[Phase 2](../03-roadmap.md) (listings) and [Phase 6](../03-roadmap.md) (operations).

## Access & roles

- Separate `/admin/login` on the `admin` guard (distinct from investor auth).
- Roles: **Super Admin**, **Manager**, **Finance**.

### Role → capability matrix

| Capability | Super Admin | Manager | Finance |
|-----------|:-----------:|:-------:|:-------:|
| Admin user management | ✅ | ❌ | ❌ |
| Listings & rebate rates | ✅ | ✅ | ❌ |
| Categories | ✅ | ✅ | ❌ |
| Investor management | ✅ | ✅ | 👁 view |
| KYC review (approve/reject) | ✅ | ✅ | ❌ |
| Payment confirmation | ✅ | 👁 view | ✅ |
| Investment management (view/filter) | ✅ | ✅ | ✅ |
| Payout processing (single + bulk) | ✅ | ❌ | ✅ |
| Exports (CSV/Excel) | ✅ | 👁 | ✅ |
| Analytics dashboard | ✅ | ✅ | ✅ |
| Manual/bulk email to investors | ✅ | ✅ | ✅ |
| Settings (addresses, templates) | ✅ | ❌ | ❌ |

*(👁 = read-only. Adjust with Bechakeena; enforce with policies/middleware regardless of UI.)*

## Listing management (Phase 2)

- CRUD listings: title, slug, category, description, batch size, target amount, funding cap,
  min/max investment, SEO fields.
- **Rebate rates:** add/edit multiple tenures (6/12 mo) with percentages (unique tenure per listing).
- Media: cover, gallery, `listing_documents`.
- Lifecycle: **publish / pause / close**; funding cap enforcement; edit/delete (soft delete).
- Editing rebate rates must **not** alter existing investments (they snapshot their terms).

## Investor management (Phase 6)

- List/search/filter users (name, email, phone, KYC status, account status).
- View full per-investor portfolio + history.
- Edit investor details; **flag** or **deactivate** suspicious accounts (blocks investing/login).
- Sensitive fields masked (show last 4 of NID/bank); full values only via explicit, audited reveal.

## KYC review (Phase 6)

- Queue of `submitted` KYC documents; view images via **signed private URLs**.
- **Approve** → `user.kyc_status = approved` + notify.
- **Reject** with reason → `kyc_status = rejected` + notify (investor may resubmit).

## Payment & investment management (Phase 6)

- View all investments; filter by listing / tenure / status / date range.
- **Confirm payment** → activates investment (transaction; sets maturity; recomputes caches;
  queues confirmation email + certificate). **Reject payment** with reason.
- Idempotent transitions (no double-activation).

## Payout processing (Phase 6)

- View payouts due (from matured investments); filter by date/status/listing.
- **Mark paid** (single): method, reference → `status = paid`, `paid_at`, `processed_by`.
- **Bulk payout**: select many → confirmation step showing count + total → process atomically,
  idempotently (already-paid rows skipped).
- Payout amount follows the confirmed **repayment model** (see [investment flow](03-investment-flow.md)).

## Exports (Phase 6)

- Investments and payouts → **CSV/Excel** (maatwebsite/excel), respecting current filters.
- Include reference, investor, listing, amount, tenure, rebate, status, dates.

## Analytics dashboard (Phase 6)

- Total funds raised (across listings); total active investors.
- Upcoming maturities + **payout liability** summary (money owed by date).
- Per-listing performance: amount raised, investor count, funding %, payout status.
- Numbers derived from raw data (reconcile against cached fields).

## Notifications & communication (Phase 7)

- Send manual email to investors — individual or bulk (queued per recipient).
- Admin alert emails on key events: large investment, KYC pending, payout due.

## Authorization & audit

- Every action gated by role middleware **and** policy — never rely on hidden UI alone.
- Record actor on state changes (`reviewed_by`, `confirmed_by`, `processed_by`) + timestamps.
- Consider an activity log for sensitive actions (KYC reveal, deactivate, bulk payout).

## Acceptance criteria

- [ ] Admin login is separate from investor login; roles enforce the capability matrix server-side.
- [ ] Manager creates a published listing with 6/12-mo rebate rates, media, and cap.
- [ ] KYC approve/reject updates status and notifies the investor; docs shown via signed URLs.
- [ ] Finance confirms a payment → investment activates with correct maturity + caches.
- [ ] Single and bulk payouts are idempotent and role-gated; already-paid rows are skipped.
- [ ] Investments/payouts export to CSV/Excel matching filters.
- [ ] Analytics totals reconcile with raw investment/payout data.
- [ ] A Finance admin cannot edit listings; a Manager cannot process payouts.
