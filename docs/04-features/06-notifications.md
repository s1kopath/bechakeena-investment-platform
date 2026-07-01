# Feature Spec — Notifications & Email

**Stack:** Laravel Notifications (mail + database channels), queued; Scheduler for time-based
events; client SMTP (SendGrid / Resend). Built in [Phase 7](../03-roadmap.md). **Rule:** all email
is **queued** — never send inline in a web request.

## Channels

- **mail** — transactional email via client SMTP.
- **database** — in-app notifications shown in the dashboard notification center.

## Investor notifications

| Event | Trigger | Channels | Contents |
|-------|---------|----------|----------|
| Investment confirmation receipt | Payment confirmed → investment active | mail + db | reference, listing, amount, tenure, rebate, maturity, agreement summary, certificate link |
| Rebate payout processed | Payout marked `paid` | mail + db | amount, investment ref, date, method/reference |
| Maturity reminder | **7 days before** `maturity_date` (scheduler) | mail + db | investment ref, maturity date, expected payout |
| New listing launch | Listing published | mail (opt-in) + db | listing title, rebate/tenure, link |
| KYC approved / rejected | Admin review | mail + db | status + rejection reason (if any) |

## Admin alert emails

| Event | Trigger | Recipients |
|-------|---------|-----------|
| Large investment | Investment ≥ configurable threshold | Super Admin, Finance |
| KYC pending | New KYC submitted | Manager |
| Payout due | Payout `scheduled_date` reached / approaching | Finance |
| New contact message | Contact form submitted | support inbox |

## Manual email (admin)

- Compose + send to a single investor or a filtered bulk audience.
- Queued **per recipient**; record send in an activity/email log; respect any unsubscribe/opt-out.

## Scheduled jobs (Laravel Scheduler)

Nightly (and/or hourly) command(s) that:
1. Transition `active` investments whose `maturity_date` has passed → `matured`.
2. Create/schedule `payouts` for matured investments (per repayment model).
3. Send **maturity reminders** for investments maturing in exactly 7 days.
4. Fire **payout due** admin alerts.

Register via `routes/console.php` / the scheduler; production runs `schedule:run` every minute via
cron (or the platform's scheduler). See [deployment](../06-deployment.md).

## Deliverability

- Configure the client's SMTP provider; set up **SPF + DKIM** (and DMARC) for the sending domain.
- Use a verified From address + reply-to.
- Templated, mobile-friendly HTML emails with a plain-text fallback; consistent branding.
- Handle bounces/complaints per provider (log; suppress hard bounces).

## Testing

- `Mail::fake()` / `Notification::fake()` to assert each event dispatches the right notification.
- Time-travel tests for the 7-day maturity-reminder window.
- Assert bulk send queues one job per recipient.

## Acceptance criteria

- [ ] Investment activation sends a confirmation (mail + db) with agreement summary + certificate link.
- [ ] Payout mark-paid sends a receipt.
- [ ] A 7-days-to-maturity investment triggers exactly one reminder.
- [ ] New published listing notifies opted-in investors.
- [ ] KYC approve/reject notifies the investor with reason where relevant.
- [ ] Admin alerts fire for large investments, pending KYC, and due payouts.
- [ ] Admin can send individual + bulk email; sends are queued and logged.
- [ ] SPF/DKIM pass on the client SMTP domain; emails render on mobile.
