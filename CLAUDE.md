# CLAUDE.md — Agent Orientation

Read this first. It orients any agent (or developer) picking up the **Bechakeena Investment
Platform**. Detailed docs live in [`docs/`](docs/) — this file points you into them.

## What this project is

A single **Laravel 13** web app that lets investors fund Bechakeena's bulk laptop procurement
cycles and earn a fixed, tenure-based rebate (6 months / 1 year). One repo, one deployment: public
marketing site + investor dashboard + admin panel.

Full context: [docs/00-overview.md](docs/00-overview.md).

## Start here (read in order)

1. [docs/README.md](docs/README.md) — doc index + how to use
2. [docs/00-overview.md](docs/00-overview.md) — scope, personas, what's in/out
3. [docs/01-architecture.md](docs/01-architecture.md) — hybrid frontend, request flow, stack
4. [docs/02-data-model.md](docs/02-data-model.md) — schema/ERD (**freeze before Phase 4**)
5. [docs/03-roadmap.md](docs/03-roadmap.md) — **the build order; work phase by phase**
6. [docs/04-features/](docs/04-features/) — per-feature specs
7. [docs/05-conventions.md](docs/05-conventions.md) — coding standards (**follow these**)
8. [docs/06-deployment.md](docs/06-deployment.md) — envs, ops, go-live

## Locked architecture decisions (do not change without updating docs)

- **Single Laravel app.** Hybrid frontend (**revised 2026-07-02** — investor moved to Livewire):
  - Public/SEO pages **and the investor area** (auth, dashboard, profile, KYC) → **Blade + Livewire 4**
    (server-rendered, `web` guard)
  - Admin panel → **React + Inertia** (inertia-laravel 3) under `/admin` **only**, separate `admin`
    guard + spatie RBAC (super_admin / manager / finance)
- **Database:** **SQLite** local/dev/test, **MySQL 8** production. Keep migrations & queries
  DB-agnostic; CI tests on MySQL.
- **Payments:** manual (MFS / bank transfer / card), admin-confirmed. No gateway in v1.
- **Auth:** Laravel session auth + Google OAuth (Socialite).
- **Money:** `DECIMAL(15,2)`, never floats. Sensitive fields (NID/passport/bank) encrypted + on the
  **private** disk, served via signed URLs.
- **Global audience.** Investors register from anywhere — **country-agnostic, no locale-specific
  validation** (phone is international/E.164). Money currently in BDT; multi-currency is out of scope
  unless requested. See [docs/00-overview.md](docs/00-overview.md).

## Current status

> Update this section at the end of every working session.

- **Phase 0 — Foundation & Tooling: ✅ COMPLETE** (2026-07-01). The hybrid stack is wired and
  verified:
  - Livewire 4 + Inertia (inertia-laravel 3) + React 19 installed; Vite builds; `@` → `resources/js` alias.
  - `/` → Blade + Livewire public page (server-rendered, HTTP 200).
  - `/dashboard` → Inertia React page `Dashboard/Index` (HTTP 200). **No auth yet** — route is open
    for the smoke test; Phase 1 wraps it in `['auth','verified']` (see TODO in `routes/web.php`).
  - Routes split: `web.php` / `auth.php` / `admin.php` (loaded in `bootstrap/app.php`;
    `HandleInertiaRequests` in the web group).
  - Layouts: public Blade `components/layouts/app.blade.php`; React `DashboardLayout` + `AdminLayout`.
  - Tailwind brand tokens (`--color-brand-*`) in `resources/css/app.css`.
  - Tooling: Pint (`pint.json`), ESLint 9 flat config (`eslint.config.js`), Prettier (`.prettierrc`);
    npm scripts `lint`/`format`. Domain folders `app/{Enums,Services,Actions}` with READMEs.
  - CI (`.github/workflows/ci.yml`): composer/npm install, build, pint --test, eslint, prettier,
    migrate + `php artisan test` **against MySQL**.
  - **Verified locally:** `npm run build`, `vendor/bin/pint --test`, `npm run lint`,
    `npm run format:check`, and `php artisan test` (2/2) all pass.
- **Committed:** Phase 0 is committed (`041633d`).
- **Brand applied** (2026-07-02): raspberry `#B71E61` palette + `--color-ink` live in
  `resources/css/app.css` (Inertia progress bar too); logo + full spec in
  [docs/07-brand.md](docs/07-brand.md). Everything is token-based (`brand-*`), so the whole app is
  on-brand.

- **Phase 1 — Authentication & Accounts: 🟡 IN PROGRESS.** Data foundation (roadmap step 1) done:
  - Enums: `KycStatus`, `UserStatus`, `KycDocumentType`, `KycDocumentStatus` (`app/Enums`).
  - Migrations: investor fields on `users` (phone, google_id, verified_at, encrypted nid/passport/bank,
    `kyc_status`, `status`; password now nullable for OAuth); `kyc_documents`; `notifications`.
  - Models: `User` now `implements MustVerifyEmail`, enum + `encrypted` casts, sensitive fields hidden,
    `kycDocuments()` relation; new `KycDocument` model.
  - Factories: `UserFactory` (phone + enum defaults + `unverified`/`kycApproved`/`kycSubmitted`/`google`/
    `deactivated` states); new `KycDocumentFactory`.
  - **Convention added:** shared UI feedback/loading standards (flash alerts, skeletons, submit loading,
    confirm dialogs) in [docs/05-conventions.md](docs/05-conventions.md) — build during Phase 1 UI.
  - **Auth stack:** investor auth is **Blade + Livewire** (full-page components), matching the public
    site and the whole investor area (see revised hybrid above). Admin auth is a **separate `admin`
    guard/login** under `/admin` in **React + Inertia** — Phase 2, fully separate.
  - **Registration + login + logout + email verification DONE** (investor / `web` guard, Livewire):
    - Livewire components `App\Livewire\Auth\{Login, Register, VerifyEmail}` (validation + throttle +
      status block inline); `RegisterUser` action (fires `Registered` → verification email).
    - Controllers kept for non-view actions: `Auth\VerifyEmailController` (signed link),
      `Auth\LogoutController`. Routes in `routes/auth.php` (guest: register/login; auth:
      verification.{notice,verify} + logout).
    - `/dashboard` = Livewire `App\Livewire\Dashboard\Index` behind **`['auth','verified']`**;
      unverified → `verification.notice`. Guest→`/login`, authed→`/dashboard` redirects in `bootstrap/app.php`.
    - Blade layouts `components/layouts/{guest,dashboard}.blade.php`; shared `partials/flash.blade.php`,
      `<x-text-field>`, `<x-primary-button target=...>` (wire:loading spinner).
    - React shared components (`resources/js/Components/*`, `AdminLayout`) kept for the admin panel (Phase 2).
  - **Verified:** `npm run build` OK; `pint`/`eslint`/`prettier` clean; `php artisan test` **30/30**
    (foundation + registration incl. a non-faked test that renders the real verification URL + login
    (email/phone, throttle, deactivated block) + logout + email-verification flow + gating redirects).
  - **Dev note:** `MAIL_MAILER=log` (verification link lands in `storage/logs/laravel.log`);
    `QUEUE_CONNECTION=database` — run `composer dev` (includes a queue worker) so the email job processes.
- **Next action (Phase 1, remaining):** password reset → Google OAuth (Socialite) + intended-URL
  redirect → profile (Inertia, encrypted fields) → KYC upload (private disk, signed URLs) →
  notifications scaffolding usage.
- **Env:** `.env` configured for **SQLite** (`database/database.sqlite`); Phase 1 migrations run.

## Must-resolve questions before Phase 4 (investment flow)

Flagged in the roadmap; get answers from Bechakeena:
- Repayment model: **principal+rebate** vs **rebate-only**
- Rebate % semantics: total-for-tenure vs annualized
- Exact agreement legal template + versioning
- Does KYC gate *investing* (assumed) or account creation?
- Card payment: truly manual, or a gateway in v1?

## Working agreement for agents

- **Follow the roadmap phase order.** Don't jump ahead; respect phase dependencies.
- **Obey [docs/05-conventions.md](docs/05-conventions.md):** thin controllers, logic in
  Services/Actions, FormRequest validation, server-side authz, transactional + idempotent money
  operations, tests per phase DoD.
- **Update docs in the same change** as any behavior/schema change.
- **Update the "Current status" section above** before you finish a session, so the next session
  knows where things stand.

## Common commands

```bash
composer setup        # first-time: install, .env, key, migrate, npm build (see composer.json)
composer dev          # run server + queue + logs + vite concurrently
php artisan test      # run tests
vendor/bin/pint       # format PHP (pint --test in CI)
npm run dev           # vite dev server
npm run build         # production asset build
```

## Environment notes

- PHP 8.3, Composer 2, Node 20 installed. Verify versions if something fails.
- Local DB is SQLite (`database/database.sqlite`); production is MySQL 8.
