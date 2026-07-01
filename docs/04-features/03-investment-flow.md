# Feature Spec — Investment Flow

**Stack:** React + Inertia (inside the dashboard); server-authoritative logic in Services/Actions.
**Goal:** a KYC-approved investor commits capital to a listing end to end. Built in
[Phase 4](../03-roadmap.md). This is the platform's most correctness-critical flow — money, legal
agreements, and concurrency all meet here.

## Preconditions (all enforced server-side)

- User authenticated + email verified.
- `user.kyc_status = approved`.
- Listing `status = published` and not past `closes_at`.
- Requested amount within `min_investment` / `max_investment` and within remaining funding cap.

## Steps

### 1. Choose tenure & amount (live preview)
- Investor picks a tenure (6 / 12 mo) from the listing's active `rebate_rates`.
- Enters an amount; a **live rebate preview** shows rebate amount + total return.
- Client computes the preview for UX, but the **server (`RebateCalculator`) is authoritative** and
  recomputes on submit. Never trust client math.
- Validate min/max and remaining cap in real time and again on submit.

### 2. Review agreement
- `AgreementGenerator` renders the versioned agreement template with investor + investment details
  (amount, tenure, rebate %, maturity date, payout terms).
- Full agreement text shown for review; investor must scroll/acknowledge.

### 3. Digital acceptance
- On accept: within a **DB transaction**
  - re-validate all preconditions (KYC, listing status, cap);
  - create `investment` (`status = pending_payment`, snapshot `tenure_months` + `rebate_percentage`,
    compute `expected_rebate_amount` + `expected_total_return`, generate unique `reference`);
  - create `agreement` with `content_snapshot`, `accepted_at`, `accepted_ip`, `accepted_user_agent`.
- **Concurrency:** lock/recheck the listing's remaining cap so simultaneous investments can't
  overshoot the funding cap.

### 4. Manual payment
- Show payment instructions per method (**MFS / bank transfer / card**) sourced from `settings`.
- Investor submits: method, reference (txn id / sender number), optional proof image → creates
  `payment` (`status = pending`).
- Investment stays `pending_payment` until an admin confirms.

### 5. Confirmation & activation (admin side — [Phase 6](../03-roadmap.md))
- Finance admin confirms the payment → transaction:
  - `payment.status = confirmed`, `confirmed_by`, `confirmed_at`;
  - `investment.status = active`, set `invested_at = now`, `maturity_date = invested_at + tenure`;
  - recompute listing `amount_raised` + `investor_count`; auto-`funded`/`closed` if cap reached;
  - queue confirmation email + certificate generation.
- Rejecting a payment sets `payment.status = rejected` (+ reason), investment stays
  `pending_payment` (investor may resubmit) or is `cancelled` per policy.

### 6. Certificate
- On activation, a queued job renders a **PDF certificate** (reference, investor, listing, amount,
  tenure, rebate, maturity) → stored on disk, downloadable from the dashboard.
- Signed agreement PDF is likewise archived (`agreements.signed_pdf_path`).

## Services / Actions

- `RebateCalculator` — pure function: `(amount, rebatePercentage, tenure) → {rebate, total}`.
- `InvestInListing` action — orchestrates validation + investment/agreement creation in a transaction.
- `AgreementGenerator` — renders + snapshots the agreement; owns versioning.
- `ConfirmPayment` action — activation transition (used by admin).
- `GenerateCertificatePdf` / `GenerateAgreementPdf` — queued jobs.

## ⚠️ Open decision — repayment model

Blocks final formulas for `expected_total_return` and `payouts.amount`. Confirm with Bechakeena:

- **(A) Rebate only** — investor receives the rebate; principal handled per procurement terms.
- **(B) Principal + rebate** — investor receives `amount × (1 + rebate%)` at maturity.

Also confirm **rebate semantics**: is the stored percentage the *total* return for the tenure
(e.g. 8% over 6 months) or *annualized*? The `RebateCalculator` and agreement copy depend on this.

## Edge cases

- Funding cap reached mid-flow → block/adjust with a clear message; never overshoot.
- Listing paused/closed between preview and submit → reject with explanation.
- KYC revoked between steps → block at submit.
- Double-submit of acceptance or payment → idempotent (guard on status + unique constraints).
- Amount below min / above max / above remaining cap → validation error.
- Payment proof upload of a non-image / oversized file → rejected.

## Acceptance criteria

- [ ] Only KYC-approved, verified users can reach the flow for a published listing.
- [ ] Live preview matches server calculation exactly.
- [ ] Min/max and funding-cap validation enforced on submit under concurrency (no overshoot).
- [ ] Agreement is snapshotted with acceptance timestamp + IP; PDF archived.
- [ ] Investment is created `pending_payment`; payment recorded `pending`.
- [ ] Admin confirmation activates the investment, sets maturity, recomputes caches, and emails a
      confirmation + certificate.
- [ ] Certificate PDF is downloadable and accurate.
- [ ] Repayment model decision recorded and reflected in calculations + agreement text.
