# 00 — Project Overview

## What we're building

The **Bechakeena Investment Platform** is a single unified Laravel web application that lets
external investors fund Bechakeena's bulk laptop procurement cycles and earn a fixed,
tenure-based rebate return. It combines a public marketing site and an authenticated investor
portal into one seamless product, plus an internal admin panel.

Bechakeena is an established B2B laptop wholesale platform operating across **Bangladesh,
Sharjah (UAE), and Dubai**, with mobile apps on Google Play and the App Store. This platform
extends that ecosystem to outside capital.

> **Global audience.** Investors may register from **anywhere in the world**, not just Bangladesh.
> Build country-agnostic: **no locale-specific validation** (phone accepts international/E.164
> formats), English UI, and keep an eye on currency/timezone assumptions. Money is currently modelled
> in **BDT** — revisit if multi-currency is ever required (business decision, not yet in scope).

## Core value proposition

- **For investors:** browse vetted investment "batches," pick a tenure (6 months / 1 year),
  invest, and earn a fixed rebate — backed by Bechakeena's 12+ year wholesale network.
- **For Bechakeena:** raise working capital for bulk procurement cycles with transparent,
  auditable agreements and payout tracking.

## The four integrated components

1. **Unified public site + portal** — SEO-optimised, server-rendered landing, listings, and
   detail pages; authenticated investors flow into a personal dashboard in the same app.
2. **Authentication & investor dashboard** — phone/email registration, KYC upload, portfolio
   tracking.
3. **Agreement & rebate management** — auto-generated digital agreements, tenure-based rebate
   configuration, payout tracking.
4. **Admin management panel** — control over listings, investors, agreements, rebate rates,
   and payout processing.

## Primary personas

| Persona | Description | Key needs |
|---------|-------------|-----------|
| **Visitor** | Unauthenticated prospect browsing the public site | Fast, trustworthy, SEO-discoverable pages; clear "how it works" |
| **Investor** | Registered, KYC-verified user | Browse listings, invest, track portfolio, download agreements & certificates, get paid |
| **Super Admin** | Platform owner | Everything, including admin user management and settings |
| **Manager** | Operations admin | Manage listings, investors, KYC approvals |
| **Finance** | Finance admin | Confirm payments, process payouts, export financial data |

## Key domain concepts

| Term | Meaning |
|------|---------|
| **Listing (Batch)** | An investment opportunity: a bulk procurement cycle with a target amount and funding cap |
| **Tenure** | The lock-in period an investor chooses (6 months or 1 year) |
| **Rebate rate** | The fixed return percentage for a given tenure on a given listing |
| **Investment** | A single investor's committed capital in one listing at one tenure |
| **Agreement** | The digital contract the investor accepts before investing; versioned and PDF-archived |
| **Certificate** | A downloadable PDF confirming a completed investment |
| **Maturity** | The date an investment's tenure ends and the rebate becomes payable |
| **Payout** | The disbursement of principal + rebate (or rebate) to the investor at maturity |
| **KYC** | Know-Your-Customer identity verification (NID / passport) required before first investment |
| **MFS** | Mobile Financial Services (bKash, Nagad, etc.) — a manual payment method |

## In scope (v1)

- Public marketing site with full SEO.
- Investor registration (email + phone), login/logout, password reset, Google OAuth.
- KYC document upload and admin review.
- Investment flow with live rebate calculation, digital agreement acceptance, manual payment,
  and PDF certificate.
- Investor dashboard: portfolio, history, payout tracker, notifications.
- Admin panel: listing management, investor management, KYC approval, investment & payout
  management, bulk payouts, CSV/Excel export, analytics.
- Transactional email (SendGrid / Resend / client SMTP).

## Explicitly out of scope (v1)

These are **not** in the signed proposal and must be treated as change requests if raised:

- Automated payment gateway / real-time settlement (payments are **manual + admin-confirmed**).
- Automatic rebate disbursement (payouts are **manually marked** by Finance).
- Secondary market / trading of investments between users.
- Native mobile apps (the existing Bechakeena apps are separate products).
- Multi-currency investing or FX handling.
- In-app chat / live support.
- SMS/OTP notifications (email only in v1, unless client provides an SMS gateway).

## Constraints & assumptions

- **Proposal validity:** features/scope valid until **2026-07-10**; changes after that may be revised.
- **Hosting** is client-provided (cloud or shared). Architecture must run on standard LAMP-style
  hosting but is designed cloud-first. See [06-deployment.md](06-deployment.md).
- **SMTP provider** is client-provided (SendGrid, Resend, or other).
- **Legal/compliance:** agreement templates and rebate terms are supplied/approved by Bechakeena.
  We build the mechanism; we do not author financial-legal terms.
- **Language:** English UI in v1 (Bangla localisation is a possible v2 add-on — build with i18n
  in mind but don't implement translations).

## Success criteria for v1

- A visitor can discover a listing via search engines, register, complete KYC, invest, accept an
  agreement, submit a manual payment, and receive a certificate — end to end.
- An admin can create a listing with tenure-based rebate rates, approve KYC, confirm a payment,
  and process a payout — end to end.
- All public pages are server-rendered and indexable; Core Web Vitals in the "good" range.
- Financial records (investments, agreements, payments, payouts) are complete and auditable.
