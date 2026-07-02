# 05 — Conventions

Consistency beats cleverness. Follow these so any developer can read any part of the codebase.

## PHP / Laravel

- **Style:** PSR-12, enforced by **Laravel Pint** (`vendor/bin/pint`). CI runs `pint --test`.
- **Types:** declare parameter + return types everywhere; use PHP 8 backed enums (`app/Enums`).
- **Controllers are thin.** Business logic lives in **Services** (`app/Services`) and single-purpose
  **Actions** (`app/Actions`). Controllers validate (via FormRequest), call an action/service, return
  a response.
- **Validation:** always via `FormRequest` classes — never inline `$request->validate()` for
  non-trivial rules.
- **Authorization:** Policies for models; `spatie/permission` roles for the admin guard. Every
  mutating endpoint authorizes explicitly.
- **Database writes** that touch multiple rows/money run inside `DB::transaction()`. Guard status
  transitions; make them idempotent.
- **Money:** `DECIMAL(15,2)`; cast to a money value object or integer minor units in code. **Never
  floats** for currency. Centralize rounding in `RebateCalculator`.
- **Enums over magic strings** for all status/type columns.
- **Eloquent:** eager-load to avoid N+1 (`with()`); use scopes for common filters; `casts()` for
  dates/enums/`encrypted` fields.
- **Naming:** Models singular (`Investment`), tables plural sn_case (`investments`), pivot alpha
  order, FKs `{model}_id`, boolean `is_*/has_*`, migrations timestamped + descriptive.
- **Queues:** email, PDF generation, bulk sends, and heavy work are queued jobs — never inline.

## React / Inertia / JS

- **Style:** ESLint + Prettier; run in CI. Functional components + hooks only.
- **Pages** live in `resources/js/Pages/{Dashboard,Admin}/…`, mirroring Inertia route names.
- **Shared UI** in `resources/js/Components`; layouts in `resources/js/Layouts`.
- **Props are server-driven** — controllers shape exactly what a page needs; don't over-fetch.
- **Forms** use Inertia's `useForm` (handles errors/CSRF/progress). Surface Laravel validation errors
  inline.
- **No business logic on the client** for money/authorization — mirror for UX only; server decides.
- **Naming:** Components/Pages `PascalCase`; hooks `useX`; files match component name.

### Shared UI feedback & loading states (build in Phase 1, reuse everywhere)

Every async or state-changing interaction must show explicit feedback. Build the primitives **once**
per stack and reuse them — never hand-roll one-off variants per page. The investor + public UI is
**Blade + Livewire**; the admin UI is **React + Inertia**.

**Investor/public (Blade + Livewire):**

- **Flash alerts** — server flashes via `session()->flash('success'|'error'|'warning'|'info', ...)`,
  rendered by the shared `@include('partials.flash')` in the layouts. Field errors stay inline via
  `@error(...)`.
- **Button / submit loading state** — the `<x-primary-button target="action">` component wires
  `wire:loading.attr="disabled"` + an inline spinner on `wire:target`; never allow a double submit.
- **Confirm dialogs** — for destructive/high-stakes actions (delete, deactivate, submit payment,
  accept agreement, bulk payout): a Livewire-driven modal (or `wire:confirm`) that states the
  consequence and disables confirm while the action is in flight.
- **Loading skeletons** — Blade partials shown behind `wire:loading` for async sections; match the
  final layout so there's no shift.

**Admin (React + Inertia):**

- Shared components in `resources/js/Components` (`Alert`, `FlashMessages`, `PrimaryButton` with a
  `processing` state, `InputError`, `TextField`); flash via `flash.*` shared props in
  `HandleInertiaRequests::share()`; submit state from `useForm().processing`; global Inertia progress
  bar; a `ConfirmDialog` for destructive actions.

**Both stacks:** every list/table defines an empty state and a retriable error state — never render a
blank screen.

## Blade / Livewire

- Public pages are Blade; interactive bits are **Livewire 3** components (`app/Livewire`,
  views in `resources/views/livewire`).
- Keep SEO content in server-rendered markup — never behind a JS-only render.
- Livewire components stay small and focused (one concern: filters, funding progress, contact form).

## Tailwind

- Define brand tokens (colors, fonts, radius, spacing) once in the Tailwind config; use tokens, not
  arbitrary hex values.
- Extract repeated patterns into components (Blade `@component`/partials or React components), not
  copy-pasted class strings.
- Mobile-first; test all breakpoints.

## Files & storage

- **KYC / bank / sensitive uploads → private disk**, accessed only via signed, expiring URLs.
- Public assets (listing images) → public disk / CDN.
- Validate upload mime + size; store paths, never blobs, in the DB.

## Testing

- **Pest/PHPUnit** (skeleton has PHPUnit 12). Feature tests for every user-facing flow; unit tests
  for services (`RebateCalculator`, status transitions).
- Use factories + seeders; `RefreshDatabase`.
- Fake externals: `Mail::fake()`, `Notification::fake()`, `Storage::fake()`, `Socialite` mocked.
- **Minimum bar:** each roadmap phase ships with tests covering its DoD. Money/authorization paths
  are non-negotiable to test.

## Git workflow

- **Branches:** `main` (protected, deployable) → feature branches `feat/…`, `fix/…`, `chore/…`,
  `docs/…`.
- **Commits:** Conventional Commits (`feat:`, `fix:`, `docs:`, `refactor:`, `test:`, `chore:`).
  Imperative mood, small and focused.
- **PRs:** one phase-slice per PR where possible; description links the relevant spec + DoD checklist;
  CI must be green; at least one review.
- **Never commit** `.env`, secrets, or real KYC/user data. `.gitignore` already covers `.env`.
- Update the relevant `docs/` file **in the same PR** as the behavior change.

## CI (must pass before merge)

1. `composer install` + `npm ci`
2. `php artisan migrate` on a fresh test DB
3. `vendor/bin/pint --test`
4. `npm run lint` (ESLint) + build check (`npm run build`)
5. `php artisan test`

## Environment & secrets

- All config via `.env` (never hardcoded). Document every new key in `.env.example`.
- Separate keys per environment; secrets in the host's secret store in prod, not in the repo.

## Definition of Done (every task)

- [ ] Meets the acceptance criteria in the relevant spec.
- [ ] Validated server-side; authorized (user/role) server-side.
- [ ] Money/status changes are transactional + idempotent.
- [ ] Tests written and passing; CI green.
- [ ] Docs updated if behavior/schema changed.
- [ ] No secrets or sensitive data committed.
