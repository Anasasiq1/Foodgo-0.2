# Deploying Foodgo on aaPanel (LNMP Stack)

## Architecture Options

### Option A: Subdomain Setup (Recommended)
- `foodgo.yourdomain.com` → React Frontend (Nginx static site pointing to `dist/`)
- `api.yourdomain.com` → WordPress + WooCommerce backend (PHP 8.1 / MySQL)

### Option B: Reverse Proxy / Monolith
- `foodgo.yourdomain.com/` → React Frontend
- `foodgo.yourdomain.com/wp-json/` → Reverse Proxied to WordPress

## Step-by-Step aaPanel Deployment

### 1. Backend Setup (WordPress + WooCommerce)
1. In aaPanel, create a new site `api.yourdomain.com` with PHP 8.0+ and MySQL.
2. Install WordPress using aaPanel One-Click Deployment or upload the latest WordPress zip.
3. Install WooCommerce and activate Cash on Delivery (COD).
4. Upload `foodgo-headless-core` to `wp-content/plugins/`.
5. Activate the plugin in WordPress Admin.
6. Under **Foodgo → Settings**, set **Frontend Website URL** to `https://foodgo.yourdomain.com`.

### 2. Frontend Setup (React / Vite)
1. Build the production assets locally or on server:
   ```bash
   npm run build
   ```
2. In aaPanel, create a static site `foodgo.yourdomain.com`.
3. Upload the contents of the `dist/` directory to the website root (`/www/wwwroot/foodgo.yourdomain.com/`).
4. Add the Nginx URL Rewrite rule for single-page apps (SPA):
   ```nginx
   location / {
       try_files $uri $uri/ /index.html;
   }
   ```
5. Obtain free SSL certificates via aaPanel Let's Encrypt for both domains.
