# Bechakeena Investment Platform — Documentation

This folder is the single source of truth for planning and building the Bechakeena
Investment Platform. Read the docs in order the first time; after that, jump to whatever
you need.

## How to use these docs

1. **Start with [00-overview.md](00-overview.md)** to understand what we're building and why.
2. **Read [01-architecture.md](01-architecture.md)** to understand the hybrid frontend and how requests flow.
3. **Study [02-data-model.md](02-data-model.md)** — the schema is the backbone of everything.
4. **Follow [03-roadmap.md](03-roadmap.md)** — this is the step-by-step build order. Work phase by phase.
5. **Reference [04-features/](04-features/)** when building a specific feature. Each file is a spec.
6. **Obey [05-conventions.md](05-conventions.md)** for coding standards, naming, and git workflow.
7. **Use [06-deployment.md](06-deployment.md)** when preparing environments and going live.

## Document index

| # | Document | Purpose |
|---|----------|---------|
| 00 | [Overview](00-overview.md) | Scope, goals, personas, glossary, non-goals |
| 01 | [Architecture](01-architecture.md) | Tech stack, hybrid frontend, request lifecycle, directory layout |
| 02 | [Data Model](02-data-model.md) | Entities, ERD, tables, relationships, enums |
| 03 | [Roadmap](03-roadmap.md) | Phased, step-by-step development plan with milestones |
| 04 | [Features](04-features/) | Per-feature functional specs |
| 05 | [Conventions](05-conventions.md) | Coding standards, naming, git, testing |
| 06 | [Deployment](06-deployment.md) | Environments, hosting, CI/CD, backups, security ops |
| 07 | [Brand](07-brand.md) | Logo asset + brand colors (raspberry `#B71E61`) and Tailwind tokens |

## Feature specs

- [Public site & SEO](04-features/01-public-site.md)
- [Authentication, accounts & KYC](04-features/02-auth-accounts-kyc.md)
- [Investment flow](04-features/03-investment-flow.md)
- [Investor dashboard](04-features/04-investor-dashboard.md)
- [Admin panel](04-features/05-admin-panel.md)
- [Notifications & email](04-features/06-notifications.md)

## Locked architecture decisions

These were confirmed at planning time. Changing them means revisiting the roadmap.

- **Single Laravel application.** One repo, one deployment. No separate frontend app.
- **Hybrid frontend:**
  - Public pages (landing, listings, detail, about, FAQ, contact) → **Blade + Livewire**, server-rendered for SEO.
  - Investor dashboard → **React + Inertia.js**.
  - Admin panel → **React + Inertia.js** under an `/admin` route prefix (same stack as the dashboard).
- **Database:** **SQLite** for local development & testing; **MySQL 8** in production. Keep
  migrations and queries DB-agnostic (portable column types, no MySQL-only SQL) and run the test
  suite against MySQL in CI before release so nothing behaves differently in production.
- **Payments:** Manual (MFS / bank transfer / card), admin-confirmed. No payment gateway integration in v1.
- **Auth:** Session-based (Laravel's built-in), with Google OAuth via Socialite. Separate guard for admins.

> When a decision changes, update this list, the affected feature spec, and the roadmap in the same commit.
