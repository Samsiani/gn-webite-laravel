# GN Laravel — Project Rules

## Deployment

### Via GitHub (default for code changes)
Push to `main` → GitHub Actions auto-deploys to `laravel.gn.ge` in ~15 seconds.

Use for:
- PHP code changes (Livewire, models, controllers, middleware)
- Blade view changes
- CSS/JS changes (Vite rebuild runs automatically)
- Route changes
- Migration files (migration runs separately, see below)
- Config file changes
- Translation file changes

```bash
git add -A && git commit -m "message" && git push origin main
```

### Via SSH (server-only operations)
Connect: `ssh root@145.223.98.72` (password in memory)
PHP path: `/usr/local/lsws/lsphp84/bin/php`
Web root: `/home/laravel.gn.ge/public_html`

Use for:
- Running migrations: `php artisan migrate --force`
- Running seeders: `php artisan db:seed --force`
- Product import from WooCommerce: `php artisan gn:import-wc`
- Blog import: `php artisan gn:import-blog`
- Meilisearch re-index: `php artisan scout:flush 'Lunar\Models\Product' && php artisan scout:import 'Lunar\Models\Product'`
- Meilisearch sync settings: `php artisan scout:sync-index-settings`
- Tinker / debugging: `php artisan tinker`
- Checking logs: `tail storage/logs/laravel.log`
- Redis/cache flush: `redis-cli FLUSHDB`
- Composer install (after adding packages): `composer install --no-dev`
- npm install (after adding packages): `npm install`
- Emergency fixes that can't wait for GitHub Actions

### Never do via GitHub Actions
- Database migrations (need manual verification)
- Bulk data imports (timeout risk)
- Destructive operations (cache flush, DB reset)
- Anything requiring interactive input

## Server Stack
- AlmaLinux + CyberPanel + OpenLiteSpeed
- PHP 8.4 at `/usr/local/lsws/lsphp84/bin/php`
- MySQL (`gn_laravel` database)
- Redis (sessions + cache)
- Meilisearch v1.6.2 on `127.0.0.1:7700` (systemd service)
- Node.js + Vite for asset builds

## Meilisearch
- Master key: stored in `.env` as `MEILISEARCH_KEY`
- Index: `products` — 874 documents
- Searchable: `name_ka`, `name_en`, `name_ru`, `skus` (names + SKU only, no descriptions)
- Filterable: `status`, `brand`, `product_type`, `skus`, `collection_ids`, `price`, `__soft_deleted`
- Sortable: `created_at`, `updated_at`, `price`, `name_ka`, `name_en`, `name_ru`
- Custom indexer: `app/Search/ProductIndexer.php` (extends Lunar's, adds `price` + `collection_ids`)
- Index settings defined in `config/scout.php` → `meilisearch.index-settings.products`
- After changing settings: `php artisan scout:sync-index-settings` (via SSH)
- After changing indexer fields: `php artisan scout:flush ... && scout:import ...` (via SSH)

## Key Paths
- Web root: `/home/laravel.gn.ge/public_html`
- Logs: `/home/laravel.gn.ge/public_html/storage/logs/laravel.log`
- .env: `/home/laravel.gn.ge/public_html/.env` (never in git)
- Settings: `/home/laravel.gn.ge/public_html/storage/site-settings.json`

## Auth
- `web` guard for storefront users (login/register/my-account)
- `staff` guard for Filament admin (/admin)
- User model needs `carts()`, `customers()`, `orders()` relationships for Lunar

## Search Architecture
- ShopPage handles both browsing and search (`/shop?q=keyword`)
- `/search` is an alias to ShopPage (backward compat)
- LiveSearch header dropdown → "View all results" links to `/shop?q=`
- SearchPage.php was removed — all search goes through ShopPage
- Meilisearch handles search/filter/sort, then Eloquent eager-loads relationships

## URLs
- `/shop`, `/shop?q=keyword` — unified catalog + search
- `/login`, `/register`, `/my-account` — storefront auth
- `/contact` — contact form (sends via SMTP from admin settings)
- `/admin` — Filament/Lunar admin panel
- `/admin/site-settings` — SMTP + contact email config

## Media
- Products: `ProductMediaDefinitions` — thumb (150), small (300), medium (400), large (800)
- Collections: `CollectionMediaDefinitions` — thumb (150), small (300)
- BlogPosts: conversions defined in model — thumb (600x400 crop), large (1200x800)
- Config: `config/lunar/media.php` (products + collections only, blog is in model)
- Regenerate: `php artisan media-library:regenerate --only-missing --force` (via SSH)
- Blog media: must reimport posts if conversions change (conversions generated on upload)
- ALWAYS use `getFirstMediaUrl('collection', 'thumb')` with fallback for all thumbnails
- Homepage blog section, blog archive, blog sidebar — all must use `'thumb'` conversion

## SEO
- `app/Services/SeoHelper.php` — robots(), title(), defaultDescription(), defaultOgImage(), globalSchema()
- Admin configurable: `/admin/site-settings` → SEO Global + LocalBusiness Schema sections
- Layout head: dynamic meta title/desc, OG tags, Twitter cards, canonical, hreflangs
- Per-page SEO from Livewire components via `->layout()` second param
- Schema.org JSON-LD: Organization, WebSite+SearchAction, Product+BreadcrumbList, Article+BreadcrumbList, CollectionPage+BreadcrumbList
- Robots: controlled from admin settings (noindex default for staging)
- Sitemap: `php artisan sitemap:generate` (via SSH, daily cron at 4 AM)
  - Rank Math pattern: `sitemap.xml` → index with sub-sitemaps
  - `page-sitemap.xml`, `product-sitemap{N}.xml` (paginated 1000/file), `product_cat-sitemap.xml`, `post-sitemap.xml`, `category-sitemap.xml`
  - XSL stylesheet at `/sitemap.xsl` for visual browser display
  - All sitemaps include hreflang alternates (ka/en/ru)
- `robots.txt` in `public/` — allows all crawlers, points to sitemap index
- Cloudflare note: Facebook/Google crawlers blocked by Cloudflare JS challenge on Free plan. ASN IP Access Rules (AS32934, AS63293) or disabling Cloudflare proxy required for social crawlers.

## Blog Import
- Command: `php artisan gn:import-blog --limit=5`
- Imports from WooCommerce REST API with ka/en/ru translations
- Converts HTML to block format, maps product links via wp_id_map
- Featured images get thumb + large WebP conversions on upload
- To re-import: delete posts first via tinker, then run import command

## Mobile
- Header: logo (gn-mobile-logo.png) | full-width search bar | hamburger menu
- Drawer: slides from RIGHT side, includes language switcher + nav + contact
- Bottom navbar: fixed, 4 icons — Home, Shop, Cart, Account
- `pb-16` on main content to avoid bottom nav overlap
- `font-size: 16px !important` on all inputs to prevent iOS zoom
- Search dropdown: `fixed left-2 right-2` on mobile (near full width)
- Top bar (phone/email) hidden on mobile — shown in drawer instead
- Favicon: `/favicon.png` (32x32) from GN icon
- Apple touch icon: `/apple-touch-icon.png` (180x180)
