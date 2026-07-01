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
  - Public/SEO pages → **Blade + Livewire 3** (server-rendered)
  - Investor dashboard → **React + Inertia.js 2**
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

- **Phase:** Not started — codebase is the **fresh Laravel 13 skeleton** (no Livewire, Inertia,
  React, or auth installed yet).
- **Next action:** **Phase 0 — Foundation & Tooling** in [docs/03-roadmap.md](docs/03-roadmap.md#phase-0--foundation--tooling--size-l---none):
  wire Livewire + Inertia/React, configure DB (SQLite locally), split routes, base layouts, tooling
  (Pint/ESLint/Prettier), and a green CI.
- **`.env`:** not yet configured (skeleton default). Set `DB_CONNECTION=sqlite` for local.

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
