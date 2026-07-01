# Bechakeena Investment Platform

A single **Laravel 13** web application that lets investors fund Bechakeena's bulk laptop
procurement cycles and earn a fixed, tenure-based rebate (6 months / 1 year). It combines a
public marketing site, an investor portal, and an admin panel into one product.

Bechakeena is an established B2B laptop wholesale platform operating across Bangladesh,
Sharjah (UAE), and Dubai. This platform extends that ecosystem to outside capital.

## Documentation

All planning and development docs live in [`docs/`](docs/). Start with
[`docs/README.md`](docs/README.md), then follow the phased build order in
[`docs/03-roadmap.md`](docs/03-roadmap.md).

Agents and new contributors: read [`CLAUDE.md`](CLAUDE.md) first for orientation.

| Doc | Purpose |
|-----|---------|
| [00-overview](docs/00-overview.md) | Scope, personas, glossary |
| [01-architecture](docs/01-architecture.md) | Hybrid frontend, request flow, stack |
| [02-data-model](docs/02-data-model.md) | Schema / ERD |
| [03-roadmap](docs/03-roadmap.md) | Phased, step-by-step build plan |
| [04-features](docs/04-features/) | Per-feature specs |
| [05-conventions](docs/05-conventions.md) | Coding standards |
| [06-deployment](docs/06-deployment.md) | Environments, ops, go-live |

## Tech stack

- **Backend:** PHP 8.3, Laravel 13
- **Public pages (SEO):** Blade + Livewire 4 (server-rendered)
- **Investor dashboard & admin:** React 19 + Inertia.js
- **Styling / build:** Tailwind CSS 4, Vite 8
- **Database:** SQLite (local/dev/test) · MySQL 8 (production)
- **Auth:** Laravel session auth + Google OAuth (Socialite)
- **Payments:** Manual (MFS / bank transfer / card), admin-confirmed

## Local setup

Requires PHP 8.3, Composer 2, Node 20.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite      # local database (SQLite)
php artisan migrate
npm install
npm run build
```

Then run everything (server + queue + logs + Vite) with:

```bash
composer dev
```

The app serves at http://localhost:8000.

## Common commands

```bash
composer dev          # server + queue + logs + vite concurrently
php artisan serve     # app server only
npm run dev           # vite dev server
php artisan test      # run the test suite
vendor/bin/pint       # format PHP (pint --test in CI)
```

## Project status

Early development — see the **Current status** section in [`CLAUDE.md`](CLAUDE.md) for what's built
and what's next.

## License

Proprietary — © Bechakeena. All rights reserved.
