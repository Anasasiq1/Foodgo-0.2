# Foodgo — Headless WordPress + WooCommerce Architecture

## 1. System Overview

Foodgo is a decoupled, headless food ordering platform combining a high-performance React (Vite + TypeScript + Tailwind CSS) client with a robust WordPress + WooCommerce backend.

```
┌─────────────────────────────────────────────────────────┐
│                    FOODGO FRONTEND                      │
│                                                         │
│  React 19 + TypeScript + Vite + Tailwind CSS            │
│  • Mobile-First Responsive Storefront                   │
│  • Food Customization UI (Spice, Portion, Toppings)     │
│  • WooCommerce Store API Cart & Checkout Integration    │
│  • Dynamic Runtime Backend Auto-Configuration           │
└────────────────────────────┬────────────────────────────┘
                             │
                             │ HTTPS JSON APIs
                             │ (Store API / REST API)
                             ▼
┌─────────────────────────────────────────────────────────┐
│                  WORDPRESS BACKEND                      │
│                                                         │
│  WordPress 6.x + WooCommerce 8.x+                       │
│  • Foodgo Headless Core Plugin                          │
│  • Products, Inventory & Tax Engine                     │
│  • Native WooCommerce Payment Gateways                  │
│  • Store API Cart & Orders                              │
└────────────────────────────┬────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────┐
│                 WORDPRESS DATABASE                      │
│                                                         │
│  MySQL / MariaDB (Managed entirely by WordPress)        │
│  • wp_posts, wp_postmeta                                │
│  • wp_wc_orders, wp_wc_order_items                      │
└─────────────────────────────────────────────────────────┘
```

## 2. Key Principles

1. **WordPress + WooCommerce as Single Source of Truth**:
   The frontend has **zero** custom database. Products, categories, variations, stock, taxes, orders, coupons, customers, and payment settings live solely in WordPress.
2. **Dynamic Configuration Discovery**:
   On startup, the frontend queries `/wp-json/foodgo/v1/config` to discover currency symbols, active modules, available payment gateways, and store settings without requiring frontend recompilation.
3. **Food Customization Integrity**:
   Food customizations (Spiciness, Curry/Salna units, extra toppings, special instructions) are passed as Store API cart item extensions and recorded directly on WooCommerce order line items.
