<?php
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\TenantMiddleware;
use App\Middleware\SuperAdminMiddleware;
use App\Middleware\SubscriptionMiddleware;

// -------------------------------------------------------------
// 1. PUBLIC & GUEST ROUTES
// -------------------------------------------------------------
Router::get('/', 'LandingController@index');
Router::get('/login', 'AuthController@showLogin');
Router::post('/login', 'AuthController@login');
Router::get('/register', 'AuthController@showRegister');
Router::post('/register', 'AuthController@register');
Router::get('/logout', 'AuthController@logout');
Router::get('/forgot-password', 'AuthController@showForgotPassword');
Router::post('/forgot-password', 'AuthController@forgotPassword');
Router::get('/invoices/view/{id}', 'InvoicePrintController@publicView');
Router::get('/download', 'LandingController@downloadApp');
Router::get('/download/app', 'LandingController@downloadApp');
Router::get('/download/exe', 'LandingController@downloadApp');

// -------------------------------------------------------------
// 2. TENANT ONBOARDING & COMPANY SWITCHING (Requires Login)
// -------------------------------------------------------------
Router::get('/company/create', 'CompanyController@create', [AuthMiddleware::class]);
Router::post('/company/store', 'CompanyController@store', [AuthMiddleware::class]);
Router::get('/company/switch', 'CompanyController@switchCompany', [AuthMiddleware::class]);
Router::get('/company/switch/{id}', 'CompanyController@selectCompany', [AuthMiddleware::class]);

// -------------------------------------------------------------
// 3. CORE SAAS ROUTES (Requires Auth + Tenant + Subscription)
// -------------------------------------------------------------
$saasMiddlewares = [AuthMiddleware::class, TenantMiddleware::class, SubscriptionMiddleware::class];

// Dashboard
Router::get('/dashboard', 'DashboardController@index', $saasMiddlewares);

// Items Master & Taxonomy
Router::get('/items', 'ItemController@index', $saasMiddlewares);
Router::get('/items/create', 'ItemController@create', $saasMiddlewares);
Router::post('/items/store', 'ItemController@store', $saasMiddlewares);
Router::post('/items/quick-store', 'ItemController@quickStore', $saasMiddlewares);
Router::get('/items/edit/{id}', 'ItemController@edit', $saasMiddlewares);
Router::post('/items/update/{id}', 'ItemController@update', $saasMiddlewares);
Router::get('/items/delete/{id}', 'ItemController@delete', $saasMiddlewares);
Router::get('/items/search', 'ItemController@search', $saasMiddlewares);
Router::get('/items/barcode/{code}', 'ItemController@findByBarcode', $saasMiddlewares);
Router::get('/units', 'ItemController@units', $saasMiddlewares);
Router::post('/units/store', 'ItemController@storeUnit', $saasMiddlewares);
Router::post('/groups/store', 'ItemController@storeGroup', $saasMiddlewares);

// Customer Master
Router::get('/customers', 'CustomerController@index', $saasMiddlewares);
Router::get('/customers/create', 'CustomerController@create', $saasMiddlewares);
Router::post('/customers/store', 'CustomerController@store', $saasMiddlewares);
Router::post('/customers/quick-store', 'CustomerController@quickStore', $saasMiddlewares);
Router::get('/customers/edit/{id}', 'CustomerController@edit', $saasMiddlewares);
Router::post('/customers/update/{id}', 'CustomerController@update', $saasMiddlewares);
Router::get('/customers/delete/{id}', 'CustomerController@delete', $saasMiddlewares);
Router::get('/customers/ledger/{id}', 'CustomerController@ledger', $saasMiddlewares);

// Vendor Master
Router::get('/vendors', 'VendorController@index', $saasMiddlewares);
Router::get('/vendors/create', 'VendorController@create', $saasMiddlewares);
Router::post('/vendors/store', 'VendorController@store', $saasMiddlewares);
Router::post('/vendors/quick-store', 'VendorController@quickStore', $saasMiddlewares);
Router::get('/vendors/edit/{id}', 'VendorController@edit', $saasMiddlewares);
Router::post('/vendors/update/{id}', 'VendorController@update', $saasMiddlewares);
Router::get('/vendors/delete/{id}', 'VendorController@delete', $saasMiddlewares);
Router::get('/vendors/ledger/{id}', 'VendorController@ledger', $saasMiddlewares);

// Chart of Accounts Master
Router::get('/accounts', 'AccountController@index', $saasMiddlewares);
Router::get('/accounts/create', 'AccountController@create', $saasMiddlewares);
Router::post('/accounts/store', 'AccountController@store', $saasMiddlewares);
Router::get('/accounts/edit/{id}', 'AccountController@edit', $saasMiddlewares);
Router::post('/accounts/update/{id}', 'AccountController@update', $saasMiddlewares);
Router::get('/accounts/delete/{id}', 'AccountController@delete', $saasMiddlewares);
Router::get('/accounts/ledger/{id}', 'AccountController@ledger', $saasMiddlewares);

// Sales Invoices
Router::get('/sales', 'SalesController@index', $saasMiddlewares);
Router::get('/sales/create', 'SalesController@create', $saasMiddlewares);
Router::post('/sales/store', 'SalesController@store', $saasMiddlewares);
Router::get('/sales/view/{id}', 'SalesController@viewInvoice', $saasMiddlewares);
Router::get('/sales/edit/{id}', 'SalesController@edit', $saasMiddlewares);
Router::post('/sales/update/{id}', 'SalesController@update', $saasMiddlewares);
Router::get('/sales/delete/{id}', 'SalesController@delete', $saasMiddlewares);
Router::post('/sales/delete/{id}', 'SalesController@delete', $saasMiddlewares);
Router::post('/sales/cancel/{id}', 'SalesController@cancel', $saasMiddlewares);

// Purchase Bills
Router::get('/purchases', 'PurchaseController@index', $saasMiddlewares);
Router::get('/purchases/create', 'PurchaseController@create', $saasMiddlewares);
Router::post('/purchases/store', 'PurchaseController@store', $saasMiddlewares);
Router::get('/purchases/view/{id}', 'PurchaseController@viewBill', $saasMiddlewares);
Router::get('/purchases/edit/{id}', 'PurchaseController@edit', $saasMiddlewares);
Router::post('/purchases/update/{id}', 'PurchaseController@update', $saasMiddlewares);
Router::get('/purchases/delete/{id}', 'PurchaseController@delete', $saasMiddlewares);
Router::post('/purchases/delete/{id}', 'PurchaseController@delete', $saasMiddlewares);
Router::post('/purchases/cancel/{id}', 'PurchaseController@cancel', $saasMiddlewares);

// Returns (Credit / Debit Notes)
Router::get('/returns', 'ReturnController@index', $saasMiddlewares);
Router::get('/returns/sales/create', 'ReturnController@createSalesReturn', $saasMiddlewares);
Router::post('/returns/sales/store', 'ReturnController@storeSalesReturn', $saasMiddlewares);

// Financial Vouchers (Receipts, Payments, Journals)
Router::get('/vouchers/receipts', 'VoucherController@receiptsIndex', $saasMiddlewares);
Router::get('/vouchers/receipts/create', 'VoucherController@createReceipt', $saasMiddlewares);
Router::post('/vouchers/receipts/store', 'VoucherController@storeReceipt', $saasMiddlewares);
Router::get('/vouchers/payments', 'VoucherController@paymentsIndex', $saasMiddlewares);
Router::get('/vouchers/payments/create', 'VoucherController@createPayment', $saasMiddlewares);
Router::post('/vouchers/payments/store', 'VoucherController@storePayment', $saasMiddlewares);
Router::get('/vouchers/journals', 'VoucherController@journalsIndex', $saasMiddlewares);
Router::get('/vouchers/journals/create', 'VoucherController@createJournal', $saasMiddlewares);
Router::post('/vouchers/journals/store', 'VoucherController@storeJournal', $saasMiddlewares);

// Reports & Financial Statements
Router::get('/reports', 'ReportController@index', $saasMiddlewares);
Router::get('/reports/sales', 'ReportController@salesReport', $saasMiddlewares);
Router::get('/reports/cash-book', 'ReportController@cashBook', $saasMiddlewares);
Router::get('/reports/trial-balance', 'ReportController@trialBalance', $saasMiddlewares);
Router::get('/reports/profit-loss', 'ReportController@profitAndLoss', $saasMiddlewares);
Router::get('/reports/item-profit', 'ReportController@itemProfit', $saasMiddlewares);
Router::get('/reports/gst', 'GstReportController@index', $saasMiddlewares);
Router::get('/reports/gst/gstr1', 'GstReportController@gstr1', $saasMiddlewares);
Router::get('/reports/gst/gstr3b', 'GstReportController@gstr3b', $saasMiddlewares);

// Printing Invoices (A4, A5, Thermal)
Router::get('/invoices/print/{id}', 'InvoicePrintController@printInvoice', $saasMiddlewares);

// Bill Designs Gallery & Activation (Tenant Scoped)
Router::get('/settings/bill-designs', 'BillDesignController@gallery', $saasMiddlewares);
Router::get('/settings/bill-designs/preview/{id}', 'BillDesignController@preview', $saasMiddlewares);
Router::post('/settings/bill-designs/activate/{id}', 'BillDesignController@activate', $saasMiddlewares);

// Bill Designs Tenant APIs
Router::get('/api/bill-designs', 'BillDesignApiController@index', $saasMiddlewares);
Router::get('/api/bill-designs/{id}', 'BillDesignApiController@show', $saasMiddlewares);
Router::get('/api/bill-designs/{id}/preview', 'BillDesignApiController@previewData', $saasMiddlewares);
Router::post('/api/bill-designs/{id}/activate', 'BillDesignApiController@activate', $saasMiddlewares);
Router::get('/api/company/bill-design-settings', 'BillDesignApiController@companySettings', $saasMiddlewares);
Router::get('/api/company/available-bill-designs', 'BillDesignApiController@availableDesigns', $saasMiddlewares);

// Settings, Backup & Team
Router::get('/settings', 'SettingsController@index', $saasMiddlewares);
Router::get('/settings/company', 'SettingsController@company', $saasMiddlewares);
Router::post('/settings/company/update', 'SettingsController@updateCompany', $saasMiddlewares);
Router::get('/settings/invoice', 'SettingsController@invoice', $saasMiddlewares);
Router::post('/settings/invoice/update', 'SettingsController@updateInvoice', $saasMiddlewares);
Router::get('/settings/users', 'SettingsController@users', $saasMiddlewares);
Router::get('/settings/backup', 'BackupController@index', $saasMiddlewares);
Router::get('/settings/backup/download', 'BackupController@download', $saasMiddlewares);
Router::post('/settings/backup/restore', 'BackupController@restore', $saasMiddlewares);

// Backup Direct Routes
Router::get('/backup', 'BackupController@index', $saasMiddlewares);
Router::get('/backup/download', 'BackupController@download', $saasMiddlewares);
Router::post('/backup/restore', 'BackupController@restore', $saasMiddlewares);

// Subscription & Upgrade
Router::get('/subscription', 'SubscriptionController@index', [AuthMiddleware::class, TenantMiddleware::class]);
Router::get('/subscription/plans', 'SubscriptionController@index', [AuthMiddleware::class, TenantMiddleware::class]);
Router::post('/subscription/upgrade/{id}', 'SubscriptionController@upgrade', [AuthMiddleware::class, TenantMiddleware::class]);

// -------------------------------------------------------------
// 4. SUPER ADMIN PORTAL (/admin)
// -------------------------------------------------------------
$superAdminMiddlewares = [AuthMiddleware::class, SuperAdminMiddleware::class];

Router::get('/admin', 'SuperAdminController@dashboard', $superAdminMiddlewares);
Router::get('/admin/dashboard', 'SuperAdminController@dashboard', $superAdminMiddlewares);
Router::get('/admin/companies', 'SuperAdminController@companies', $superAdminMiddlewares);
Router::post('/admin/companies/toggle/{id}', 'SuperAdminController@toggleCompanyStatus', $superAdminMiddlewares);
Router::post('/admin/companies/delete/{id}', 'SuperAdminController@deleteCompany', $superAdminMiddlewares);
Router::get('/admin/plans', 'SuperAdminController@plans', $superAdminMiddlewares);
Router::post('/admin/plans/update/{id}', 'SuperAdminController@updatePlan', $superAdminMiddlewares);
Router::get('/admin/users', 'SuperAdminController@users', $superAdminMiddlewares);
Router::get('/admin/logs', 'SuperAdminController@logs', $superAdminMiddlewares);

// Super Admin Bill Design Management
Router::get('/admin/bill-designs', 'SuperAdminBillDesignController@index', $superAdminMiddlewares);
Router::get('/admin/bill-designs/create', 'SuperAdminBillDesignController@create', $superAdminMiddlewares);
Router::post('/admin/bill-designs/store', 'SuperAdminBillDesignController@store', $superAdminMiddlewares);
Router::get('/admin/bill-designs/edit/{id}', 'SuperAdminBillDesignController@edit', $superAdminMiddlewares);
Router::post('/admin/bill-designs/update/{id}', 'SuperAdminBillDesignController@update', $superAdminMiddlewares);
Router::post('/admin/bill-designs/publish/{id}', 'SuperAdminBillDesignController@publish', $superAdminMiddlewares);
Router::post('/admin/bill-designs/unpublish/{id}', 'SuperAdminBillDesignController@unpublish', $superAdminMiddlewares);
Router::post('/admin/bill-designs/archive/{id}', 'SuperAdminBillDesignController@archive', $superAdminMiddlewares);
Router::post('/admin/bill-designs/duplicate/{id}', 'SuperAdminBillDesignController@duplicate', $superAdminMiddlewares);
Router::get('/admin/bill-designs/preview/{id}', 'SuperAdminBillDesignController@preview', $superAdminMiddlewares);

// Super Admin Company Entitlements (Manual Paid Design Access Grant & Revoke)
Router::get('/admin/bill-designs/entitlements', 'SuperAdminBillDesignController@entitlements', $superAdminMiddlewares);
Router::post('/admin/bill-designs/entitlements/grant', 'SuperAdminBillDesignController@grantAccess', $superAdminMiddlewares);
Router::post('/admin/bill-designs/entitlements/revoke/{id}', 'SuperAdminBillDesignController@revokeAccess', $superAdminMiddlewares);

// Super Admin WhatsApp Enquiry Settings
Router::get('/admin/bill-designs/settings', 'SuperAdminBillDesignController@whatsappSettings', $superAdminMiddlewares);
Router::post('/admin/bill-designs/settings', 'SuperAdminBillDesignController@updateWhatsappSettings', $superAdminMiddlewares);

// Super Admin Bill Design APIs
Router::post('/api/admin/bill-designs', 'BillDesignApiController@adminStore', $superAdminMiddlewares);
Router::put('/api/admin/bill-designs/{id}', 'BillDesignApiController@adminUpdate', $superAdminMiddlewares);
Router::post('/api/admin/bill-designs/{id}', 'BillDesignApiController@adminUpdate', $superAdminMiddlewares);
Router::post('/api/admin/bill-designs/{id}/publish', 'BillDesignApiController@adminPublish', $superAdminMiddlewares);
Router::post('/api/admin/bill-designs/{id}/unpublish', 'BillDesignApiController@adminUnpublish', $superAdminMiddlewares);
Router::post('/api/admin/bill-designs/{id}/archive', 'BillDesignApiController@adminArchive', $superAdminMiddlewares);
Router::post('/api/admin/bill-designs/{id}/duplicate', 'BillDesignApiController@adminDuplicate', $superAdminMiddlewares);
Router::post('/api/admin/company-template-access/grant', 'BillDesignApiController@adminGrantAccess', $superAdminMiddlewares);
Router::post('/api/admin/company-template-access/revoke', 'BillDesignApiController@adminRevokeAccess', $superAdminMiddlewares);
Router::get('/api/admin/company-template-access', 'BillDesignApiController@adminEntitlements', $superAdminMiddlewares);
Router::post('/api/admin/bill-design-settings', 'BillDesignApiController@adminSaveSettings', $superAdminMiddlewares);
