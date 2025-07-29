# Jowaki Electrical Services – Redesign Prompt

## ✅ Goal

Upgrade the UI structure and theme of this PHP-based eCommerce and service platform. **All current logic and functionality must remain** (login, signup, store, admin dashboard, cart, etc.). We are only improving the **visual structure and layout** using Tailwind CSS design principles and modern UI/UX patterns.

---

## 📦 Tech Stack (Do Not Change)

- PHP for backend logic
- JS (vanilla, no React)
- CSS split into page-specific files (index.css, login.css, store.css, etc.)
- HTML inside `.php` or `.html` files
- JS handles interactive logic (cart, login switcher, etc.)
- MySQL DB (unchanged)

---

## 🧠 Functionality to Keep As-Is

- Account system (login/signup/admin sessions)
- Shopping cart logic
- Product and service views
- Admin dashboard logic
- Order and stock management
- PHP POST/GET form logic

---

## 🎨 Visual Structure to Apply

### 1. Hero Section
- Large background with mission statement
- CTA buttons: "Shop Now", "Contact Us"
- Professional background image from `IMG_*.jpg`

### 2. Features Section
- Highlight key offerings (Fast Delivery, Certified Products, Expert Support)
- 3 columns, responsive

### 3. Services Section
- Residential, Commercial, Emergency Services
- Image cards or icons

### 4. Featured Products
- Grid layout of top products (reuse existing store logic)

### 5. Footer
- Company info
- Quick navigation
- Contact details and icons

---

## 🎨 Style Guidelines

- **Tailwind CSS-like utility classes** or equivalent if not using Tailwind directly
- Colors:
  - Primary: `hsl(207, 90%, 54%)` (Blue)
  - Secondary: `hsl(45, 93%, 47%)` (Yellow)
  - Accent: `hsl(151, 55%, 42%)` (Green)
- Mobile-first
- Smooth hover states and animations
- Buttons and cards with shadows and rounded corners

---

## ✅ Outcome

A responsive and clean visual layout across:
- `index.php` (home)
- `Store.php`
- `Service.html`
- `login.html`/`login.php`

No PHP logic should be broken. Existing JavaScript should continue working.

