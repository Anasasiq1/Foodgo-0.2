# Foodgo Setup & Deployment Guides

## 1. WordPress Installation
1. Install standard WordPress 6.0+ on your server or aaPanel (LAMP/LNMP stack with PHP 7.4+ and MySQL/MariaDB).
2. Set Permalink Settings to `Post name` (`/sample-post/`) under **Settings → Permalinks** (Required for REST API).

## 2. WooCommerce Installation
1. In WordPress Admin, navigate to **Plugins → Add New**.
2. Search for **WooCommerce** and click **Install Now**, then **Activate**.
3. Complete the store setup wizard (currency, location, payments like Cash on Delivery).

## 3. Foodgo Headless Core Plugin Installation
1. Copy the `foodgo-headless-core` folder from this repository to your WordPress plugins directory (`wp-content/plugins/foodgo-headless-core`).
2. In WordPress Admin, go to **Plugins → Installed Plugins** and click **Activate** under **Foodgo Headless Core**.
3. Go to the new **Foodgo** menu in the WordPress sidebar.
4. Enter your React frontend URL in **Frontend Website URL** (e.g. `https://foodgo.yourdomain.com`).
5. Save settings.

## 4. Connecting the React Frontend
Set your WordPress URL in your frontend environment:
```bash
# .env in React project
VITE_WP_URL="https://api.yourdomain.com"
```
Or build and deploy to any static host (Vercel, Netlify, aaPanel, Cloudflare Pages).
The frontend will auto-discover all products, categories, currencies, and payment methods from WooCommerce!
