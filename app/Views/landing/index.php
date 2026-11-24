<!-- Hero Section -->
<section class="landing-hero text-center text-lg-start">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="badge bg-info-subtle text-info fw-bold mb-3 px-3 py-2 rounded-pill">
                    <i class="bi bi-shield-check me-1"></i> 100% Indian GST & Non-GST Compliant
                </div>
                <h1 class="fw-extrabold text-white">
                    Simple Billing & Accounting for Your Business
                </h1>
                <p class="lead">
                    Create invoices, manage stock, customers, purchases, accounts, and GST reports from anywhere. Designed for fast retail, wholesale, and services.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start mb-4">
                    <a href="<?= url('/register') ?>" class="btn btn-primary btn-lg fw-bold px-4 shadow">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Start 30-Day Free Trial
                    </a>
                    <a href="#pricing" class="btn btn-outline-light btn-lg fw-bold px-4">
                        <i class="bi bi-award me-1"></i> View Pricing
                    </a>
                </div>
                <div class="d-flex align-items-center gap-4 text-secondary small justify-content-center justify-content-lg-start">
                    <div><i class="bi bi-check2-circle text-success me-1"></i> No Credit Card Required</div>
                    <div><i class="bi bi-check2-circle text-success me-1"></i> Instant Setup in 2 Mins</div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="position-relative p-2 bg-white rounded-4 shadow-lg border" style="background: rgba(255,255,255,0.05) !important; border-color: rgba(255,255,255,0.15) !important;">
                    <div class="bg-dark rounded-3 p-3 text-start text-light" style="font-family: monospace; font-size: 0.85rem;">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary">
                            <span class="text-info fw-bold"><i class="bi bi-receipt me-1"></i> MYSLIP Fast POS Terminal</span>
                            <span class="badge bg-success">GST Active (27)</span>
                        </div>
                        <div class="mb-2 text-muted">Customer: <strong class="text-white">Sharma Trading Co.</strong></div>
                        <table class="table table-dark table-sm mb-3">
                            <thead>
                                <tr class="text-secondary">
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Rate</th>
                                    <th class="text-end">GST</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>LED 43 Inch TV</td>
                                    <td class="text-center">2 PCS</td>
                                    <td class="text-end">₹22,000</td>
                                    <td class="text-end">18%</td>
                                    <td class="text-end">₹51,920</td>
                                </tr>
                                <tr>
                                    <td>Wireless Speaker</td>
                                    <td class="text-center">5 NOS</td>
                                    <td class="text-end">₹1,500</td>
                                    <td class="text-end">18%</td>
                                    <td class="text-end">₹8,850</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between pt-2 border-top border-secondary fw-bold fs-6">
                            <span>Grand Total (Net)</span>
                            <span class="text-success fs-5">₹60,770.00</span>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button class="btn btn-sm btn-primary w-50"><i class="bi bi-printer me-1"></i> Print A4/Thermal</button>
                            <button class="btn btn-sm btn-success w-50"><i class="bi bi-whatsapp me-1"></i> Share WhatsApp</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Features Section -->
<section id="features" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <h6 class="text-primary fw-bold text-uppercase">Powerful Capabilities</h6>
            <h2 class="fw-bold">Everything Your Business Needs to Grow</h2>
            <p class="text-muted">Designed specifically for Indian MSMEs, traders, retailers, wholesalers, and service providers.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                    <h5 class="fw-bold mb-2">GST & Non-GST Billing</h5>
                    <p class="text-muted small">
                        One-click switch between GST tax invoices with state-level IGST / CGST+SGST calculation and clean Non-GST bills of supply.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Inventory & Stock Ledger</h5>
                    <p class="text-muted small">
                        Track stock movements with real-time quantities, low stock reorder alerts, barcode scanning, and weighted average cost tracking.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Double-Entry Accounting</h5>
                    <p class="text-muted small">
                        Automated ledger postings behind every sale, purchase, receipt, and payment. Trial Balance, Profit & Loss, and Balance Sheet built-in.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-printer"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Multi-Format Printing</h5>
                    <p class="text-muted small">
                        Print professional A4 invoices, compact A5 bills, and high-speed 3-inch (80mm) thermal POS receipts with UPI QR codes.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-bold mb-2">GSTR-1 & 3B Tax Reports</h5>
                    <p class="text-muted small">
                        Instant B2B, B2C, HSN summary, and tax registers ready for your accountant or CA for monthly return filing.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-file-earmark-excel"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Excel & Cloud Backup</h5>
                    <p class="text-muted small">
                        Export all reports to Excel (XLSX/CSV) and download 1-click single-tenant backup archives of your business data.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h6 class="text-primary fw-bold text-uppercase">Transparent Pricing</h6>
            <h2 class="fw-bold">Simple, Affordable Plans for Every Stage</h2>
            <p class="text-muted">Start with our risk-free 30-day trial. Upgrade whenever you are ready.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($plans as $plan): ?>
            <div class="col-lg-3 col-md-6">
                <div class="pricing-card <?= $plan['is_popular'] ? 'popular' : '' ?>">
                    <?php if ($plan['is_popular']): ?>
                        <div class="popular-badge">Most Popular</div>
                    <?php endif; ?>

                    <h5 class="fw-bold text-dark mb-1"><?= e($plan['name']) ?></h5>
                    <div class="pricing-price">
                        <?= (float)$plan['price'] == 0 ? 'Free' : formatCurrency($plan['price']) ?>
                        <?php if ((float)$plan['price'] > 0): ?>
                            <span class="text-muted fs-6 fw-normal">/ year</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted small mb-4">
                        <?= (float)$plan['price'] == 0 ? 'Full feature access for 30 days.' : 'Includes continuous updates & cloud storage.' ?>
                    </p>

                    <ul class="list-unstyled small mb-4">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Up to <strong><?= $plan['max_users'] ?> Team Users</strong></li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> <strong><?= $plan['max_items'] ?></strong> Item Catalog</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> <strong>GST & Non-GST</strong> Billing</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> A4, A5 & <strong>3-inch Thermal</strong></li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Double-Entry <strong>Accounting</strong></li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> <strong>GSTR-1 & 3B</strong> Tax Registers</li>
                    </ul>

                    <a href="<?= url('/register') ?>" class="btn <?= $plan['is_popular'] ? 'btn-primary' : 'btn-outline-dark' ?> fw-bold w-100 py-2">
                        <?= (float)$plan['price'] == 0 ? 'Start 30-Day Trial' : 'Choose ' . e($plan['name']) ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section id="faq" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h6 class="text-primary fw-bold text-uppercase">Frequently Asked Questions</h6>
            <h2 class="fw-bold">Got Questions? We Have Answers</h2>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion shadow-sm" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Can I use MYSLIP if my business does not have a GSTIN?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted small">
                                Yes, absolutely! When creating your company, simply select <strong>GST Enabled: NO</strong>. The system will cleanly hide tax rates, CGST/SGST/IGST columns, and GSTIN validations, giving you a streamlined Non-GST billing experience.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Can I print bills on standard 3-inch thermal POS receipt printers?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted small">
                                Yes. MYSLIP includes dedicated printer templates for standard A4, A5, and 3-inch (80mm) thermal POS printers. You can set your company default format in Invoice Settings.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Is my company data isolated and secure from other tenants?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted small">
                                Yes. Every record is strictly tenant-scoped using session-authenticated company IDs and database transactions. You also have full control to download single-tenant JSON backup archives at any time.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
