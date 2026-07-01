# Feature Spec — Public Site & SEO

**Stack:** Blade + Livewire 3 (server-rendered). **Auth:** none (guest). **Goal:** discoverable,
fast, trustworthy marketing site that converts visitors into registered investors. Built in
[Phase 3](../03-roadmap.md).

## Pages & routes

| Route | Page | Rendering | Key content |
|-------|------|-----------|-------------|
| `/` | Homepage | Blade | Overview, featured listings, How It Works, trust signals, CTAs |
| `/listings` | Browse | Livewire | Filter/search/sort, funding progress, pagination |
| `/listings/{slug}` | Detail | Blade + Livewire | Batch info, rebate table, live progress, Invest CTA |
| `/about` | About | Blade | Founder, mission, vision, offices (Dhaka/Sharjah/Dubai) |
| `/faq` | FAQ | Blade | Accordion from `faqs` table |
| `/contact` | Contact | Blade + Livewire | Form → `contact_messages` + email; office addresses |
| `/sitemap.xml` | Sitemap | Controller | Static pages + published listings |
| `/robots.txt` | Robots | static/route | Allow crawl, point to sitemap |

## Homepage sections

1. **Hero** — value proposition + primary CTAs (Register, Browse listings).
2. **How It Works** — Register → Browse → Invest → Earn (4-step visual).
3. **Featured listings** — cards with rebate rate, tenure options, target amount, funding progress bar.
4. **Why Bechakeena / trust signals** — 12+ years wholesale experience, verified network, global
   presence (Dhaka, Sharjah, Dubai), app-store presence.
5. **FAQ teaser + final CTA**.

## Listings browse (Livewire)

- **Filters:** category, tenure (6 / 12 mo), search (title/description), sort (newest, rebate %,
  funding progress, closing soon).
- **Cards:** cover image, title, category, best rebate rate, tenure options, target, `amount_raised`
  progress bar, "View / Invest".
- Only `status = published` listings appear. Livewire updates results without a full reload but URL
  reflects filters (query string) for shareability + SEO.
- Pagination (SEO-friendly, real URLs).

## Listing detail

- Full description, gallery, batch size, `listing_documents` links.
- **Rebate table**: each tenure → rebate %, example return for a sample amount.
- **Funding progress**: `amount_raised / target_amount`, investor count, closes_at.
- **Invest CTA**:
  - Guest → `/login?intended=/listings/{slug}` (store intended URL; see auth spec).
  - Authenticated + KYC approved → into the [investment flow](03-investment-flow.md).
  - Authenticated, KYC not approved → prompt to complete KYC first.
- Closed/paused/funded listings show status and disable the CTA.

## Contact page

- Form: name, email, phone (optional), subject, message → stored in `contact_messages`, admin
  notified by email; success/validation states via Livewire.
- Office addresses + support email pulled from `settings`.
- Spam protection: honeypot + rate limiting (and optional captcha if abused).

## SEO requirements (this is a headline proposal feature — treat as must-have)

- **Server-rendered HTML**: all content present in the initial response; nothing critical requires
  JS to render or index. Verify with JS disabled / view-source.
- **Meta**: unique `<title>` + meta description per page; listing pages use `meta_title` /
  `meta_description` (fallback to title/summary).
- **Open Graph + Twitter cards**: title, description, image (`og_image_path` for listings).
- **Canonical URLs** on every page; consistent trailing-slash policy.
- **Structured data (JSON-LD)**:
  - `Organization` (Bechakeena) sitewide.
  - `Product` / `Offer` (or `FinancialProduct`-style) per listing with name, description, image.
  - `BreadcrumbList` on detail pages.
  - `FAQPage` on the FAQ page.
- **XML sitemap** auto-includes published listings + static pages; regenerated on listing
  publish/close (or generated on request).
- **`robots.txt`** allows public pages, disallows `/dashboard`, `/admin`, auth/utility routes.
- Semantic HTML, descriptive alt text, heading hierarchy.

## Performance

- Public-page response caching (cache full HTML or heavy fragments; bust on listing changes).
- Image optimization + responsive `srcset`; lazy-load below the fold.
- CDN-served static assets (Vite build output) with long cache headers + hashed filenames.
- Target: Lighthouse SEO ≥ 95, Performance ≥ 90 on 4G, Core Web Vitals in "good."

## Acceptance criteria

- [ ] All public routes return 200 and render complete HTML without JS.
- [ ] Browse filters/search/sort work and are reflected in the URL.
- [ ] Detail page shows accurate live funding progress and rebate table.
- [ ] Guest "Invest" redirects to login and returns to the same detail page after auth.
- [ ] Every page has correct title/meta/canonical/OG; JSON-LD validates.
- [ ] `/sitemap.xml` lists only published listings + static pages; `robots.txt` blocks app routes.
- [ ] Contact form stores the message and emails the admin; spam-protected.
- [ ] Fully responsive across mobile/tablet/desktop; Lighthouse SEO ≥ 95.
