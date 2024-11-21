# 🍰 Online Bay Bakery

<p align="center">
  <img src="images/cake.jpg" alt="Online Bay Bakery" width="600" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);" />
</p>

<p align="center">
  <a href="#features"><img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version" /></a>
  <a href="#features"><img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" /></a>
  <a href="#features"><img src="https://img.shields.io/badge/PHPMailer-SMTP-FF6F00?style=for-the-badge&logo=gmail&logoColor=white" alt="PHPMailer" /></a>
  <a href="#features"><img src="https://img.shields.io/badge/Twilio-SMS_Alerts-F22F46?style=for-the-badge&logo=twilio&logoColor=white" alt="Twilio" /></a>
  <a href="#license"><img src="https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge" alt="License" /></a>
</p>

---

## 📖 Overview

**Online Bay Bakery** is a full-featured web-based e-commerce platform built for artisanal bakeries and confectioneries. It offers a seamless customer shopping experience — from browsing handcrafted bakery items by category to managing carts, completing checkout via popular regional payment gateways (JazzCash, EasyPaisa), receiving real-time SMS order confirmations via Twilio, and self-service password recovery using Gmail SMTP.

---

## ✨ Features

- 👤 **Customer Authentication & Profile Management**
  - Secure registration and login with bcrypt password hashing (`PASSWORD_DEFAULT`).
  - Session hijacking protection (`session_regenerate_id`).
  - Account settings for updating personal info and changing passwords.
- 🥐 **Dynamic Product Catalog**
  - Products categorized by cakes, cookies, artisan breads, and pastries.
  - Category filtering and detailed product listings.
- 🛒 **Shopping Cart & Checkout**
  - Real-time cart quantity tracking and instant total calculations.
  - Multi-method checkout support (JazzCash, EasyPaisa, Cash on Delivery).
- 📱 **Real-time Twilio SMS Notifications**
  - Automated SMS confirmation sent upon successful order placement.
- 🔑 **Secure Password Recovery**
  - Tokenized password reset links sent via Gmail SMTP through PHPMailer.
  - Single-use, time-sensitive cryptographic reset tokens (`bin2hex(random_bytes(32))`).
- 🛡️ **Security-Hardened Architecture**
  - Zero hardcoded secrets: all credentials managed via `.env` environment variables.
  - SQL injection protection using prepared statements (`mysqli::prepare`).
  - Strict `.gitignore` protecting dependencies and sensitive environment configs.

---

## 🛠️ Tech Stack

| Component | Technology |
| :--- | :--- |
| **Backend** | PHP 8.x |
| **Database** | MySQL / MariaDB |
| **Styling** | Vanilla CSS3 (Responsive UI) |
| **Mailing** | PHPMailer (`^6.9`) via Google SMTP |
| **SMS Gateway** | Twilio SDK (`^8.2`) |
| **Dependency Manager** | Composer |

---

## 📁 Project Structure

```text
Bakery/
├── images/                  # Product and UI images
├── .env.example             # Template for environment variables (Safe for VCS)
├── .gitignore               # Strict gitignore rules (protects .env & vendor/)
├── account_settings.php     # User account and password updates
├── add_to_cart.php          # Cart addition handler
├── category.php             # Category-specific product showcase
├── checkout.php             # Checkout and payment method selection
├── composer.json            # PHP project dependencies
├── composer.lock            # Locked dependency versions
├── config.php               # Centralized secure configuration & DB connector
├── forgot_password.php      # SMTP email password reset initiator
├── index.php                # Landing and customer sign-in portal
├── login.php                # Authentication controller
├── logout.php               # Session termination
├── online_bay_bakery.sql    # Database schema with demo seed data
├── process_payment.php      # Order completion and Twilio SMS dispatcher
├── products.php             # Main category catalog view
├── register.php             # User registration controller
├── reset_password.php       # Password reset fulfillment
├── style.css                # Global application stylesheet
├── thank_you.php            # Order confirmation view
└── view_cart.php            # Customer cart review page
```

---

## 🚀 Getting Started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) or [WampServer](https://www.wampserver.com/) (Apache + PHP 8.0+ & MySQL)
- [Composer](https://getcomposer.org/)

### 1. Clone the Repository

```bash
git clone https://github.com/faisalmanzoorbhatti/Bakery.git
cd Bakery
```

### 2. Configure Environment Variables

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Open `.env` and fill in your local database credentials and API keys:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=online_bay_bakery

# Base Application URL
APP_URL=http://localhost/Bakery

# Gmail SMTP Settings
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_gmail_app_password

# Twilio SMS Credentials
TWILIO_ACCOUNT_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_PHONE_NUMBER=your_twilio_phone_number
```

### 3. Import Database

1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Create a database named `online_bay_bakery`.
3. Import the sanitized schema file `online_bay_bakery.sql`.

> **Demo Accounts (Password: `password123`):**
> - Customer: `customer@example.com`
> - Admin: `admin@example.com`

### 4. Install Dependencies

```bash
composer install
```

### 5. Run the Application

Place the folder in your web server root (e.g., `C:/xampp/htdocs/Bakery`) and navigate to:
```text
http://localhost/Bakery/index.php
```

---

## 🔒 Security Best Practices

- **Never commit `.env`**: Always ensure `.env` remains in `.gitignore`.
- **Use App Passwords**: For Gmail SMTP, generate a Google App Password rather than your account master password.
- **Rotate Credentials**: If any API key is ever accidentally exposed, rotate it immediately in the respective service console (Twilio / Google Account).

---

## 👤 Author

**Faisal Manzoor Bhatti**
- GitHub: [@faisalmanzoorbhatti](https://github.com/faisalmanzoorbhatti)

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).
