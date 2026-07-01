# Enums

PHP 8 backed enums for all status/type columns — use these instead of magic strings.

Planned (see [docs/02-data-model.md](../../docs/02-data-model.md)):
`KycStatus`, `UserStatus`, `ListingStatus`, `InvestmentStatus`, `PaymentMethod`, `PaymentStatus`,
`PayoutStatus`, `AdminRole`.

Cast them on models via `casts()` so Eloquent hydrates enum instances automatically.
