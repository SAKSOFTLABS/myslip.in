-- ==========================================================
-- MYSLIP.IN — Initial Seed Data
-- ==========================================================

-- 1. Predefined System Roles
INSERT INTO `roles` (`id`, `company_id`, `role_name`, `description`, `is_system`) VALUES
(1, NULL, 'Company Admin', 'Full control over company data and team members', 1),
(2, NULL, 'Accountant', 'Full financial accounting, vouchers, ledger, and tax reporting', 1),
(3, NULL, 'Billing Staff', 'Create and view sales invoices, customers, and inventory', 1),
(4, NULL, 'Viewer', 'Read-only access to records and reports', 1)
ON DUPLICATE KEY UPDATE `role_name` = VALUES(`role_name`);

-- 2. Granular Permissions
INSERT INTO `permissions` (`id`, `module`, `action`, `description`) VALUES
-- Items
(1, 'items', 'view', 'View inventory items'),
(2, 'items', 'create', 'Add new items'),
(3, 'items', 'edit', 'Edit item details'),
(4, 'items', 'delete', 'Delete items'),
-- Customers
(5, 'customers', 'view', 'View customer directory'),
(6, 'customers', 'create', 'Add new customers'),
(7, 'customers', 'edit', 'Edit customer details'),
(8, 'customers', 'delete', 'Delete customers'),
-- Vendors
(9, 'vendors', 'view', 'View vendor directory'),
(10, 'vendors', 'create', 'Add new vendors'),
(11, 'vendors', 'edit', 'Edit vendor details'),
(12, 'vendors', 'delete', 'Delete vendors'),
-- Accounts
(13, 'accounts', 'view', 'View chart of accounts and balances'),
(14, 'accounts', 'create', 'Create accounts'),
(15, 'accounts', 'edit', 'Edit accounts'),
(16, 'accounts', 'delete', 'Delete accounts'),
-- Sales
(17, 'sales', 'view', 'View sales invoices'),
(18, 'sales', 'create', 'Create sales invoice'),
(19, 'sales', 'edit', 'Edit sales invoice'),
(20, 'sales', 'cancel', 'Cancel sales invoice'),
-- Purchases
(21, 'purchases', 'view', 'View purchase bills'),
(22, 'purchases', 'create', 'Create purchase bill'),
(23, 'purchases', 'edit', 'Edit purchase bill'),
(24, 'purchases', 'cancel', 'Cancel purchase bill'),
-- Returns
(25, 'returns', 'view', 'View sales & purchase returns'),
(26, 'returns', 'create', 'Create returns'),
(27, 'returns', 'cancel', 'Cancel returns'),
-- Receipts & Payments
(28, 'receipts', 'view', 'View receipt vouchers'),
(29, 'receipts', 'create', 'Record customer receipts'),
(30, 'receipts', 'cancel', 'Cancel receipts'),
(31, 'payments', 'view', 'View payment vouchers'),
(32, 'payments', 'create', 'Record vendor/expense payments'),
(33, 'payments', 'cancel', 'Cancel payments'),
(34, 'journals', 'view', 'View journal vouchers'),
(35, 'journals', 'create', 'Create journal vouchers'),
(36, 'journals', 'cancel', 'Cancel journal vouchers'),
-- Reports
(37, 'reports', 'view', 'View operational and financial reports'),
(38, 'reports', 'export', 'Export reports to Excel/PDF'),
(39, 'gst_reports', 'view', 'View GSTR-1, GSTR-2B, GSTR-3B tax reports'),
-- Settings & Backup
(40, 'settings', 'manage', 'Manage company profile, numbering and print setup'),
(41, 'users', 'manage', 'Manage team members and permissions'),
(42, 'backup', 'create', 'Create and download company backups'),
(43, 'backup', 'restore', 'Restore company data from backup')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- 3. Role-Permission Mappings
-- Company Admin: All permissions (1 through 43)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`;

-- Accountant: Financials, inventory, transactions, reports, GST
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, `id` FROM `permissions` WHERE `id` IN (
    1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15,
    17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27,
    28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39
);

-- Billing Staff: Items, Customers, Sales, Receipts
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, `id` FROM `permissions` WHERE `id` IN (
    1, 2, 3, 5, 6, 7, 17, 18, 19, 25, 26, 28, 29, 37
);

-- Viewer: Read-only
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, `id` FROM `permissions` WHERE `action` = 'view';

-- 4. Standard System Account Groups
INSERT INTO `account_groups` (`id`, `company_id`, `name`, `category`, `is_system`) VALUES
(1, NULL, 'Cash-in-Hand', 'assets', 1),
(2, NULL, 'Bank Accounts', 'assets', 1),
(3, NULL, 'Sundry Debtors (Customers)', 'assets', 1),
(4, NULL, 'Stock-in-Hand', 'assets', 1),
(5, NULL, 'Fixed Assets', 'assets', 1),
(6, NULL, 'Current Assets', 'assets', 1),
(7, NULL, 'Sundry Creditors (Vendors)', 'liabilities', 1),
(8, NULL, 'Duties & Taxes (GST)', 'liabilities', 1),
(9, NULL, 'Loans & Borrowings', 'liabilities', 1),
(10, NULL, 'Current Liabilities', 'liabilities', 1),
(11, NULL, 'Capital Account', 'capital', 1),
(12, NULL, 'Drawings', 'capital', 1),
(13, NULL, 'Sales Accounts', 'income', 1),
(14, NULL, 'Direct Income', 'income', 1),
(15, NULL, 'Indirect Income', 'income', 1),
(16, NULL, 'Purchase Accounts', 'expenses', 1),
(17, NULL, 'Direct Expenses', 'expenses', 1),
(18, NULL, 'Indirect Expenses', 'expenses', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 5. Predefined Measurement Units
INSERT INTO `units` (`id`, `company_id`, `name`, `short_name`, `decimal_places`, `is_system`, `status`) VALUES
(1, NULL, 'Pieces', 'PCS', 0, 1, 'active'),
(2, NULL, 'Numbers', 'NOS', 0, 1, 'active'),
(3, NULL, 'Kilograms', 'KG', 3, 1, 'active'),
(4, NULL, 'Grams', 'GM', 3, 1, 'active'),
(5, NULL, 'Litres', 'LTR', 2, 1, 'active'),
(6, NULL, 'Millilitres', 'ML', 0, 1, 'active'),
(7, NULL, 'Box', 'BOX', 0, 1, 'active'),
(8, NULL, 'Set', 'SET', 0, 1, 'active'),
(9, NULL, 'Metres', 'MTR', 2, 1, 'active'),
(10, NULL, 'Packets', 'PKT', 0, 1, 'active'),
(11, NULL, 'Dozens', 'DOZ', 0, 1, 'active')
ON DUPLICATE KEY UPDATE `short_name` = VALUES(`short_name`);

-- 6. SaaS Subscription Plans
INSERT INTO `plans` (`id`, `name`, `slug`, `price`, `billing_cycle`, `duration_days`, `max_users`, `max_companies`, `max_items`, `max_invoices_month`, `features_json`, `is_popular`, `status`) VALUES
(1, '30-Day Free Trial', 'trial', 0.00, 'trial', 30, 2, 1, 500, 200, '{"gst_billing":true,"inventory":true,"accounting":true,"reports":true,"excel_export":true,"pdf_export":true,"thermal_print":true,"backup":true,"whatsapp_share":true}', 0, 'active'),
(2, 'Starter Business', 'starter', 1999.00, 'yearly', 365, 2, 1, 1000, 500, '{"gst_billing":true,"inventory":true,"accounting":true,"reports":true,"excel_export":true,"pdf_export":true,"thermal_print":true,"backup":true,"whatsapp_share":true}', 0, 'active'),
(3, 'Standard Growth', 'standard', 3999.00, 'yearly', 365, 5, 2, 5000, 2500, '{"gst_billing":true,"inventory":true,"accounting":true,"reports":true,"excel_export":true,"pdf_export":true,"thermal_print":true,"backup":true,"whatsapp_share":true,"priority_support":true}', 1, 'active'),
(4, 'Premium Enterprise', 'premium', 6999.00, 'yearly', 365, 15, 5, 50000, 10000, '{"gst_billing":true,"inventory":true,"accounting":true,"reports":true,"excel_export":true,"pdf_export":true,"thermal_print":true,"backup":true,"whatsapp_share":true,"priority_support":true,"custom_invoice":true}', 0, 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `price` = VALUES(`price`);

-- 7. Super Admin Account
-- Password: "Admin@123456" (hashed with PASSWORD_BCRYPT)
INSERT INTO `users` (`id`, `full_name`, `email`, `mobile`, `password_hash`, `is_super_admin`, `is_email_verified`, `status`) VALUES
(1, 'MYSLIP Super Admin', 'admin@myslip.in', '9876543210', '$2y$10$DA.I1DfKuo1eWqbokS329.JNie9RrJcdfEwngdYLuXjZd.HDJga1W', 1, 1, 'active')
ON DUPLICATE KEY UPDATE `is_super_admin` = 1, `password_hash` = VALUES(`password_hash`);
