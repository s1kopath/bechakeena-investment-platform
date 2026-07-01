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

- **Single Laravel app.** Hybrid frontend:
  - Public/SEO pages → **Blade + Livewire 4** (server-rendered)
  - Investor dashboard → **React 19 + Inertia.js** (inertia-laravel 3)
  - Admin panel → **React + Inertia** under `/admin`, separate `admin` guard + spatie RBAC
    (super_admin / manager / finance)
- **Database:** **SQLite** local/dev/test, **MySQL 8** production. Keep migrations & queries
  DB-agnostic; CI tests on MySQL.
- **Payments:** manual (MFS / bank transfer / card), admin-confirmed. No gateway in v1.
- **Auth:** Laravel session auth + Google OAuth (Socialite).
- **Money:** `DECIMAL(15,2)`, never floats. Sensitive fields (NID/passport/bank) encrypted + on the
  **private** disk, served via signed URLs.

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
- **Next action:** **Phase 1 — Authentication & Accounts** ([docs/03-roadmap.md](docs/03-roadmap.md))
  — registration (email+phone), login/logout, password reset, Google OAuth (Socialite), profile,
  KYC upload. Then wrap the `/dashboard` route group in auth middleware.
- **Env:** `.env` configured for **SQLite** (`database/database.sqlite`); base migrations run.
- **Not committed:** Phase 0 changes are in the working tree, not yet committed to git.

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
