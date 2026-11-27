-- ==========================================================
-- MYSLIP.IN — Master Database Schema
-- Multi-Tenant SaaS Billing, Inventory & Accounting System
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `mobile` VARCHAR(20) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `is_super_admin` TINYINT(1) NOT NULL DEFAULT 0,
    `is_email_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `verification_token` VARCHAR(100) NULL,
    `remember_token` VARCHAR(100) NULL,
    `status` ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active',
    `last_login_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Companies (Tenants)
DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_user_id` INT UNSIGNED NOT NULL,
    `company_name` VARCHAR(150) NOT NULL,
    `business_name` VARCHAR(150) NULL,
    `business_type` ENUM('retail', 'wholesale', 'service', 'trading', 'manufacturing', 'other') NOT NULL DEFAULT 'retail',
    `address_line1` VARCHAR(255) NULL,
    `address_line2` VARCHAR(255) NULL,
    `city` VARCHAR(100) NULL,
    `district` VARCHAR(100) NULL,
    `state` VARCHAR(100) NOT NULL DEFAULT 'Maharashtra',
    `state_code` VARCHAR(5) NOT NULL DEFAULT '27',
    `pin_code` VARCHAR(10) NULL,
    `country` VARCHAR(50) NOT NULL DEFAULT 'India',
    `mobile` VARCHAR(20) NOT NULL,
    `alternate_mobile` VARCHAR(20) NULL,
    `email` VARCHAR(150) NULL,
    `website` VARCHAR(150) NULL,
    `gst_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `gstin` VARCHAR(20) NULL,
    `pan` VARCHAR(15) NULL,
    `cin` VARCHAR(30) NULL,
    `logo_url` VARCHAR(255) NULL,
    `signature_url` VARCHAR(255) NULL,
    `bank_name` VARCHAR(150) NULL,
    `bank_account_no` VARCHAR(50) NULL,
    `bank_ifsc` VARCHAR(20) NULL,
    `bank_branch` VARCHAR(100) NULL,
    `upi_id` VARCHAR(100) NULL,
    `invoice_prefix` VARCHAR(10) NOT NULL DEFAULT 'INV',
    `currency_symbol` VARCHAR(10) NOT NULL DEFAULT '₹',
    `status` ENUM('trial', 'active', 'expired', 'suspended') NOT NULL DEFAULT 'trial',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_owner` (`owner_user_id`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_comp_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Financial Years
DROP TABLE IF EXISTS `financial_years`;
CREATE TABLE `financial_years` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `fy_name` VARCHAR(50) NOT NULL, -- e.g. "2026-2027"
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_current` TINYINT(1) NOT NULL DEFAULT 1,
    `is_closed` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_comp_fy` (`company_id`, `is_current`),
    CONSTRAINT `fk_fy_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Roles & Permissions
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NULL, -- NULL = System predefined role
    `role_name` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) NULL,
    `is_system` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_comp_role` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `module` VARCHAR(50) NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) NULL,
    UNIQUE KEY `uk_mod_act` (`module`, `action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    UNIQUE KEY `uk_role_perm` (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Company Users (Team Members)
DROP TABLE IF EXISTS `company_users`;
CREATE TABLE `company_users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comp_user` (`company_id`, `user_id`),
    CONSTRAINT `fk_cu_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cu_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cu_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Plans & Subscriptions
DROP TABLE IF EXISTS `plans`;
CREATE TABLE `plans` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `billing_cycle` ENUM('monthly', 'yearly', 'lifetime', 'trial') NOT NULL DEFAULT 'yearly',
    `duration_days` INT NOT NULL DEFAULT 365,
    `max_users` INT NOT NULL DEFAULT 1,
    `max_companies` INT NOT NULL DEFAULT 1,
    `max_items` INT NOT NULL DEFAULT 1000,
    `max_invoices_month` INT NOT NULL DEFAULT 500,
    `features_json` TEXT NULL,
    `is_popular` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `plan_id` INT UNSIGNED NOT NULL,
    `status` ENUM('trial', 'active', 'expired', 'suspended') NOT NULL DEFAULT 'trial',
    `start_date` DATE NOT NULL,
    `expiry_date` DATE NOT NULL,
    `trial_start_date` DATE NULL,
    `trial_expiry_date` DATE NULL,
    `payment_method` VARCHAR(50) NULL,
    `gateway_tx_id` VARCHAR(100) NULL,
    `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sub_comp` (`company_id`, `status`),
    CONSTRAINT `fk_sub_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sub_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Item Groups & Units
DROP TABLE IF EXISTS `item_groups`;
CREATE TABLE `item_groups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `parent_id` INT UNSIGNED NULL,
    `description` VARCHAR(255) NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ig_comp` (`company_id`),
    CONSTRAINT `fk_ig_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `units`;
CREATE TABLE `units` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NULL, -- NULL for system units
    `name` VARCHAR(50) NOT NULL,
    `short_name` VARCHAR(20) NOT NULL,
    `decimal_places` TINYINT NOT NULL DEFAULT 0,
    `is_system` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_unit_comp` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Items (Products / Services)
DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `code` VARCHAR(50) NULL,
    `barcode` VARCHAR(100) NULL,
    `name` VARCHAR(200) NOT NULL,
    `group_id` INT UNSIGNED NULL,
    `unit_id` INT UNSIGNED NULL,
    `hsn_sac` VARCHAR(20) NULL,
    `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `purchase_rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `selling_rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `mrp` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `opening_stock` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
    `opening_stock_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `min_stock` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
    `max_stock` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
    `reorder_level` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
    `description` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_item_comp` (`company_id`, `is_deleted`),
    INDEX `idx_item_barcode` (`company_id`, `barcode`),
    INDEX `idx_item_code` (`company_id`, `code`),
    CONSTRAINT `fk_item_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_item_group` FOREIGN KEY (`group_id`) REFERENCES `item_groups` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_item_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Customers & Vendors
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `customer_code` VARCHAR(50) NULL,
    `name` VARCHAR(150) NOT NULL,
    `contact_person` VARCHAR(100) NULL,
    `mobile` VARCHAR(20) NULL,
    `alternate_mobile` VARCHAR(20) NULL,
    `email` VARCHAR(150) NULL,
    `billing_address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `district` VARCHAR(100) NULL,
    `state` VARCHAR(100) NOT NULL DEFAULT 'Maharashtra',
    `state_code` VARCHAR(5) NOT NULL DEFAULT '27',
    `pin_code` VARCHAR(10) NULL,
    `gstin` VARCHAR(20) NULL,
    `pan` VARCHAR(15) NULL,
    `credit_limit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `credit_days` INT NOT NULL DEFAULT 0,
    `opening_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `balance_type` ENUM('DR', 'CR') NOT NULL DEFAULT 'DR',
    `price_level` VARCHAR(50) NULL,
    `notes` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_cust_comp` (`company_id`, `is_deleted`),
    INDEX `idx_cust_mobile` (`company_id`, `mobile`),
    CONSTRAINT `fk_cust_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `vendors`;
CREATE TABLE `vendors` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `vendor_code` VARCHAR(50) NULL,
    `name` VARCHAR(150) NOT NULL,
    `contact_person` VARCHAR(100) NULL,
    `mobile` VARCHAR(20) NULL,
    `alternate_mobile` VARCHAR(20) NULL,
    `email` VARCHAR(150) NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `district` VARCHAR(100) NULL,
    `state` VARCHAR(100) NOT NULL DEFAULT 'Maharashtra',
    `state_code` VARCHAR(5) NOT NULL DEFAULT '27',
    `pin_code` VARCHAR(10) NULL,
    `gstin` VARCHAR(20) NULL,
    `pan` VARCHAR(15) NULL,
    `credit_limit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `credit_days` INT NOT NULL DEFAULT 0,
    `opening_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `balance_type` ENUM('DR', 'CR') NOT NULL DEFAULT 'CR',
    `notes` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_vend_comp` (`company_id`, `is_deleted`),
    INDEX `idx_vend_mobile` (`company_id`, `mobile`),
    CONSTRAINT `fk_vend_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Chart of Accounts
DROP TABLE IF EXISTS `account_groups`;
CREATE TABLE `account_groups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NULL, -- NULL = standard system group
    `name` VARCHAR(100) NOT NULL,
    `category` ENUM('assets', 'liabilities', 'income', 'expenses', 'capital') NOT NULL,
    `is_system` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ag_comp` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `group_id` INT UNSIGNED NOT NULL,
    `account_name` VARCHAR(150) NOT NULL,
    `opening_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `balance_type` ENUM('DR', 'CR') NOT NULL DEFAULT 'DR',
    `is_bank` TINYINT(1) NOT NULL DEFAULT 0,
    `is_cash` TINYINT(1) NOT NULL DEFAULT 0,
    `bank_account_no` VARCHAR(50) NULL,
    `bank_ifsc` VARCHAR(20) NULL,
    `is_system` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_acc_comp` (`company_id`, `is_deleted`),
    CONSTRAINT `fk_acc_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_acc_group` FOREIGN KEY (`group_id`) REFERENCES `account_groups` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Sales & Sales Items
DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `financial_year_id` INT UNSIGNED NOT NULL,
    `invoice_number` VARCHAR(50) NOT NULL,
    `invoice_date` DATE NOT NULL,
    `due_date` DATE NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `customer_name` VARCHAR(150) NOT NULL,
    `customer_mobile` VARCHAR(20) NULL,
    `customer_gstin` VARCHAR(20) NULL,
    `customer_state` VARCHAR(100) NOT NULL DEFAULT 'Maharashtra',
    `customer_state_code` VARCHAR(5) NOT NULL DEFAULT '27',
    `place_of_supply` VARCHAR(100) NOT NULL DEFAULT 'Maharashtra',
    `is_interstate` TINYINT(1) NOT NULL DEFAULT 0,
    `payment_mode` ENUM('cash', 'bank', 'credit', 'upi', 'cheque', 'other') NOT NULL DEFAULT 'credit',
    `sales_type` ENUM('tax_invoice', 'bill_of_supply', 'retail_invoice', 'proforma') NOT NULL DEFAULT 'tax_invoice',
    `reference_no` VARCHAR(100) NULL,
    `sales_person` VARCHAR(100) NULL,
    `gross_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `item_discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `bill_discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `bill_discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `taxable_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `cgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `sgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `igst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `round_off` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `grand_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `balance_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `payment_status` ENUM('paid', 'partial', 'unpaid') NOT NULL DEFAULT 'unpaid',
    `notes` TEXT NULL,
    `terms` TEXT NULL,
    `status` ENUM('active', 'cancelled') NOT NULL DEFAULT 'active',
    `cancelled_reason` VARCHAR(255) NULL,
    `cancelled_by` INT UNSIGNED NULL,
    `cancelled_at` DATETIME NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comp_inv` (`company_id`, `financial_year_id`, `invoice_number`),
    INDEX `idx_sale_comp_date` (`company_id`, `invoice_date`),
    INDEX `idx_sale_cust` (`company_id`, `customer_id`),
    CONSTRAINT `fk_sale_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sale_fy` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_sale_cust` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sales_items`;
CREATE TABLE `sales_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `sale_id` INT UNSIGNED NOT NULL,
    `item_id` INT UNSIGNED NOT NULL,
    `item_name` VARCHAR(200) NOT NULL,
    `barcode` VARCHAR(100) NULL,
    `hsn_sac` VARCHAR(20) NULL,
    `quantity` DECIMAL(15,3) NOT NULL DEFAULT 1.000,
    `unit_name` VARCHAR(20) NOT NULL DEFAULT 'PCS',
    `rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `taxable_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `cgst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `cgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `sgst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `sgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `igst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `igst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `cost_rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_si_sale` (`sale_id`),
    INDEX `idx_si_item` (`company_id`, `item_id`),
    CONSTRAINT `fk_si_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_si_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Purchases & Purchase Items
DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `financial_year_id` INT UNSIGNED NOT NULL,
    `purchase_number` VARCHAR(50) NOT NULL,
    `supplier_invoice_number` VARCHAR(100) NULL,
    `purchase_date` DATE NOT NULL,
    `due_date` DATE NULL,
    `vendor_id` INT UNSIGNED NOT NULL,
    `vendor_name` VARCHAR(150) NOT NULL,
    `vendor_gstin` VARCHAR(20) NULL,
    `vendor_state` VARCHAR(100) NOT NULL DEFAULT 'Maharashtra',
    `vendor_state_code` VARCHAR(5) NOT NULL DEFAULT '27',
    `is_interstate` TINYINT(1) NOT NULL DEFAULT 0,
    `payment_mode` ENUM('cash', 'bank', 'credit', 'upi', 'cheque', 'other') NOT NULL DEFAULT 'credit',
    `reference_no` VARCHAR(100) NULL,
    `gross_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `taxable_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `cgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `sgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `igst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `round_off` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `grand_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `balance_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `payment_status` ENUM('paid', 'partial', 'unpaid') NOT NULL DEFAULT 'unpaid',
    `notes` TEXT NULL,
    `status` ENUM('active', 'cancelled') NOT NULL DEFAULT 'active',
    `cancelled_reason` VARCHAR(255) NULL,
    `cancelled_by` INT UNSIGNED NULL,
    `cancelled_at` DATETIME NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comp_pur` (`company_id`, `financial_year_id`, `purchase_number`),
    INDEX `idx_pur_comp_date` (`company_id`, `purchase_date`),
    INDEX `idx_pur_vend` (`company_id`, `vendor_id`),
    CONSTRAINT `fk_pur_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pur_fy` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_pur_vend` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `purchase_items`;
CREATE TABLE `purchase_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `purchase_id` INT UNSIGNED NOT NULL,
    `item_id` INT UNSIGNED NOT NULL,
    `item_name` VARCHAR(200) NOT NULL,
    `hsn_sac` VARCHAR(20) NULL,
    `quantity` DECIMAL(15,3) NOT NULL DEFAULT 1.000,
    `unit_name` VARCHAR(20) NOT NULL DEFAULT 'PCS',
    `rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `taxable_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `cgst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `cgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `sgst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `sgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `igst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `igst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pi_pur` (`purchase_id`),
    INDEX `idx_pi_item` (`company_id`, `item_id`),
    CONSTRAINT `fk_pi_pur` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pi_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Sales Returns & Purchase Returns
DROP TABLE IF EXISTS `sales_returns`;
CREATE TABLE `sales_returns` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `financial_year_id` INT UNSIGNED NOT NULL,
    `return_number` VARCHAR(50) NOT NULL,
    `return_date` DATE NOT NULL,
    `original_sale_id` INT UNSIGNED NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `customer_name` VARCHAR(150) NOT NULL,
    `reason` VARCHAR(255) NULL,
    `gross_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `taxable_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `cgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `sgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `igst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `round_off` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `grand_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `refunded_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('active', 'cancelled') NOT NULL DEFAULT 'active',
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comp_sr` (`company_id`, `financial_year_id`, `return_number`),
    INDEX `idx_sr_comp_date` (`company_id`, `return_date`),
    CONSTRAINT `fk_sr_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sr_fy` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_sr_cust` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_sr_orig` FOREIGN KEY (`original_sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sales_return_items`;
CREATE TABLE `sales_return_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `sales_return_id` INT UNSIGNED NOT NULL,
    `item_id` INT UNSIGNED NOT NULL,
    `item_name` VARCHAR(200) NOT NULL,
    `hsn_sac` VARCHAR(20) NULL,
    `quantity` DECIMAL(15,3) NOT NULL DEFAULT 1.000,
    `unit_name` VARCHAR(20) NOT NULL DEFAULT 'PCS',
    `rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `taxable_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `cgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `sgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `igst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sri_sr` (`sales_return_id`),
    CONSTRAINT `fk_sri_sr` FOREIGN KEY (`sales_return_id`) REFERENCES `sales_returns` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sri_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `purchase_returns`;
CREATE TABLE `purchase_returns` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `financial_year_id` INT UNSIGNED NOT NULL,
    `return_number` VARCHAR(50) NOT NULL,
    `return_date` DATE NOT NULL,
    `original_purchase_id` INT UNSIGNED NULL,
    `vendor_id` INT UNSIGNED NOT NULL,
    `vendor_name` VARCHAR(150) NOT NULL,
    `reason` VARCHAR(255) NULL,
    `gross_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `taxable_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `cgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `sgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `igst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `round_off` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `grand_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `received_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('active', 'cancelled') NOT NULL DEFAULT 'active',
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comp_pr` (`company_id`, `financial_year_id`, `return_number`),
    INDEX `idx_pr_comp_date` (`company_id`, `return_date`),
    CONSTRAINT `fk_pr_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pr_fy` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_pr_vend` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_pr_orig` FOREIGN KEY (`original_purchase_id`) REFERENCES `purchases` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `purchase_return_items`;
CREATE TABLE `purchase_return_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `purchase_return_id` INT UNSIGNED NOT NULL,
    `item_id` INT UNSIGNED NOT NULL,
    `item_name` VARCHAR(200) NOT NULL,
    `hsn_sac` VARCHAR(20) NULL,
    `quantity` DECIMAL(15,3) NOT NULL DEFAULT 1.000,
    `unit_name` VARCHAR(20) NOT NULL DEFAULT 'PCS',
    `rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `taxable_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `cgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `sgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `igst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pri_pr` (`purchase_return_id`),
    CONSTRAINT `fk_pri_pr` FOREIGN KEY (`purchase_return_id`) REFERENCES `purchase_returns` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pri_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Financial Vouchers (Receipts, Payments, Journals)
DROP TABLE IF EXISTS `receipts`;
CREATE TABLE `receipts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `financial_year_id` INT UNSIGNED NOT NULL,
    `receipt_number` VARCHAR(50) NOT NULL,
    `receipt_date` DATE NOT NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `deposit_account_id` INT UNSIGNED NOT NULL, -- Cash / Bank account
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `payment_mode` ENUM('cash', 'bank', 'upi', 'cheque', 'neft_rtgs', 'other') NOT NULL DEFAULT 'cash',
    `cheque_no` VARCHAR(50) NULL,
    `utr_no` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `status` ENUM('active', 'cancelled') NOT NULL DEFAULT 'active',
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comp_rec` (`company_id`, `financial_year_id`, `receipt_number`),
    INDEX `idx_rec_comp_date` (`company_id`, `receipt_date`),
    CONSTRAINT `fk_rec_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rec_fy` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_rec_cust` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_rec_dep` FOREIGN KEY (`deposit_account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `receipt_allocations`;
CREATE TABLE `receipt_allocations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `receipt_id` INT UNSIGNED NOT NULL,
    `sale_id` INT UNSIGNED NOT NULL,
    `allocated_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ra_rec` (`receipt_id`),
    INDEX `idx_ra_sale` (`sale_id`),
    CONSTRAINT `fk_ra_rec` FOREIGN KEY (`receipt_id`) REFERENCES `receipts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ra_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `financial_year_id` INT UNSIGNED NOT NULL,
    `payment_number` VARCHAR(50) NOT NULL,
    `payment_date` DATE NOT NULL,
    `vendor_id` INT UNSIGNED NULL,
    `expense_account_id` INT UNSIGNED NULL,
    `paid_from_account_id` INT UNSIGNED NOT NULL, -- Cash / Bank account
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `payment_mode` ENUM('cash', 'bank', 'upi', 'cheque', 'neft_rtgs', 'other') NOT NULL DEFAULT 'cash',
    `cheque_no` VARCHAR(50) NULL,
    `utr_no` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `status` ENUM('active', 'cancelled') NOT NULL DEFAULT 'active',
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comp_pay` (`company_id`, `financial_year_id`, `payment_number`),
    INDEX `idx_pay_comp_date` (`company_id`, `payment_date`),
    CONSTRAINT `fk_pay_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pay_fy` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_pay_from` FOREIGN KEY (`paid_from_account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payment_allocations`;
CREATE TABLE `payment_allocations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `payment_id` INT UNSIGNED NOT NULL,
    `purchase_id` INT UNSIGNED NOT NULL,
    `allocated_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pa_pay` (`payment_id`),
    INDEX `idx_pa_pur` (`purchase_id`),
    CONSTRAINT `fk_pa_pay` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pa_pur` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `journals`;
CREATE TABLE `journals` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `financial_year_id` INT UNSIGNED NOT NULL,
    `journal_number` VARCHAR(50) NOT NULL,
    `journal_date` DATE NOT NULL,
    `narration` TEXT NOT NULL,
    `total_debit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_credit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('active', 'cancelled') NOT NULL DEFAULT 'active',
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comp_jrn` (`company_id`, `financial_year_id`, `journal_number`),
    INDEX `idx_jrn_comp_date` (`company_id`, `journal_date`),
    CONSTRAINT `fk_jrn_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_jrn_fy` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `journal_entries`;
CREATE TABLE `journal_entries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `journal_id` INT UNSIGNED NOT NULL,
    `account_id` INT UNSIGNED NOT NULL,
    `debit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `credit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `narration` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_je_jrn` (`journal_id`),
    CONSTRAINT `fk_je_jrn` FOREIGN KEY (`journal_id`) REFERENCES `journals` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_je_acc` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Double-Entry General Ledger (All Posted Financial Transactions)
DROP TABLE IF EXISTS `ledger_entries`;
CREATE TABLE `ledger_entries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `financial_year_id` INT UNSIGNED NOT NULL,
    `voucher_type` ENUM('opening', 'sale', 'purchase', 'sales_return', 'purchase_return', 'receipt', 'payment', 'journal') NOT NULL,
    `voucher_id` INT UNSIGNED NOT NULL,
    `voucher_number` VARCHAR(50) NOT NULL,
    `voucher_date` DATE NOT NULL,
    `account_id` INT UNSIGNED NOT NULL,
    `debit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `credit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `narration` VARCHAR(255) NULL,
    `is_cancelled` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_le_comp_acc` (`company_id`, `account_id`, `voucher_date`),
    INDEX `idx_le_comp_fy` (`company_id`, `financial_year_id`, `voucher_date`),
    INDEX `idx_le_vouch` (`company_id`, `voucher_type`, `voucher_id`),
    CONSTRAINT `fk_le_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_le_acc` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Stock Movement Ledger (Immutable Inventory Audit Trail)
DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE `stock_movements` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `financial_year_id` INT UNSIGNED NOT NULL,
    `item_id` INT UNSIGNED NOT NULL,
    `transaction_type` ENUM('opening', 'sale', 'purchase', 'sales_return', 'purchase_return', 'adjustment') NOT NULL,
    `transaction_id` INT UNSIGNED NOT NULL,
    `transaction_item_id` INT UNSIGNED NULL,
    `transaction_date` DATE NOT NULL,
    `qty_in` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
    `qty_out` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
    `rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `remarks` VARCHAR(255) NULL,
    `is_cancelled` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sm_comp_item` (`company_id`, `item_id`, `transaction_date`),
    INDEX `idx_sm_comp_fy` (`company_id`, `financial_year_id`),
    INDEX `idx_sm_tx` (`company_id`, `transaction_type`, `transaction_id`),
    CONSTRAINT `fk_sm_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sm_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Numbering Series Sequences
DROP TABLE IF EXISTS `numbering_series`;
CREATE TABLE `numbering_series` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `financial_year_id` INT UNSIGNED NOT NULL,
    `voucher_type` ENUM('sale', 'purchase', 'sales_return', 'purchase_return', 'receipt', 'payment', 'journal') NOT NULL,
    `prefix` VARCHAR(20) NOT NULL,
    `suffix` VARCHAR(20) NULL,
    `next_number` INT UNSIGNED NOT NULL DEFAULT 1,
    `zero_pad` TINYINT NOT NULL DEFAULT 5,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comp_fy_vouch` (`company_id`, `financial_year_id`, `voucher_type`),
    CONSTRAINT `fk_ns_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Invoice & Company Customization Settings
DROP TABLE IF EXISTS `invoice_settings`;
CREATE TABLE `invoice_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL UNIQUE,
    `default_format` ENUM('a4', 'a5', 'thermal') NOT NULL DEFAULT 'a4',
    `show_logo` TINYINT(1) NOT NULL DEFAULT 1,
    `show_bank_details` TINYINT(1) NOT NULL DEFAULT 1,
    `show_upi_qr` TINYINT(1) NOT NULL DEFAULT 1,
    `show_terms` TINYINT(1) NOT NULL DEFAULT 1,
    `terms_conditions` TEXT NULL,
    `declaration` TEXT NULL,
    `signature_label` VARCHAR(100) NOT NULL DEFAULT 'Authorized Signatory',
    `print_copies` TINYINT NOT NULL DEFAULT 1,
    `primary_color` VARCHAR(20) NOT NULL DEFAULT '#1e3a8a',
    `thermal_footer_note` VARCHAR(255) NOT NULL DEFAULT 'Thank you for your business!',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_is_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `company_settings`;
CREATE TABLE `company_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comp_key` (`company_id`, `setting_key`),
    CONSTRAINT `fk_cs_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Audit Logs & Password Resets
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NULL,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(100) NOT NULL,
    `record_id` INT UNSIGNED NULL,
    `ip_address` VARCHAR(50) NULL,
    `user_agent` VARCHAR(255) NULL,
    `details_json` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_al_comp_date` (`company_id`, `created_at`),
    INDEX `idx_al_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(150) NOT NULL,
    `token` VARCHAR(100) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pr_token` (`token`),
    INDEX `idx_pr_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Backups Table
DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `version` VARCHAR(20) NOT NULL DEFAULT '1.0',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_bk_comp` (`company_id`),
    CONSTRAINT `fk_bk_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Gateway Transactions
DROP TABLE IF EXISTS `payment_gateway_transactions`;
CREATE TABLE `payment_gateway_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `plan_id` INT UNSIGNED NOT NULL,
    `gateway` ENUM('razorpay', 'stripe', 'manual') NOT NULL DEFAULT 'razorpay',
    `order_id` VARCHAR(100) NULL,
    `payment_id` VARCHAR(100) NULL,
    `signature` VARCHAR(255) NULL,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
    `status` ENUM('pending', 'success', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    `payload_json` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pg_comp` (`company_id`),
    INDEX `idx_pg_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Bill Templates (Predesigned Bill Design Gallery)
CREATE TABLE IF NOT EXISTS `bill_templates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `template_key` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'GST Invoice',
    `paper_size` VARCHAR(50) NOT NULL DEFAULT 'A4',
    `preview_image` VARCHAR(255) NULL,
    `template_config` LONGTEXT NULL,
    `version` VARCHAR(20) NOT NULL DEFAULT '1.0.0',
    `pricing_type` ENUM('free', 'paid') NOT NULL DEFAULT 'free',
    `price` DECIMAL(10,2) NULL DEFAULT 0.00,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `display_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'published',
    `created_by` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_bt_status` (`status`),
    INDEX `idx_bt_category` (`category`),
    INDEX `idx_bt_pricing` (`pricing_type`),
    INDEX `idx_bt_paper` (`paper_size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Company Bill Settings (Company Active Design)
CREATE TABLE IF NOT EXISTS `company_bill_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `active_template_id` INT NOT NULL,
    `active_template_version` VARCHAR(20) NOT NULL DEFAULT '1.0.0',
    `document_type` VARCHAR(50) NOT NULL DEFAULT 'sales_invoice',
    `branch_id` INT NULL,
    `updated_by` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comp_doc` (`company_id`, `document_type`),
    INDEX `idx_cbs_company` (`company_id`),
    INDEX `idx_cbs_template` (`active_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Company Bill Template Access (Admin-Controlled Entitlements for Paid Designs)
CREATE TABLE IF NOT EXISTS `company_bill_template_access` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `template_id` INT NOT NULL,
    `access_status` ENUM('active', 'revoked') NOT NULL DEFAULT 'active',
    `granted_by` INT NULL,
    `granted_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `revoked_date` DATETIME NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comp_tpl` (`company_id`, `template_id`),
    INDEX `idx_cbta_comp_status` (`company_id`, `access_status`),
    INDEX `idx_cbta_template` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. Bill Design Settings (Super Admin WhatsApp Enquiry Configuration)
CREATE TABLE IF NOT EXISTS `bill_design_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `whatsapp_number` VARCHAR(30) NOT NULL DEFAULT '919876543210',
    `default_enquiry_message` TEXT NULL,
    `business_name` VARCHAR(150) NULL DEFAULT 'MYSLIP.IN',
    `support_email` VARCHAR(150) NULL DEFAULT 'support@myslip.in',
    `updated_by` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
