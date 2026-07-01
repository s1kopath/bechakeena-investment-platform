# 01 — Architecture

## One app, three surfaces

The platform is **one Laravel application, one deployment, one database**. It exposes three
user-facing surfaces, each with the rendering approach best suited to it:

```
                          ┌─────────────────────────────────────────────┐
                          │        Bechakeena Investment Platform         │
                          │            (single Laravel 13 app)            │
                          └─────────────────────────────────────────────┘
                                             │
        ┌────────────────────────────────────┼────────────────────────────────────┐
        │                                     │                                     │
        ▼                                     ▼                                     ▼
┌────────────────┐                 ┌────────────────────┐                ┌────────────────────┐
│  PUBLIC SITE   │                 │ INVESTOR DASHBOARD │                │    ADMIN PANEL     │
│  Blade +       │                 │  React + Inertia   │                │  React + Inertia   │
│  Livewire      │                 │  (auth: web guard) │                │  (auth: admin      │
│  (SEO, SSR)    │                 │                    │                │   guard, /admin)   │
├────────────────┤                 ├────────────────────┤                ├────────────────────┤
│ / (landing)    │                 │ /dashboard         │                │ /admin/listings    │
│ /listings      │  ── login ──►   │ /dashboard/invest… │                │ /admin/investors   │
│ /listings/{s}  │                 │ /dashboard/history │                │ /admin/payouts     │
│ /about /faq    │                 │ /dashboard/payouts │                │ /admin/analytics   │
│ /contact       │                 │ /notifications     │                │ …                  │
└────────────────┘                 └────────────────────┘                └────────────────────┘
```

### Why hybrid?

- **Public pages must be crawlable and fast.** Blade + Livewire renders complete HTML on the
  server, so search engines index everything without executing JavaScript. Livewire adds
  interactivity (filters, search, funding progress) without a SPA.
- **The dashboard and admin are app-like.** Rich, stateful, behind auth, and never indexed —
  React + Inertia gives a first-class SPA developer experience while still routing and
  authorizing on the server through Laravel controllers.

Inertia is **not** a separate API. Controllers return `Inertia::render('Page', $props)`; there's
no REST layer to maintain for the first-party UI. Same routes, same middleware, same auth.

## Tech stack

| Layer | Choice | Notes |
|-------|--------|-------|
| Language / Framework | **PHP 8.3 + Laravel 13** | Already scaffolded (`composer.json`: `laravel/framework ^13.8`) |
| Public UI | **Livewire 4** + Blade | Server-rendered, SEO-first |
| App UI (dashboard + admin) | **Inertia.js (inertia-laravel 3) + React 19** | SPA experience, server-driven routing |
| Styling | **Tailwind CSS 4** | Already scaffolded (`@tailwindcss/vite`) |
| Build | **Vite 8** + `laravel-vite-plugin` | Already scaffolded |
| Database | **SQLite** (local/dev/test) · **MySQL 8** (production) | Keep migrations/queries DB-agnostic; test on MySQL in CI |
| Auth (users) | Laravel session auth + **Laravel Socialite** (Google OAuth) | Same-domain, cookie sessions |
| Auth (admin) | Separate `admin` guard | Own login, own middleware |
| Authorization | Policies + Gates; **spatie/laravel-permission** for admin RBAC | Super Admin / Manager / Finance |
| Queue | Database driver (v1); Redis-ready | Emails, PDF generation, reminders |
| PDF | **barryvdh/laravel-dompdf** (or Spatie Browsershot for richer output) | Agreements & certificates |
| Mail | Laravel Mail + client SMTP (SendGrid / Resend) | Transactional email |
| File storage | `local`/`public` disk in v1; S3-compatible in cloud | KYC docs on **private** disk |
| Scheduling | Laravel Scheduler (`schedule:run` via cron) | Maturity reminders, payout scheduling |

### Packages to add (beyond the skeleton)

Install these during Phase 0 / as each phase needs them:

```
composer require livewire/livewire
composer require inertiajs/inertia-laravel
composer require laravel/socialite
composer require spatie/laravel-permission
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel        # CSV/Excel export (admin)
composer require spatie/laravel-sitemap   # XML sitemap (optional; can be hand-rolled)
```

```
npm i @inertiajs/react react react-dom
npm i -D @vitejs/plugin-react eslint prettier
```

> Installed in Phase 0 (2026-07-01): `livewire/livewire ^4.3`, `inertiajs/inertia-laravel ^3.1`,
> `@inertiajs/react ^3.5`, `react ^19`. Pin majors in `composer.json` / `package.json`.

## Request lifecycle

### Public page (Blade + Livewire)
```
Browser → Route (web.php) → Controller/Livewire component
        → Blade view rendered server-side (full HTML)
        → Livewire hydrates for interactivity (filters, search)
        → Response is fully-formed HTML (indexable)
```

### Dashboard / admin page (Inertia + React)
```
Browser → Route (web.php, auth middleware) → Controller
        → Inertia::render('Dashboard/Portfolio', $props)
        → First load: Blade root view (app.blade.php) + JSON props → React mounts
        → Subsequent nav: XHR returns JSON props only → React swaps page (no full reload)
```

### The login handoff (the "seamless transition")
```
Visitor on /listings/{slug} (Blade) clicks "Invest"
   → not authenticated → redirect to /login (intended URL stored)
   → user logs in / registers
   → redirect back to intended URL OR into /dashboard (Inertia)
```
The transition from server-rendered public pages to the Inertia app is a normal full-page
navigation — the user perceives one continuous product. Store the intended URL so users returning
from a detail page land back on it (see [investment flow spec](04-features/03-investment-flow.md)).

## Routing map (high level)

| Prefix | Guard / Middleware | Rendering | Routes file |
|--------|--------------------|-----------|-------------|
| `/` (public) | none / guest | Blade + Livewire | `routes/web.php` |
| `/login`, `/register`, `/password/*`, `/auth/google/*` | guest | Blade | `routes/auth.php` |
| `/dashboard/*`, `/notifications` | `auth`, `verified` | Inertia (React) | `routes/web.php` |
| `/admin/*` | `auth:admin`, role middleware | Inertia (React) | `routes/admin.php` |

Split routes into multiple files loaded from `bootstrap/app.php` (`web.php`, `auth.php`,
`admin.php`) to keep each surface's routing readable.

## Directory layout (target)

```
app/
  Http/
    Controllers/
      Public/           # public pages (thin; most logic in Livewire)
      Dashboard/        # Inertia controllers for investor dashboard
      Admin/            # Inertia controllers for admin panel
      Auth/             # login, register, OAuth, password reset
    Middleware/
    Requests/           # FormRequest validation classes
  Livewire/             # Livewire components (public interactivity)
    Listings/           # browse, filters, funding progress
  Models/               # Eloquent models (see 02-data-model.md)
  Services/             # domain services (RebateCalculator, AgreementGenerator, PayoutService…)
  Actions/              # single-purpose actions (InvestInListing, ApproveKyc…)
  Notifications/        # Laravel notifications (email + database)
  Policies/             # authorization
  Enums/                # InvestmentStatus, KycStatus, PayoutStatus, AdminRole…
resources/
  views/                # Blade: public pages + Inertia root (app.blade.php)
    livewire/           # Livewire component views
  js/
    Pages/              # Inertia React pages
      Dashboard/
      Admin/
    Components/         # shared React components
    Layouts/            # Inertia layouts (DashboardLayout, AdminLayout)
    app.jsx             # Inertia bootstrap
  css/
routes/
  web.php  auth.php  admin.php  console.php
database/
  migrations/  factories/  seeders/
docs/                   # you are here
```

## Cross-cutting concerns

- **Authorization:** every dashboard action checks ownership (an investor only sees their own
  investments); every admin action checks role. Use Policies for models, `spatie/permission`
  roles for the admin guard.
- **Money:** store all monetary amounts as **integer minor units (paisa/cents)** or
  `DECIMAL(15,2)` — never floats. Standardize in [conventions](05-conventions.md).
- **Auditability:** investments, agreements, payments, and payouts are append-friendly. Record
  `accepted_at`, `accepted_ip`, `confirmed_by`, `processed_by`, timestamps everywhere.
- **Idempotency:** payment confirmation and payout marking must be safe against double-submits
  (guard on status transitions inside DB transactions).
- **SEO:** see [public site spec](04-features/01-public-site.md) — meta tags, Open Graph,
  JSON-LD structured data, XML sitemap, semantic HTML, and caching.
- **Security:** KYC and bank data are sensitive. Private storage disk, signed URLs for document
  access, encryption at rest for the most sensitive fields. See [deployment](06-deployment.md).

## Environments

| Env | Purpose | DB | Mail | Storage |
|-----|---------|----|----|---------|
| local | development | SQLite | Mailpit / log | local disk |
| staging | client review | MySQL | real SMTP (test key) | S3 / disk |
| production | live | MySQL | client SMTP | S3 / private disk |

Details in [06-deployment.md](06-deployment.md).
