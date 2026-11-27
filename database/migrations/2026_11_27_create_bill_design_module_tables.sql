-- =============================================================
-- Migration: Bill Design Module Schema & Seeds
-- Date: 2026-11-27
-- Description: Bill templates gallery, company bill settings,
--              company access entitlements, and WhatsApp settings.
-- =============================================================

-- 1. Bill Templates Table
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

-- 2. Company Bill Settings Table (Company active design)
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

-- 3. Company Bill Template Access (Entitlement records for Paid designs)
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

-- 4. Bill Design Settings Table (WhatsApp enquiry config)
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

-- 5. Seed Initial Bill Design Settings
INSERT INTO `bill_design_settings` (`id`, `whatsapp_number`, `default_enquiry_message`, `business_name`, `support_email`)
VALUES (
    1,
    '919876543210',
    'Hello, I am interested in the bill design "{{design_name}}" in your billing software.\n\nCompany: {{company_name}}\n\nPlease provide the pricing and activation details.',
    'MYSLIP.IN Billing SaaS',
    'support@myslip.in'
)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- 6. Seed Predesigned Bill Templates
INSERT INTO `bill_templates` 
(`id`, `name`, `template_key`, `description`, `category`, `paper_size`, `preview_image`, `template_config`, `version`, `pricing_type`, `price`, `currency`, `is_featured`, `display_order`, `status`) 
VALUES
(1, 'Classic GST Invoice', 'classic-gst', 'Official corporate full-page GST Tax Invoice featuring complete party information, place of supply, HSN code breakdown, and dual banking details.', 'GST Invoice', 'A4', '/assets/images/templates/classic-gst.svg', '{"primary_color":"#000000","accent_color":"#111827","font_family":"Inter, sans-serif","show_hsn":true,"show_qr":true}', '1.0.0', 'free', 0.00, 'INR', 1, 1, 'published'),
(2, 'Modern Blue Invoice', 'modern-blue', 'Contemporary clean layout with deep royal blue accents, stylized item rows, rounded summary card, and prominent UPI QR payment section.', 'GST Invoice', 'A4', '/assets/images/templates/modern-blue.svg', '{"primary_color":"#0284c7","accent_color":"#0ea5e9","font_family":"Inter, sans-serif","show_hsn":true,"show_qr":true}', '1.0.0', 'free', 0.00, 'INR', 1, 2, 'published'),
(3, 'Minimalist Mono', 'minimal-clean', 'Understated typography with light borders and airy layout, designed for modern boutiques, design studios, and technology service companies.', 'Non-GST Invoice', 'A4', '/assets/images/templates/minimal-clean.svg', '{"primary_color":"#18181b","accent_color":"#71717a","font_family":"Inter, sans-serif","show_hsn":false,"show_qr":true}', '1.0.0', 'free', 0.00, 'INR', 0, 3, 'published'),
(4, 'Retail Quick Slip (A5)', 'retail-pos', 'Optimized half-page A5 design for retail counters and supermarkets, emphasizing clear item rates, savings/discount badges, and concise receipt format.', 'Retail Invoice', 'A5', '/assets/images/templates/retail-pos.svg', '{"primary_color":"#d97706","accent_color":"#f59e0b","font_family":"Inter, sans-serif","show_hsn":false,"show_qr":true}', '1.0.0', 'free', 0.00, 'INR', 0, 4, 'published'),
(5, 'Thermal POS 80mm (3-Inch)', 'thermal-80mm', 'High-speed standard 3-inch (80mm) thermal roll receipt with store header, itemized quantities, tax summary, and instant UPI payment QR.', 'Thermal Receipt', 'Thermal 80mm', '/assets/images/templates/thermal-80mm.svg', '{"primary_color":"#000000","accent_color":"#333333","font_family":"monospace","show_hsn":false,"show_qr":true}', '1.0.0', 'free', 0.00, 'INR', 1, 5, 'published'),
(6, 'Thermal POS 58mm (2-Inch)', 'thermal-58mm', 'Compact 2-inch (58mm) receipt format crafted for handheld bluetooth mobile printers and field delivery collection slips.', 'POS Receipt', 'Thermal 58mm', '/assets/images/templates/thermal-58mm.svg', '{"primary_color":"#000000","accent_color":"#333333","font_family":"monospace","show_hsn":false,"show_qr":false}', '1.0.0', 'free', 0.00, 'INR', 0, 6, 'published'),
(7, 'Executive Gold Luxury', 'executive-gold', 'High-end corporate design featuring deep onyx and metallic gold framing, watermark branding, structured payment schedule, and luxury border trims.', 'GST Invoice', 'A4', '/assets/images/templates/executive-gold.svg', '{"primary_color":"#b45309","accent_color":"#d97706","font_family":"Inter, sans-serif","show_hsn":true,"show_qr":true}', '1.0.0', 'paid', 499.00, 'INR', 1, 7, 'published'),
(8, 'Wholesale & B2B Heavy Grid', 'wholesale-grid', 'Multi-line industrial invoice with vehicle/transport details, e-way bill number, package/dispatch counts, detailed HSN/tax breakup table, and multi-signatory blocks.', 'Wholesale Invoice', 'A4', '/assets/images/templates/wholesale-grid.svg', '{"primary_color":"#334155","accent_color":"#475569","font_family":"Inter, sans-serif","show_hsn":true,"show_qr":true}', '1.0.0', 'paid', 599.00, 'INR', 1, 8, 'published'),
(9, 'Creative Emerald Fresh', 'creative-emerald', 'Vibrant emerald green commercial layout with prominent left-aligned brand badge, friendly customer notes, and modern item badge styling.', 'GST Invoice', 'A4', '/assets/images/templates/creative-emerald.svg', '{"primary_color":"#059669","accent_color":"#10b981","font_family":"Inter, sans-serif","show_hsn":true,"show_qr":true}', '1.0.0', 'paid', 399.00, 'INR', 0, 9, 'published')
ON DUPLICATE KEY UPDATE 
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `category` = VALUES(`category`),
    `paper_size` = VALUES(`paper_size`),
    `preview_image` = VALUES(`preview_image`),
    `template_config` = VALUES(`template_config`),
    `pricing_type` = VALUES(`pricing_type`),
    `price` = VALUES(`price`),
    `is_featured` = VALUES(`is_featured`),
    `display_order` = VALUES(`display_order`),
    `status` = VALUES(`status`);
