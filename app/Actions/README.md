# Actions

Single-purpose, invokable use-cases that orchestrate a flow inside a DB transaction. One class,
one job; called from controllers/jobs.

Planned (see [docs/04-features/](../../docs/04-features/)):
`InvestInListing`, `ConfirmPayment`, `ApproveKyc`, `ProcessPayout`.

State-changing actions that touch money must be transactional and idempotent (guard status
transitions).
