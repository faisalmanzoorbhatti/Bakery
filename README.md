# 🥖 L'Atelier Gourmet Bakery & Boulangerie

<p align="center">
  <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=1200&q=80" alt="L'Atelier Gourmet Bakery" width="100%" style="border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);" />
</p>

<p align="center">
  <a href="#features"><img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version" /></a>
  <a href="#features"><img src="https://img.shields.io/badge/MariaDB-13.0+-4479A1?style=for-the-badge&logo=mariadb&logoColor=white" alt="Database" /></a>
  <a href="#features"><img src="https://img.shields.io/badge/CSS3-Luxury_Artisanal_UI-orange?style=for-the-badge" alt="CSS3" /></a>
  <a href="#features"><img src="https://img.shields.io/badge/Items-42_Gourmet_Bakes-amber?style=for-the-badge" alt="Items" /></a>
  <a href="#license"><img src="https://img.shields.io/badge/License-Proprietary-red.svg?style=for-the-badge" alt="License" /></a>
</p>

---

## 📖 Overview

**L'Atelier Gourmet Bakery** is an e-commerce platform crafted for high-end bakeries, artisan patisseries, and gourmet confectioneries. Inspired by world-renowned European boulangeries, it delivers a pitch-ready portfolio experience built to impress bakery owners looking to scale online delivery operations.

The platform comes pre-loaded with **42 handcrafted gourmet bakery items** across 6 rich categories, live instant search, category pill filters, animated toast notifications, a responsive shopping cart with coupon support (`BAKERY10`), multi-channel delivery slot scheduling, customer order tracking, and an **Admin Control Portal** for live order fulfillment and menu management.

---

## ✨ Flagship Features

### 🥐 1. 42 Handcrafted Bakery Items (6 Gourmet Categories)
* **Artisanal Cakes & Cheesecakes (8):** Belgian Dark Chocolate Ganache Cake, Signature Lotus Biscoff Baked Cheesecake, Red Velvet Royale, NY Baked Berry Cheesecake, Salted Caramel Pecan Cake, Classic Italian Tiramisu, Wild Strawberry Chiffon, Spiced Carrot Walnut Cake.
* **Fresh Sourdough & Crusty Breads (8):** San Francisco Artisan Sourdough (48-Hr Ferment), Traditional Parisian Baguette, Rosemary Sea Salt Focaccia, Golden Brioche Loaf, Ancient Grains Loaf, Garlic Herb Pull-Apart, Italian Olive Ciabatta, Dark Caraway Rye.
* **Viennoiserie & French Pastries (8):** 100% French Butter Croissants, Pain au Chocolat, Roasted Almond Frangipane Croissant, Bronte Pistachio Custard Danish, Cinnamon Brioche Swirl, Fresh Berry Custard Tartlet, Parisian Chocolate Éclair, Vanilla Mille-Feuille.
* **Gourmet Cookies & Brownies (7):** Valrhona Double Dark Sea Salt Cookies, Red Velvet White Choc Chunk, Fudgy Walnut Brownie Slab, Paris Macaron Luxe Box (6 Pcs), Salted Caramel Blondie, Molten Nutella Lava Cookie, Kyoto Matcha White Choc Cookie.
* **Savory Bites & Artisan Quiches (6):** Smoked Chicken & Wild Mushroom Quiche, Spinach & Feta Danish, Woodfired Beef Pastrami Calzone, Crispy Chicken Patties, Tandoori Paneer Turnover, Farmhouse Chicken Pot Pie.
* **Handcrafted Beverages & Roastery (5):** Madagascar Vanilla Bean Velvet Latte, Iced Salted Caramel Macchiato, Belgian Dark Hot Cocoa, Japanese Uji Matcha Latte, Citrus Mint Sparkler.

### 📱 2. 100% Fluid Responsive Design
* Handcrafted responsive layout engineered for Mobile (360px+), Tablets/iPads, Laptops, and Ultrawide monitors.
* No empty sidebars or rigid widths — uses modern fluid container tokens (`clamp(16px, 3.5vw, 40px)`).
* Mobile-friendly bottom floating cart bar and touch-friendly steppers.

### 🛡️ 3. Operations Admin Portal (`admin_dashboard.php` & `admin_products.php`)
* **Live Revenue Analytics:** Total sales volume, order counts, customer registrations, and active menu items.
* **Orders Management:** Full customer contact details (Name, Email, Phone), delivery time window, and expandable **"View Items"** breakdown with high-res product photos and quantities.
* **Fulfillment Pipeline:** Update order statuses live (`Paid` &rarr; `Preparing (In Oven)` &rarr; `Out for Delivery` &rarr; `Completed`).
* **Menu Publisher:** Add new bakery items, prices, descriptions, and custom badges (`⭐ Best Seller`, `🔥 Trending`, `🌾 48-Hr Ferment`).

### 🛍️ 4. Customer Shopping Experience
* Instant client-side search and category filtering with zero page reloads.
* Interactive stepper quantity selector (`- 1 +`) with seamless AJAX Add-to-Cart.
* Dynamic Free Delivery progress bar (unlocks free shipping over $35).
* Coupon code support (use `BAKERY10` for 10% discount).
* Delivery time slot selection (Morning, Afternoon, Evening, or Immediate Express 45 mins).
* Special Baker's note field for custom cake messages.
* Customer Order History (`my_orders.php`) with milestone timeline tracking.

---

## 🧪 Quick Testing Credentials

The login page features **1-click autofill buttons** for rapid testing:

| Role | Email | Password | Destination |
| :--- | :--- | :--- | :--- |
| **👤 Customer** | `customer@example.com` | `password123` | Storefront (`products.php`) |
| **🛡️ Admin** | `admin@example.com` | `password123` | Admin Portal (`admin_dashboard.php`) |

---

## 🛠️ Tech Stack

| Component | Technology |
| :--- | :--- |
| **Backend** | PHP 8.2+ |
| **Database** | MariaDB 13.0+ / MySQL 8.0+ |
| **Styling** | Vanilla CSS3 (Custom Design System, Typography, CSS Variables) |
| **Typography** | Google Fonts (*Outfit* & *Playfair Display*) |
| **Images** | High-Resolution Unsplash Food Photography CDN |
| **Mailing** | PHPMailer (`^6.9`) via Google SMTP |
| **SMS Alerts** | Twilio REST SDK (`^8.2`) |

---

## 🚀 Local Installation & Setup

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/faisalmanzoorbhatti/Bakery.git
   cd Bakery
   ```

2. **Configure Environment:**
   ```bash
   cp .env.example .env
   # Update DB_HOST, DB_USER, DB_PASS in .env if needed
   ```

3. **Import Database Schema:**
   ```bash
   mariadb -u root -p online_bay_bakery < online_bay_bakery.sql
   ```

4. **Launch Local PHP Server:**
   ```bash
   php -S localhost:8000
   ```
   Open **`http://localhost:8000`** in your browser.

---

## 📄 License & Intellectual Property
This project is protected under a [Proprietary & Commercial Evaluation License](LICENSE) &copy; 2024-2026 **Faisal Manzoor Bhatti**. Unauthorized commercial deployment, reselling, or redistribution without permission is strictly prohibited. For commercial licensing or custom bakery deployments, contact `faisalmanzoor11147@gmail.com`.
