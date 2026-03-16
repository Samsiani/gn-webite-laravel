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
PHP path: `/usr/local/lsws/lsphp83/bin/php`
Web root: `/home/laravel.gn.ge/public_html`

Use for:
- Running migrations: `php artisan migrate --force`
- Running seeders: `php artisan db:seed --force`
- Product import from WooCommerce: `php artisan gn:import-wc`
- Blog import: `php artisan gn:import-blog`
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
- PHP 8.3 at `/usr/local/lsws/lsphp83/bin/php`
- MySQL (`gn_laravel` database)
- Redis (sessions + cache)
- Node.js + Vite for asset builds

## Key Paths
- Web root: `/home/laravel.gn.ge/public_html`
- Logs: `/home/laravel.gn.ge/public_html/storage/logs/laravel.log`
- .env: `/home/laravel.gn.ge/public_html/.env` (never in git)
- Settings: `/home/laravel.gn.ge/public_html/storage/site-settings.json`

## Auth
- `web` guard for storefront users (login/register/my-account)
- `staff` guard for Filament admin (/admin)
- User model needs `carts()`, `customers()`, `orders()` relationships for Lunar

## URLs
- `/login`, `/register`, `/my-account` — storefront auth
- `/contact` — contact form (sends via SMTP from admin settings)
- `/admin` — Filament/Lunar admin panel
- `/admin/site-settings` — SMTP + contact email config
