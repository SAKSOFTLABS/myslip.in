# MYSLIP.IN — Complete Technical & Architectural Documentation

## 1. Project Overview & Mission
**MYSLIP.IN** is a full-featured, lightweight, multi-tenant Cloud Billing, Inventory Management, and Double-Entry Accounting web application designed for Indian small and medium enterprises (SMEs), retailers, wholesalers, and service businesses.

- **Tagline**: Simple Billing & Accounting for Your Business
- **Default Currency**: INR (₹) with Indian Lakhs/Crores numbering format (e.g. `1,23,456.78`) and Words conversion
- **Tax System**: Full Indian GST compliance (CGST, SGST, IGST, HSN/SAC codes, reverse charge, GSTR-1, GSTR-3B)
- **Timezone**: `Asia/Kolkata`
- **Default Locale**: `en_IN`

---

## 2. Technology Stack & Design Principles
- **Backend Architecture**: Custom PHP 8.x MVC (Model-View-Controller) engine built without heavy framework overhead.
  - Zero bloated external dependencies (runs out-of-the-box on standard PHP 8.2+ with PDO MySQL).
  - Fast request lifecycle: `< 50ms` execution overhead.
- **Database**: MySQL 5.7+ / MariaDB 10.3+ with InnoDB engine, `utf8mb4_unicode_ci` collation, foreign keys, and indexes.
- **Frontend**: Responsive UI built with Bootstrap 5, customized CSS stylesheets (`app.css`, `landing.css`, `print.css`), and Vanilla JavaScript (`app.js`, `billing.js`).
- **Server Environment**: Apache HTTP Server with `mod_rewrite` enabled for clean RESTful-style URL routing.

---

## 3. Directory Structure
```
myslip.in/
├── .htaccess                     # Root rewrite rule routing traffic to public/
├── .env.example                  # Environment configuration template
├── .gitignore                    # Version control ignore rules
├── README.md                     # Repository presentation and quickstart
├── PROJECT_OVERVIEW.md           # Deep architectural specification (This file)
├── app/
│   ├── Config/
│   │   ├── app.php               # Application name, timezone, base paths, session parameters
│   │   ├── constants.php         # Business status constants, tax slabs, roles
│   │   └── database.php          # Database PDO connection settings (supports .env)
│   ├── Controllers/             # Request handlers for Web and API endpoints
│   │   ├── AccountController.php     # Chart of accounts and ledgers
│   │   ├── AuthController.php        # Authentication (login, register, logout, recovery)
│   │   ├── BackupController.php      # JSON-based company snapshot & restoration
│   │   ├── CompanyController.php     # Multi-company onboarding and switching
│   │   ├── CustomerController.php    # Customer directory, balance & ledger
│   │   ├── DashboardController.php   # Business dashboard metrics and charts
│   │   ├── GstReportController.php   # GSTR-1 and GSTR-3B tax calculations
│   │   ├── InvoicePrintController.php# Thermal, A4, and A5 printable bill views
│   │   ├── ItemController.php        # Inventory items, barcodes, units, and groups
│   │   ├── LandingController.php     # Public landing homepage
│   │   ├── PurchaseController.php    # Vendor bills and stock addition
│   │   ├── ReportController.php      # P&L, Trial Balance, Cash Book, Item Profit
│   │   ├── ReturnController.php      # Sales credit notes & purchase debit notes
│   │   ├── SalesController.php       # Point-of-Sale (POS) & GST sales invoicing
│   │   ├── SettingsController.php    # Company profile, invoice templates, user roles
│   │   ├── SubscriptionController.php# Plan selection & payment upgrades
│   │   ├── SuperAdminController.php  # Global SaaS administration portal
│   │   ├── VendorController.php      # Supplier directory & vendor ledger
│   │   └── VoucherController.php     # Receipts, Payments, and Journal entries
│   ├── Core/                     # Framework primitives
│   │   ├── App.php                   # Bootstrapper, autoloading, env loader
│   │   ├── Controller.php            # Base controller (views, responses, flash messages)
│   │   ├── Database.php              # PDO singleton wrapper with transactions
│   │   ├── Model.php                 # Query builder, active record helper, pagination
│   │   ├── Request.php               # Request wrapper (GET/POST/JSON/Files sanitization)
│   │   ├── Response.php              # HTTP response generator (JSON, Redirects, HTML)
│   │   ├── Router.php                # Fast route dispatcher with middleware pipeline
│   │   ├── Security.php              # XSS escaping, CSRF tokens, sanitization
│   │   ├── Session.php               # Secure session handling, auth check, company state
│   │   └── View.php                  # Template rendering engine with layouts & partials
│   ├── Helpers/
│   │   └── functions.php             # Global helpers (currency formatting, number-to-words, URL generators)
│   ├── Middleware/
│   │   ├── AuthMiddleware.php        # Validates user login state
│   │   ├── CsrfMiddleware.php        # Validates CSRF token on POST requests
│   │   ├── SubscriptionMiddleware.php# Enforces active subscription/trial access
│   │   ├── SuperAdminMiddleware.php  # Restricts /admin routes to Super Admins
│   │   └── TenantMiddleware.php      # Ensures an active company is selected
│   ├── Models/                   # Domain entities and business logic
│   └── Views/                    # HTML/PHP templates organized by module
├── database/
│   ├── schema.sql                # Complete relational schema (tables, foreign keys, indexes)
│   └── seeds.sql                 # Roles, granular permissions, units, and subscription plans
├── public/                       # Web Document Root
│   ├── .htaccess                 # URL routing to index.php
│   ├── index.php                 # Front controller entry point
│   └── assets/                   # Static CSS, JS, Fonts, and Images
├── routes/
│   └── web.php                   # Web application route definitions
└── storage/                      # Writable runtime storage
    ├── backups/                  # Company JSON backup files
    ├── cache/                    # Compiled caches
    ├── logs/                     # Application error and debug logs
    └── uploads/                  # Uploaded logos and attachments
```

---

## 4. Key Business Capabilities & Features

### 4.1 Multi-Tenant / Multi-Company Isolation
- A single user account can create or belong to multiple companies.
- Context switching between companies is seamless via `/company/switch/{id}`.
- All transactional and master records contain a `company_id` partition key.

### 4.2 Masters Management
- **Inventory & Items**:
  - Item name, SKU, Barcode, HSN/SAC code, Category/Group.
  - Unit of Measurement (PCS, KGS, BOX, NOS, MTR, etc.).
  - Purchase price, Sales price, Minimum stock alert, Opening stock.
- **Customers & Debtors**:
  - Customer name, Phone, Email, Billing & Shipping Address.
  - State code (determines Intra-state CGST+SGST vs Inter-state IGST).
  - GSTIN, PAN, Opening balance, Credit limit, and live ledger view.
- **Vendors & Creditors**:
  - Supplier profile, GSTIN, Bank details, opening balance, and live ledger view.
- **Chart of Accounts**:
  - Categorized into Assets, Liabilities, Incomes, Expenses, and Capital.
  - Pre-seeded system accounts with support for custom sub-ledgers.

### 4.3 Transactions & Invoicing Workflows
- **Sales Invoices**:
  - Real-time billing UI (`billing.js`) supporting item lookup and barcode scanning.
  - Automatic calculation of item discounts, tax slabs, taxable amount, and GST split.
  - Auto-generated sequential invoice numbers (e.g. `INV-2026-0001`).
  - Invoice status tracking (Paid, Unpaid, Partially Paid, Overdue, Cancelled).
- **Purchase Bills**:
  - Direct supplier bill recording, automatic inventory replenishment, and Input Tax Credit (ITC) tracking.
- **Returns (Debit / Credit Notes)**:
  - Sales Returns (Credit Notes) and Purchase Returns (Debit Notes) with automatic stock and balance adjustment.
- **Financial Vouchers**:
  - **Receipt Vouchers**: Customer payments received via Cash, Bank, Cheque, UPI.
  - **Payment Vouchers**: Payments made to vendors or direct expense recording.
  - **Journal Vouchers**: Double-entry ledger adjustment entries.

### 4.4 Reporting & Compliance
- **GST Compliance**:
  - **GSTR-1**: Formatted summaries for B2B (registered buyers), B2CL (unregistered inter-state > 2.5L), B2CS (small retail), and HSN summary.
  - **GSTR-3B**: Monthly outward supplies, taxable turnover, and net tax liability.
- **Financial Statements**:
  - **Cash Book**: Daily in/out cash flows.
  - **Trial Balance**: Complete debit/credit verification across all ledgers.
  - **Profit & Loss Statement**: Gross profit, net operating profit, and net margin.
  - **Item Profitability**: Gross profit margin per item sold.

### 4.5 Invoice Printing Styles
- Standard A4 Tax Invoice (detailed item tables, bank details, terms, authorized signatory).
- Compact A5 Half-Page Invoice.
- Thermal POS Receipt (2-inch / 3-inch 80mm roll) with clean barcode and itemized totals.

### 4.6 Disaster Recovery & Backups
- Built-in one-click backup mechanism exports complete company data into a JSON snapshot.
- Snapshot can be downloaded or restored at any point.

---

## 5. Security & Protection
- **Password Security**: Strong `PASSWORD_BCRYPT` hashing.
- **CSRF Protection**: Tokens enforced on state-modifying requests (`POST`, `PUT`, `DELETE`).
- **SQL Injection Prevention**: 100% prepared PDO statements with bound parameters.
- **XSS Prevention**: Automatic `htmlspecialchars` output sanitization via `e()` helper.
- **Multi-Tenant Protection**: `TenantMiddleware` ensures requests cannot manipulate data outside the active session company.
