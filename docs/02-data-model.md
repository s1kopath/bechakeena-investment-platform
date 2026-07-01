# 02 — Data Model

This is the backbone of the platform. Build migrations in the order implied by the foreign keys.
All monetary columns are `DECIMAL(15,2)` (BDT). All tables use `bigint` auto-increment PKs and
`created_at`/`updated_at` timestamps unless noted. Soft deletes (`deleted_at`) on records we must
never truly lose (listings, investments, agreements, payments, payouts).

## Entity-relationship diagram

```
                         ┌──────────────┐
                         │  categories  │
                         └──────┬───────┘
                                │ 1
                                │
                          N     ▼
┌──────────────┐        ┌──────────────┐        ┌────────────────┐
│    admins    │        │   listings   │ 1────N │  rebate_rates  │
│ (RBAC roles) │        │  (batches)   │        │ (tenure→rate)  │
└──────┬───────┘        └──────┬───────┘        └────────────────┘
       │ reviews/               │ 1                      ▲
       │ processes              │                        │ snapshot
       │                   N    ▼                        │
       │              ┌──────────────────┐               │
       │              │   investments    │───────────────┘
       │              └───┬─────┬─────┬───┘
       │           1 hasOne│  1  │     │ N
       │                   ▼     ▼     ▼
       │          ┌───────────┐ ┌──────────┐ ┌─────────┐
       │          │ agreements│ │ payments │ │ payouts │
       │          └───────────┘ └────┬─────┘ └────┬────┘
       │                             │ confirmed_by│ processed_by
       └─────────────────────────────┴─────────────┘
                                │
                          N     │  N          N
┌──────────────┐         ┌──────▼───────┐   ┌──────────────────┐
│kyc_documents │ N─────1 │    users     │ 1─│  notifications   │
│              │         │ (investors)  │   │  (Laravel db)    │
└──────────────┘         └──────┬───────┘   └──────────────────┘
                                │ 1
                                │ N
                          (investments, payments, payouts belong to user)

  Standalone: categories, faqs, contact_messages, settings,
              sessions, jobs, failed_jobs, password_reset_tokens (Laravel)
```

Relationship summary:

- `users` **1—N** `investments`, `kyc_documents`, `payments`, `payouts`, `notifications`
- `categories` **1—N** `listings`
- `listings` **1—N** `rebate_rates`, `investments`, `listing_documents`
- `investments` **1—1** `agreements`, **1—1** `payments`, **1—N** `payouts` (usually 1)
- `investments` **N—1** `rebate_rates` (the chosen tenure/rate, snapshotted onto the investment)
- `admins` review `kyc_documents`, confirm `payments`, process `payouts` (via `*_by` FKs)

---

## Tables

### `users` — investors
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email | string, unique | |
| phone | string, unique | E.164 or local format, validated |
| password | string, nullable | nullable for OAuth-only accounts |
| google_id | string, nullable, unique | Socialite |
| email_verified_at | timestamp, nullable | |
| phone_verified_at | timestamp, nullable | v2: OTP; nullable for now |
| nid_number | string, nullable, **encrypted** | national ID |
| passport_number | string, nullable, **encrypted** | |
| bank_account_name | string, nullable | for payouts |
| bank_account_number | string, nullable, **encrypted** | |
| bank_name | string, nullable | |
| bank_branch | string, nullable | |
| routing_number | string, nullable | |
| kyc_status | enum | `pending`, `submitted`, `approved`, `rejected` (default `pending`) |
| status | enum | `active`, `flagged`, `deactivated` (default `active`) |
| remember_token | string, nullable | |
| timestamps | | |

### `kyc_documents`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK → users | |
| type | enum | `nid`, `passport` |
| document_number | string, **encrypted** | |
| front_image_path | string | **private disk** |
| back_image_path | string, nullable | |
| selfie_path | string, nullable | optional liveness/selfie |
| status | enum | `pending`, `approved`, `rejected` (default `pending`) |
| reviewed_by | FK → admins, nullable | |
| reviewed_at | timestamp, nullable | |
| rejection_reason | string, nullable | |
| timestamps | | |

### `admins` — internal staff (separate guard)
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email | string, unique | |
| password | string | |
| status | enum | `active`, `disabled` |
| timestamps | | |

> Roles/permissions via **spatie/laravel-permission** attached to the `admin` guard:
> `super_admin`, `manager`, `finance`. Avoid a role enum column so permissions stay flexible.

### `categories`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| slug | string, unique | |
| timestamps | | |

### `listings` — investment batches
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| category_id | FK → categories, nullable | |
| title | string | |
| slug | string, unique | for SEO URLs |
| summary | string, nullable | short teaser |
| description | longtext | full batch info (rich text) |
| cover_image_path | string, nullable | |
| gallery | json, nullable | array of image paths |
| batch_size | integer, nullable | units of laptops in the batch |
| target_amount | decimal(15,2) | fundraising target |
| funding_cap | decimal(15,2) | hard max that can be raised |
| min_investment | decimal(15,2) | per-investor minimum |
| max_investment | decimal(15,2), nullable | per-investor maximum |
| amount_raised | decimal(15,2), default 0 | **cached** sum of active investments |
| investor_count | integer, default 0 | **cached** distinct investors |
| status | enum | `draft`, `published`, `paused`, `closed`, `funded` |
| published_at | timestamp, nullable | |
| closes_at | timestamp, nullable | soft close date |
| meta_title | string, nullable | SEO |
| meta_description | string, nullable | SEO |
| og_image_path | string, nullable | SEO |
| timestamps + soft deletes | | |

> `amount_raised` / `investor_count` are denormalized caches recomputed inside the invest/payment
> transaction (and via a reconcile command). Never trust them as the source of truth for money —
> derive from `investments` when correctness matters.

### `rebate_rates` — tenure → rate, per listing
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| listing_id | FK → listings | |
| tenure_months | integer | e.g. `6`, `12` |
| rebate_percentage | decimal(5,2) | e.g. `8.00` = 8% for the tenure |
| is_active | boolean, default true | |
| timestamps | | |
| unique | (listing_id, tenure_months) | one rate per tenure per listing |

### `listing_documents` — supporting files
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| listing_id | FK → listings | |
| title | string | |
| file_path | string | |
| timestamps | | |

### `investments`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| reference | string, unique | human-friendly code, e.g. `INV-2026-000123` |
| user_id | FK → users | |
| listing_id | FK → listings | |
| rebate_rate_id | FK → rebate_rates, nullable | which tenure was chosen |
| amount | decimal(15,2) | principal invested |
| tenure_months | integer | **snapshot** (6 / 12) |
| rebate_percentage | decimal(5,2) | **snapshot** at invest time |
| expected_rebate_amount | decimal(15,2) | computed: amount × rate |
| expected_total_return | decimal(15,2) | principal + rebate (see conventions on repayment model) |
| status | enum | `pending_payment`, `active`, `matured`, `paid_out`, `cancelled` |
| invested_at | timestamp, nullable | set when payment confirmed → active |
| maturity_date | date, nullable | invested_at + tenure_months |
| paid_out_at | timestamp, nullable | |
| certificate_path | string, nullable | generated PDF |
| timestamps + soft deletes | | |

> **Snapshotting matters:** rebate percentage and tenure are copied onto the investment so that
> later admin edits to `rebate_rates` never change an existing investor's terms.

### `agreements`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| investment_id | FK → investments, unique | one active agreement per investment |
| user_id | FK → users | |
| version | string | template version accepted |
| content_snapshot | longtext | full rendered agreement text/HTML at acceptance |
| accepted_at | timestamp, nullable | digital acceptance timestamp |
| accepted_ip | string, nullable | IP recorded at acceptance |
| accepted_user_agent | string, nullable | |
| signed_pdf_path | string, nullable | archived PDF |
| timestamps | | |

### `payments` — manual payment records
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| investment_id | FK → investments, unique | |
| user_id | FK → users | |
| method | enum | `mfs`, `bank_transfer`, `card` |
| amount | decimal(15,2) | should equal investment.amount |
| reference | string, nullable | txn id / sender number provided by investor |
| proof_image_path | string, nullable | uploaded receipt/screenshot |
| status | enum | `pending`, `confirmed`, `rejected` |
| confirmed_by | FK → admins, nullable | |
| confirmed_at | timestamp, nullable | |
| rejection_reason | string, nullable | |
| timestamps + soft deletes | | |

### `payouts` — rebate disbursement
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| investment_id | FK → investments | |
| user_id | FK → users | |
| amount | decimal(15,2) | rebate (or principal+rebate per model) |
| scheduled_date | date | usually = investment.maturity_date |
| status | enum | `scheduled`, `processing`, `paid`, `failed` |
| method | string, nullable | how it was paid |
| reference | string, nullable | disbursement txn ref |
| processed_by | FK → admins, nullable | |
| paid_at | timestamp, nullable | |
| note | string, nullable | |
| timestamps + soft deletes | | |

### `contact_messages`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | name, email, phone (nullable), subject, message, handled (bool), timestamps |

### `faqs`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | question, answer (text), category (nullable), sort_order (int), is_published (bool), timestamps |

### `settings` — platform key/value config (optional but recommended)
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | key (unique), value (json/text), timestamps |

> Used for contact addresses (Dhaka / Sharjah / Dubai), support email, default agreement template
> version, feature flags — editable without a deploy.

### Laravel framework tables
`sessions`, `password_reset_tokens`, `jobs`, `job_batches`, `failed_jobs`, `cache`,
`notifications` (database channel), `personal_access_tokens` (only if API tokens are ever needed),
`permissions` / `roles` / pivots (spatie). Ship via the standard published migrations.

---

## Enums (define as PHP 8 backed enums in `app/Enums/`)

```php
KycStatus:         Pending | Submitted | Approved | Rejected
UserStatus:        Active | Flagged | Deactivated
ListingStatus:     Draft | Published | Paused | Closed | Funded
InvestmentStatus:  PendingPayment | Active | Matured | PaidOut | Cancelled
PaymentMethod:     Mfs | BankTransfer | Card
PaymentStatus:     Pending | Confirmed | Rejected
PayoutStatus:      Scheduled | Processing | Paid | Failed
AdminRole:         SuperAdmin | Manager | Finance   // mirror spatie role names
```

## Investment status lifecycle

```
  create investment ──► pending_payment
                             │  investor submits manual payment (payments.status=pending)
                             ▼
                        (awaiting admin)
                             │  Finance confirms payment (payments.status=confirmed)
                             ▼
                          active ─────────────► matured  (maturity_date reached, scheduler)
                             │                     │
                             │ admin cancels       │  Finance marks payout paid
                             ▼                     ▼
                        cancelled              paid_out
```

Guard every transition inside a DB transaction; never move backwards; reject double-confirmation.

## Repayment model — CONFIRM WITH BECHAKEENA

Two possible interpretations of "rebate return." **Pick one before building the payout module**;
it changes `expected_total_return` and `payouts.amount`:

- **(A) Rebate only:** at maturity the investor receives principal back plus the rebate as a
  separate flow, OR principal is treated as procurement float and only the rebate is paid.
- **(B) Principal + rebate:** at maturity the investor receives `amount × (1 + rate)` in one payout.

The schema supports both (principal and rebate are stored separately). This is flagged again in
the [investment flow spec](04-features/03-investment-flow.md) as an open question.

## Seeding plan

- `categories` — a few laptop categories.
- `admins` — one super admin (from env), plus a demo manager and finance in non-prod.
- `settings` — office addresses, support email, default agreement version.
- `faqs` — starter question set.
- `listings` + `rebate_rates` — 3–5 demo batches with 6/12-month rates (non-prod only).
- `users` + `investments` — factory-generated demo data for dashboard/admin development (non-prod).
