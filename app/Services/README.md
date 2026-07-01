# Services

Domain services hold business logic that controllers must stay thin about. Stateless, testable,
single-responsibility.

Planned (see [docs/](../../docs/)):
`RebateCalculator` (money math — the authoritative rebate/return calculation),
`AgreementGenerator` (renders + versions + snapshots agreements),
`PayoutService` (scheduling + disbursement helpers).

Money is `DECIMAL(15,2)` / integer minor units — never floats. Centralize rounding here.
