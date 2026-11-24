# MYSLIP.IN 🧾

> **Simple Billing, Invoicing & Accounting Software for Growing Businesses.**

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net/)
[![Database](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)](#license)

**MYSLIP.IN** is an ultra-fast, lightweight, multi-tenant cloud billing and accounting web application crafted specifically for Indian businesses, SMEs, retailers, and wholesalers. It provides full GST compliance, point-of-sale invoicing, purchase tracking, voucher accounting, and comprehensive financial reporting.

---

## 🚀 Key Highlights
- **Full Indian GST Compliance**: Automatic CGST, SGST, IGST calculations, HSN/SAC codes, GSTR-1, and GSTR-3B summaries.
- **Fast Point of Sale (POS)**: Instant barcode scan, item search, line-item discounts, round-off, and customer ledger updates.
- **Multi-Tenant / Multi-Company**: Manage multiple business branches or firms under a single user account.
- **Print Anywhere**: Ready-to-use print templates for **Thermal POS Printers (58mm/80mm)**, **A4 Standard**, and **A5 Compact**.
- **Complete Accounting**: Double-entry Journal Vouchers, Cash/Bank Receipts, Vendor Payments, Day Book, Cash Book, and Profit & Loss.
- **One-Click Backup & Restore**: Download complete company snapshots in portable JSON format.
- **Zero Heavy Dependencies**: Pure PHP 8 MVC with zero bloated composer vendor bloat, yielding sub-50ms execution times.

---

## 🛠️ System Requirements
- **PHP**: 8.2 or higher
- **Extensions**: `pdo_mysql`, `mbstring`, `openssl`, `json`
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Web Server**: Apache with `mod_rewrite` enabled

---

## 💻 Local Installation (XAMPP / WampServer)

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/SAKSOFTLABS/myslip.in.git
   cd myslip.in
   ```
   *(Or place inside `C:/xampp/htdocs/myslip.in`)*

2. **Database Setup**:
   - Create a database in phpMyAdmin or MySQL terminal named `myslip_db`:
     ```sql
     CREATE DATABASE `myslip_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     ```
   - Import the schema:
     ```bash
     mysql -u root -p myslip_db < database/schema.sql
     ```
   - Seed default roles, permissions, units, and super admin:
     ```bash
     mysql -u root -p myslip_db < database/seeds.sql
     ```

3. **Environment Configuration**:
   - Copy `.env.example` to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Update your local database credentials and app URL if needed.

4. **Launch the Application**:
   - Open your browser and navigate to:
     ```
     http://localhost/myslip.in/public
     ```
   - Default Super Admin Credentials:
     - **Email**: `admin@myslip.in`
     - **Password**: `Admin@123456`

---

## 🌐 Deploying Live via Git

### Step 1: Set Up Remote Git Repository
If not already connected to a remote GitHub / GitLab repo:
```bash
git remote add origin https://github.com/YOUR_USERNAME/myslip.in.git
git branch -M main
git push -u origin main
```

### Step 2: Deploy to Production Server

#### Option A: Direct VPS Deployment (Ubuntu/Debian, Apache/Nginx)
1. Clone your repository into your web root (e.g., `/var/www/myslip.in`):
   ```bash
   git clone https://github.com/YOUR_USERNAME/myslip.in.git /var/www/myslip.in
   cd /var/www/myslip.in
   ```
2. Create `.env` file with live production credentials:
   ```bash
   cp .env.example .env
   nano .env
   ```
   Set:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://myslip.in
   APP_BASE_PATH=
   SESSION_SECURE=true
   DB_HOST=127.0.0.1
   DB_DATABASE=your_live_db
   DB_USERNAME=your_live_user
   DB_PASSWORD=your_secure_password
   ```
3. Set permissions for Apache:
   ```bash
   sudo chown -R www-data:www-data storage/
   sudo chmod -R 775 storage/
   ```
4. Point Apache VirtualHost `DocumentRoot` to `/var/www/myslip.in/public`.
5. Import `database/schema.sql` and `database/seeds.sql` into your live MySQL database.

#### Option B: cPanel / Shared Hosting with Git Version Control
1. In cPanel, go to **Git™ Version Control**.
2. Click **Create**, paste your repository clone URL, and set repository path (e.g., `myslip.in`).
3. Set up a sub-domain or main domain pointing its document root to `myslip.in/public`.
4. In cPanel File Manager, create `.env` under `myslip.in/` with your cPanel MySQL database details.
5. Import `schema.sql` and `seeds.sql` via phpMyAdmin.

#### Continuous Updates:
Whenever you push code changes to GitHub:
```bash
git add .
git commit -m "Your update message"
git push origin main
```
On your live server, pull the latest code:
```bash
git pull origin main
```

---

## 📂 Project Architecture Overview
For detailed database relations, controller workflows, and permission maps, refer to [`PROJECT_OVERVIEW.md`](PROJECT_OVERVIEW.md).

---

## 🔒 Security & Best Practices
- Never commit `.env` or sensitive `.json` backup files to version control (protected by `.gitignore`).
- Keep `APP_DEBUG=false` in production.
- Always enforce SSL/HTTPS on live deployments.
