# 06 — Deployment & Operations

Hosting is **client-provided** (cloud or shared). This app is designed cloud-first but can run on
standard PHP hosting with some trade-offs (noted below).

## Environments

| Env | Purpose | Branch | DB | Mail | Storage |
|-----|---------|--------|----|----|---------|
| local | dev | any | SQLite | Mailpit / log | local |
| staging | client review / QA | `main` (auto-deploy) | MySQL | SMTP (test) | S3 / disk |
| production | live | tagged release | MySQL | client SMTP | S3 / private disk |

Every env has its own `.env`; secrets live in the host's secret store, not the repo.

## Requirements

- PHP 8.3, Composer 2, Node 20 (build-time). **Database: SQLite locally/in dev & test; MySQL 8 in
  production.** Migrations and queries stay DB-agnostic; CI runs the suite against MySQL before
  release so production matches.
- Extensions: pdo_mysql (prod), pdo_sqlite (local/dev), mbstring, openssl, gd/imagick (PDF/images),
  bcmath, fileinfo, zip.
- A queue worker + a scheduler (cron) — **critical** for email, PDFs, maturity/payout jobs.

## Build & release steps

```
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link           # if serving public uploads via the public disk
php artisan queue:restart          # after each deploy so workers load new code
```

Zero-downtime (cloud): deploy to a fresh release dir, run migrations, then switch the symlink.
Use `php artisan down`/`up` (with a secret bypass) around risky migrations.

## Queue worker

- Run a persistent worker: `php artisan queue:work --tries=3 --timeout=120` under **Supervisor**
  (cloud/VPS) or the platform's worker process.
- **Shared hosting caveat:** no long-running processes → fall back to the `sync` driver (email/PDF
  run inline — slower requests) **or** a cron-triggered `queue:work --stop-when-empty` every minute.
  Prefer real cloud hosting if volume is non-trivial.

## Scheduler (cron)

Add one cron entry:
```
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```
Drives maturity transitions, payout scheduling, maturity reminders, and admin alerts (see
[notifications spec](04-features/06-notifications.md)). Without it, time-based features do not fire.

## Storage & CDN

- **KYC/bank/sensitive files → private disk** (S3 private bucket or non-public local dir). Serve only
  via signed, expiring URLs. Never web-accessible.
- **Public images** (listings) → public disk / CDN with long cache headers + hashed asset names.
- Vite build output served via CDN where possible for global performance.
- On shared hosting without S3: keep private uploads **outside** the web root and stream via
  authorized controller routes.

## Security operations

- **HTTPS everywhere**; HSTS; secure, `SameSite` cookies; force TLS.
- `APP_DEBUG=false` and `APP_ENV=production` in prod.
- **Encrypt sensitive fields** (NID, passport, bank account) via Eloquent `encrypted` casts; ensure
  `APP_KEY` is set and backed up (losing it makes encrypted data unrecoverable).
- Rate limiting on auth + contact + investment endpoints.
- Regular `composer audit` / dependency updates.
- Run `/security-review` before launch (Phase 8) and after major changes.
- Restrict `/admin` (separate guard already); consider IP allow-listing or extra MFA for admins.
- Backups of `APP_KEY` and `.env` stored securely (separately from DB backups).

## Backups & DR

- **Database:** automated daily (or more) backups, offsite, retention ≥ 30 days; **test restores**.
- **Files:** back up KYC/agreement/certificate storage (versioned bucket or scheduled sync).
- Document a restore runbook; verify it before launch.

## Monitoring & logging

- Error tracking (e.g. Sentry) for exceptions.
- Uptime monitoring on public + login endpoints.
- Log aggregation with retention; **never log** sensitive fields (NID/bank/passwords).
- Queue depth + failed-jobs monitoring (`failed_jobs`); alert on backlog.
- Email deliverability monitoring (bounces/complaints) via the SMTP provider.

## Configuration checklist (per environment)

- [ ] `APP_ENV`, `APP_DEBUG`, `APP_URL`, `APP_KEY` set correctly.
- [ ] MySQL credentials + DB created; migrations run.
- [ ] Mail (SMTP) configured; From/reply-to verified; SPF/DKIM/DMARC set (prod).
- [ ] Queue driver + worker running (or documented shared-hosting fallback).
- [ ] Scheduler cron installed.
- [ ] Storage disks configured; private disk not web-accessible; `storage:link` if needed.
- [ ] Google OAuth client id/secret + callback URL for this domain.
- [ ] CDN configured for assets/images.
- [ ] Config/route/view/event caches built.
- [ ] Backups scheduled + restore-tested; `APP_KEY`/`.env` backed up securely.
- [ ] Monitoring + error tracking live.

## Go-live (Phase 8) checklist

- [ ] Security review clean or risks accepted in writing.
- [ ] Financial reconciliation passes (caches vs raw; no double payouts).
- [ ] Full investor + admin journeys pass on staging (cross-browser + mobile).
- [ ] SEO verified (sitemap submitted, structured data valid, robots correct).
- [ ] Backups + monitoring confirmed working in production.
- [ ] Rollback plan documented.
