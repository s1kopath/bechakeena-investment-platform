# Feature Spec — Investor Dashboard

**Stack:** React + Inertia. **Auth:** `auth` + `verified`. **Goal:** investors track their
portfolio, documents, payouts, and notifications. Built in [Phase 5](../03-roadmap.md).
**Rule:** every query is scoped to the authenticated user — no cross-user access, ever.

## Routes (Inertia pages)

| Route | Page |
|-------|------|
| `/dashboard` | Portfolio summary (home) |
| `/dashboard/investments` | Investment history (filterable) |
| `/dashboard/investments/{ref}` | Single investment detail + documents |
| `/dashboard/payouts` | Payout tracker |
| `/dashboard/profile` | Profile + bank + KYC (see auth spec) |
| `/notifications` | Notifications center |

## Portfolio summary

- **Total invested** (sum of active + matured principal).
- **Expected rebate** (sum of `expected_rebate_amount` for active).
- **Active investments** count; **upcoming maturities** (next N by date).
- Small charts optional (allocation by listing / tenure).
- Quick links: browse listings, complete KYC (if not approved), latest notifications.

## Investment history

- Table/list of all investments with: reference, listing, amount, tenure, rebate %, status,
  invested date, maturity date.
- **Filters:** status (active / matured / paid_out / cancelled / pending_payment), listing, tenure,
  date range; sort by date/amount/maturity.
- Row → investment detail.

## Investment detail

- Full terms (snapshotted rebate %, tenure, maturity, expected return).
- **Downloads:** signed agreement PDF, investment certificate PDF (authorized, from private disk).
- Payment status (pending / confirmed / rejected + reason).
- Payout status for this investment (scheduled date + actual).

## Payout tracker

- Per-investment: scheduled payout date (= maturity), status (`scheduled` / `processing` / `paid` /
  `failed`), paid date + reference when paid.
- Summary: total expected payouts, next payout date, total paid to date.

## Notifications center

- Database-channel notifications: maturity alerts, payout confirmations, new-listing alerts.
- Unread badge in nav; mark read/all-read; links deep into the relevant page.

## Authorization

- All controllers filter by `auth()->id()`; policies verify ownership on investment/agreement/payout
  document access.
- Direct access to another user's `{ref}` or document path returns 403/404.

## Acceptance criteria

- [ ] Portfolio totals reconcile with the user's raw investment/payout data.
- [ ] History filters and sorts correctly; only the user's records appear.
- [ ] Agreement + certificate downloads are authorized and correct.
- [ ] Payout tracker reflects scheduled vs actual accurately.
- [ ] Notifications show, mark read, and deep-link correctly.
- [ ] Attempting to access another user's investment/document is forbidden.
