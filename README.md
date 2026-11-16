# ShopZone — E-Commerce Platform

A full-featured PHP + MySQL e-commerce application.

---

## 🚀 Quick Setup (XAMPP)

### 1. Copy Files
Place the `shopzone` folder inside your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\shopzone\   (Windows)
/Applications/XAMPP/htdocs/shopzone/   (Mac)
```

### 2. Import Database
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Create a new database named `shopzone`
3. Click **Import** → choose `shopzone/database.sql` → click **Go**

### 3. Configure Database
Open `shopzone/config.php` and verify:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // XAMPP default is empty
define('DB_NAME', 'shopzone');
define('SITE_URL', 'http://localhost/shopzone');
```

### 4. Set Permissions
Make sure the uploads folder is writable:
```bash
chmod 755 shopzone/uploads/products/
```

### 5. Visit Your Store
- **Store:** http://localhost/shopzone
- **Admin Panel:** http://localhost/shopzone/admin/dashboard.php

---

## 🔑 Default Credentials

| Role  | Email                | Password   |
|-------|----------------------|------------|
| Admin | admin@shopzone.com   | password   |

**⚠️ Change the admin password after first login!**

---

## 📁 Project Structure

```
shopzone/
├── config.php              ← Database config & constants
├── database.sql            ← Import this first!
├── index.php               ← Homepage
├── includes/
│   ├── functions.php       ← Core helpers (auth, flash, etc.)
│   ├── header.php          ← Navigation & HTML head
│   ├── footer.php          ← Footer & scripts
│   └── product_card.php    ← Reusable product card
├── pages/
│   ├── shop.php            ← Product listing + filters
│   ├── product.php         ← Product detail + reviews
│   ├── cart.php            ← Shopping cart
│   ├── cart_action.php     ← AJAX cart API
│   ├── wishlist.php        ← Wishlist page
│   ├── wishlist_action.php ← AJAX wishlist toggle
│   ├── checkout.php        ← Checkout + order placement
│   ├── orders.php          ← Order history (user)
│   ├── login.php           ← Login / Register / Forgot
│   ├── reset_password.php  ← Password reset
│   ├── profile.php         ← User profile
│   ├── apply_promo.php     ← AJAX promo code
│   └── logout.php          ← Logout
├── admin/
│   ├── dashboard.php       ← Stats + chart
│   ├── products.php        ← Add/Edit/Delete products
│   ├── orders.php          ← Manage + update orders
│   ├── users.php           ← View users
│   ├── categories.php      ← Manage categories
│   └── promos.php          ← Promo codes
├── assets/
│   ├── css/style.css       ← Full stylesheet
│   └── js/app.js           ← AJAX cart, dark mode, etc.
└── uploads/
    └── products/           ← Uploaded product images
```

---

## ✨ Features Included

### Authentication
- [x] Email/password login & registration
- [x] Secure bcrypt password hashing
- [x] Remember Me (30-day persistent cookie)
- [x] Password reset via email link
- [x] Session-based auth + auto-login via cookie

### Products
- [x] Grid layout with cards (image, price, rating, description)
- [x] Featured products
- [x] Sale prices with discount percentage badges
- [x] Stock tracking
- [x] Product detail page with image gallery
- [x] Category filtering
- [x] Search (name & description)
- [x] Sort by: newest, price, rating, popularity
- [x] Pagination

### Cart & Checkout
- [x] AJAX add/remove/update (no page reload)
- [x] Quantity controls
- [x] Dynamic total updates
- [x] Promo code / discount system
- [x] Free shipping over $50
- [x] Checkout form with order summary
- [x] Buy Now (single product checkout)

### Wishlist
- [x] Toggle wishlist via AJAX
- [x] Dedicated wishlist page

### Orders
- [x] User order history with expandable details
- [x] Admin order management + status updates
- [x] Status: Pending → Processing → Shipped → Delivered → Cancelled

### Reviews & Ratings
- [x] Star rating (1–5)
- [x] User reviews on product pages
- [x] Average rating displayed on cards

### Admin Panel
- [x] Revenue chart (Chart.js, last 6 months)
- [x] Stats: revenue, orders, products, users
- [x] Full product CRUD with image upload
- [x] Order status management
- [x] User management
- [x] Category management
- [x] Promo code management

### UI/UX
- [x] Dark mode toggle (persisted in localStorage)
- [x] Responsive / mobile-friendly (Bootstrap 5)
- [x] Smooth hover animations on cards
- [x] Toast notifications for cart/wishlist actions
- [x] Flash messages (success/error)
- [x] Hero section with stats
- [x] Topbar with promo announcement

---

## 🎨 Promo Codes (Seeded)

| Code       | Discount | Min Order |
|------------|----------|-----------|
| WELCOME10  | 10%      | $20       |
| SAVE20     | 20%      | $50       |
| MEGA30     | 30%      | $100      |

---

## 🛠️ Tech Stack

- **Backend:** PHP 8+ (Core PHP)
- **Database:** MySQL 5.7+ / MariaDB
- **Frontend:** HTML5, CSS3 (custom), Bootstrap 5.3
- **Icons:** Feather Icons
- **Charts:** Chart.js
- **Fonts:** Syne + DM Sans (Google Fonts)
- **Server:** XAMPP (Apache + MySQL)

---

## 🔒 Security Notes

- All user inputs are sanitized with `htmlspecialchars()`
- All DB queries use PDO prepared statements (no SQL injection)
- Passwords hashed with `password_hash()` (bcrypt)
- CSRF protection: add tokens to forms for production
- For production: enable HTTPS, set `secure` cookie flag, use `.env` for config
