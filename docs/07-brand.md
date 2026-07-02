# 07 — Brand Assets

Canonical brand assets for the Bechakeena Investment Platform, collected from the live company
site **[bechakeena.com](https://bechakeena.com/)** (2026-07-02). Use these for the public site,
emails, favicon, and the investor/admin UIs.

> ✅ **Applied (2026-07-02).** The raspberry scale below is live in
> [`resources/css/app.css`](../resources/css/app.css) (`--color-brand-*` + `--color-ink`), and the
> Inertia progress bar (`resources/js/app.jsx`) uses `#b71e61`. This doc remains the source of truth
> for the values.

## Logo

- **Local copy:** [`docs/assets/brand/bechakeena-logo.jpg`](assets/brand/bechakeena-logo.jpg)
- **Source URL:** `https://bechakeena.com/wp-content/uploads/logo-alt-e1762073168842.jpg`
- **Format / size:** JPEG, 539 × 609 px, on a white background.
- **Mark:** four stylized `b`/`d`/`a` glyphs (an abstract monogram) in raspberry magenta, inside a
  square bracket-style frame, with the **BECHAKEENA** wordmark in dark charcoal below.
- The same image is used as the site favicon (`32×32`, `192×192`) and apple-touch-icon.

**Favicons (generated 2026-07-02)** from the logo (monogram squared on white) into `public/`:
`favicon.ico` (16/32/48), `favicon-16x16.png`, `favicon-32x32.png`, `apple-touch-icon.png` (180),
`icon-192.png`, `icon-512.png`. Linked from both root templates (`resources/views/app.blade.php`
and `resources/views/components/layouts/app.blade.php`).

**TODO before production:** obtain a vector (SVG) and a transparent-background PNG from Bechakeena.
The current asset is a raster JPEG on solid white — fine for reference, not ideal for crisp/dark UIs.

## Brand colors

Sampled directly from the logo pixels (the site's CSS only contained the default WordPress/Gutenberg
block palette, which is **not** brand-meaningful).

| Role | Hex | RGB | Notes |
|------|-----|-----|-------|
| **Primary — Raspberry** | `#B71E61` | `rgb(183, 30, 97)` | The logo mark. HSL ≈ 333°, 70%, 41%. Brand primary. |
| **Neutral — Charcoal** | `#403F40` | `rgb(64, 63, 64)` | The wordmark text. Use for headings/body ink, not pure black. |
| **Surface — White** | `#FFFFFF` | `rgb(255,255,255)` | Logo background / page surface. |

### Tailwind-style tonal scale (`--color-brand-*`)

Derived from the primary `#B71E61` (anchored at `600`). Live in
[`resources/css/app.css`](../resources/css/app.css):

```css
/* Bechakeena brand palette — raspberry magenta (from logo). Use brand-* tokens, not raw hex. */
--color-brand-50:  #faeff4;
--color-brand-100: #f7dee9;
--color-brand-200: #efb8d0;
--color-brand-300: #e788b2;
--color-brand-400: #dd4b8b;
--color-brand-500: #d02570;
--color-brand-600: #b71e61; /* ← primary, matches the logo */
--color-brand-700: #95184f;
--color-brand-800: #7b1441;
--color-brand-900: #5f1133;
```

Suggested neutral ink token to pair with the wordmark:

```css
--color-ink: #403f40; /* charcoal wordmark color for headings/body text */
```

### Usage guidance

- **Primary actions / links / active states:** `brand-600` (`#B71E61`); hover → `brand-700`.
- **Text on `brand-600`:** white passes WCAG AA (contrast ≈ 5.6:1 for normal text).
- **Body/heading ink:** `--color-ink` (`#403F40`) on white, not `#000`.
- **Tints for backgrounds/badges:** `brand-50`/`brand-100`; borders `brand-200`.
- Keep the magenta as an accent; avoid large flat magenta fills on long-form pages.

## Provenance

Collected 2026-07-02 from `https://bechakeena.com/`. Brand name **Bechakeena**; company tagline on
the site: *"B2B Laptop Wholesale Platform — connecting retailers and wholesalers nationwide."* The
investment platform is a sibling product to that wholesale business.
