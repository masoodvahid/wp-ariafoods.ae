# Aria Foods Modern — WordPress theme

Plugin-free B2B theme for [Aria Foods](https://ariafoods.co): Iranian dates and date ingredients for wholesale buyers.

- Product catalogue with specification sheets (`/product/…`, `/product-category/…`, `/dates-products/`)
- Quote, sample, contact and order-tracking forms (AJAX), saved under **Inquiries** and emailed
- SEO built in: titles, meta descriptions, canonical, Open Graph, JSON-LD, core XML sitemap
- Fast: one CSS + one deferred JS file, self-hosted font, click-to-load YouTube, instant page navigation
- Products like WooCommerce, without WooCommerce: `product` post type, product categories, catalogue page, SKU, image gallery, schema.org Product
- Customer accounts (`customer` role): sign-up, sign-in, password reset, company profile and request history on `/my-account/`
- One-click importer from ariafoods.co (**Appearance → Import content**)
- Upgrade tool for older products, e.g. from WooCommerce (**Products → Upgrade products**): SKU, gallery, price, stock, spec tables, Yoast SEO, categories
- Updates itself from this repository (main branch)

## Install

1. Download this repository as ZIP, or use the theme zip you were given.
2. WordPress → Appearance → Themes → Add New → Upload Theme → Activate. The theme folder must be named `ariafoods`.
3. Appearance → Customize → *Aria Foods settings* for contact details, the Dubai branch and fonts.

## Updates from GitHub

The site reads `Version:` in `style.css` on the `main` branch.

1. Change the code and raise `Version:` in `style.css` (e.g. `1.6.0` → `1.6.1`).
2. Commit and push to `main`.
3. In WordPress click **Check GitHub now** (Appearance → Themes, Dashboard → Updates, or the admin bar), then **Update now**.

No tags or releases needed. Files in `.github`, `README.md` and git settings are left out of the downloaded zip (`.gitattributes`).

### Private repository

Add a fine-grained GitHub token (read-only *Contents* on this repository) to `wp-config.php`:

```php
define( 'ARIA_GITHUB_TOKEN', 'github_pat_xxx' );
```
