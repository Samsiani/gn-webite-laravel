# Deployment Guide: laravel.gn.ge
## CyberPanel + AlmaLinux + OpenLiteSpeed + Cloudflare

---

## Step 1: Create Website in CyberPanel

1. Login to CyberPanel admin panel
2. **Websites → Create Website**
   - Domain: `laravel.gn.ge`
   - PHP: `8.3`
   - SSL: Let's Encrypt (or Cloudflare origin cert)
3. **Cloudflare**: Add `A` record for `laravel` pointing to your VPS IP

---

## Step 2: Create MySQL Database

In CyberPanel → **Databases → Create Database**:
- Database name: `gn_laravel`
- Username: `gn_laravel`
- Password: (generate strong password)

---

## Step 3: Install Redis (if not done)

```bash
# Check if Redis is running
redis-cli ping
# Should return: PONG

# If not installed:
dnf install redis -y
systemctl enable --now redis
```

---

## Step 4: Upload Project Files

SSH into your server:

```bash
cd /home/laravel.gn.ge/public_html

# Option A: Git clone (recommended)
git init
git remote add origin YOUR_REPO_URL
git pull origin main

# Option B: Upload via SFTP/SCP from local
# From your local machine:
rsync -avz --exclude='node_modules' --exclude='.git' --exclude='vendor' --exclude='database/database.sqlite' \
  /Users/george/Documents/gn-laravel/ \
  root@YOUR_VPS_IP:/home/laravel.gn.ge/public_html/
```

---

## Step 5: Install Dependencies on Server

```bash
cd /home/laravel.gn.ge/public_html

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node and build assets
npm install
npm run build

# Remove node_modules after build (saves disk)
rm -rf node_modules
```

---

## Step 6: Configure Environment

```bash
cp .env.example .env
nano .env
```

Set these values in `.env`:

```env
APP_NAME="GN Industrial"
APP_ENV=production
APP_KEY=  # Will generate below
APP_DEBUG=false
APP_URL=https://laravel.gn.ge

APP_LOCALE=ka
APP_FALLBACK_LOCALE=en

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gn_laravel
DB_USERNAME=gn_laravel
DB_PASSWORD=YOUR_DB_PASSWORD

# Session & Cache — use Redis
SESSION_DRIVER=redis
SESSION_ENCRYPT=true
SESSION_LIFETIME=120
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Search
SCOUT_DRIVER=collection

# Mail
MAIL_MAILER=log

# WooCommerce Import (remove after migration)
WC_URL=https://gn.ge
WC_KEY=
WC_SECRET=
```

Generate app key:
```bash
php artisan key:generate
```

---

## Step 7: Configure OpenLiteSpeed

In CyberPanel → **Websites → laravel.gn.ge → Rewrite Rules**

Add this rewrite rule:

```
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L]
```

**OR** (better method) — set document root to `public/`:

CyberPanel → **Websites → laravel.gn.ge → vHost Config**

Find `docRoot` and change:
```
docRoot /home/laravel.gn.ge/public_html/public
```

Then restart OpenLiteSpeed:
```bash
systemctl restart lsws
```

---

## Step 8: Set Permissions

```bash
cd /home/laravel.gn.ge/public_html

# Storage and cache must be writable
chmod -R 775 storage bootstrap/cache
chown -R nobody:nobody storage bootstrap/cache

# Create storage link
php artisan storage:link

# Create SQLite fallback (not used but prevents errors)
touch database/database.sqlite
```

---

## Step 9: Run Migrations & Seed

```bash
cd /home/laravel.gn.ge/public_html

# Run all migrations
php artisan migrate --force

# Seed initial data (languages, currency, tax, staff, attributes)
php artisan db:seed --force

# Clear and optimize
php artisan cache:clear
php artisan config:clear
```

---

## Step 10: Optimize for Production

```bash
cd /home/laravel.gn.ge/public_html

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Or run all at once:
php artisan optimize
```

---

## Step 11: Import Products & Blog

```bash
cd /home/laravel.gn.ge/public_html

# First set WC_KEY and WC_SECRET in .env, then:

# Import products (5 per category with images)
php artisan gn:import-wc --per-category=5 --images

# Import blog posts (10 with translations)
php artisan gn:import-blog --limit=10

# Clear cache after import
php artisan cache:clear
```

---

## Step 12: Change Admin Password

```bash
php artisan tinker --execute="
\$staff = \Lunar\Admin\Models\Staff::where('email', 'admin@gn.ge')->first();
\$staff->update(['password' => bcrypt('YOUR_SECURE_PASSWORD')]);
echo 'Password updated';
"
```

---

## Step 13: Cloudflare Settings

In Cloudflare dashboard for gn.ge:

1. **DNS**: `A` record → `laravel` → your VPS IP → Proxied (orange cloud)
2. **SSL/TLS**: Full (strict)
3. **Speed → Optimization**:
   - Auto Minify: HTML, CSS, JS
   - Brotli: ON
   - Early Hints: ON
4. **Caching → Configuration**:
   - Browser Cache TTL: 1 month
   - Always Online: ON
5. **Page Rules** (optional):
   - `laravel.gn.ge/build/*` → Cache Level: Cache Everything, Edge TTL: 1 month

---

## Post-Deployment Checklist

- [ ] Visit `https://laravel.gn.ge` — homepage loads
- [ ] Visit `https://laravel.gn.ge/admin` — login works
- [ ] Test product page, cart, checkout
- [ ] Test language switching (KA/EN/RU)
- [ ] Test search
- [ ] Test blog
- [ ] Check images load (storage symlink working)
- [ ] Run Lighthouse audit (target: 90+ performance)

---

## Troubleshooting

**500 error**: Check `storage/logs/laravel.log`
```bash
tail -50 /home/laravel.gn.ge/public_html/storage/logs/laravel.log
```

**Assets not loading**: Rebuild and check permissions
```bash
npm run build
php artisan storage:link
```

**Styles broken**: Clear caches
```bash
php artisan optimize:clear
php artisan optimize
```

**Redis connection error**: Check Redis is running
```bash
systemctl status redis
redis-cli ping
```

**Permission denied**: Fix ownership
```bash
chown -R nobody:nobody storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```
