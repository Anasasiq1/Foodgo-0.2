# FOODGO — Headless WordPress + WooCommerce Frontend

**Modern Decoupled React Frontend for WooCommerce & WordPress**  
*React 19 • TypeScript • Vite • Tailwind CSS • WooCommerce Store API • Foodgo Headless Core Plugin*

---

## 🌟 Architecture Overview

Foodgo operates as a fully decoupled **Headless WooCommerce Frontend**.

```
                ┌──────────────────────────┐
                │       FOODGO FRONTEND    │
                │                          │
                │ React + TypeScript       │
                │ Vite                     │
                │ Tailwind CSS             │
                │ Existing Foodgo UI       │
                └────────────┬─────────────┘
                             │
                             │ HTTPS API
                             ▼
                ┌──────────────────────────┐
                │       WORDPRESS          │
                │                          │
                │ WooCommerce              │
                │ Foodgo Headless Plugin   │
                │ Authentication           │
                │ REST API                 │
                │ Store API                │
                └────────────┬─────────────┘
                             │
                             ▼
                ┌──────────────────────────┐
                │ WordPress Database       │
                │ MySQL / MariaDB          │
                └──────────────────────────┘
```

- **Frontend**: Database-less React SPA with interactive product customizer (portion, spicy level, curry selection, toppings).
- **Backend**: Standard WordPress + WooCommerce managing all products, categories, stock, orders, coupons, taxes, and payments.
- **Plugin (`foodgo-headless-core`)**: Provides `/wp-json/foodgo/v1/config` automatic runtime configuration, Store API customization extensions, kitchen merchant tickets, and delivery logistics.

---

## 🚀 Quick Setup

### 1. WordPress & WooCommerce Setup
1. Install WordPress 6.0+ on your server or aaPanel.
2. Install & activate **WooCommerce**.
3. Upload and activate the `foodgo-headless-core` plugin from this repository.
4. In **WordPress Admin → Foodgo**, enter your frontend website URL.

### 2. Frontend Configuration
Set your WordPress URL in `.env`:
```bash
VITE_WP_URL="https://api.yourdomain.com"
```

### 3. Run or Build Frontend
```bash
# Development
npm run dev

# Production Build
npm run build
```

---

## 📖 Comprehensive Documentation

- [System Architecture](docs/ARCHITECTURE.md)
- [WordPress Setup Guide](docs/WORDPRESS-SETUP.md)
- [aaPanel Deployment Guide](docs/AAPANEL.md)
