<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MYSLIP.IN — Simple GST Billing & Accounting Software for Indian Businesses</title>
    <meta name="description" content="Create GST & Non-GST invoices, track inventory, manage customers, vendors, double-entry accounting, and generate GSTR-1, GSTR-3B tax reports with MYSLIP.IN.">
    
    <!-- OpenGraph -->
    <meta property="og:title" content="MYSLIP.IN — Simple GST Billing & Accounting">
    <meta property="og:description" content="Create invoices, manage stock, purchases, receipts, ledger, and GST reports from anywhere.">
    <meta property="og:url" content="<?= url('/') ?>">
    <meta property="og:type" content="website">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/landing.css') ?>" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-3" style="background-color: #0f172a !important;">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center gap-2" href="<?= url('/') ?>">
            <i class="bi bi-receipt text-primary"></i>
            MY<span class="text-info">SLIP</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="landingNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fw-medium">
                <li class="nav-item"><a class="nav-link text-light" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="#billing">Billing & Invoicing</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="#accounting">Accounting</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="#gst">GST Compliance</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="#pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="#faq">FAQ</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if (\App\Core\Session::isAuthenticated()): ?>
                    <a href="<?= url('/dashboard') ?>" class="btn btn-primary fw-bold">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?= url('/login') ?>" class="btn btn-outline-light btn-sm px-3 fw-bold">Login</a>
                    <a href="<?= url('/register') ?>" class="btn btn-primary btn-sm px-3 fw-bold">
                        Start 30-Day Free Trial
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Main Page Content -->
<?= $content ?>

<!-- Footer -->
<footer class="bg-dark text-light py-5" style="background-color: #090d16 !important;">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h4 class="fw-bold mb-3 d-flex align-items-center gap-2 text-white">
                    <i class="bi bi-receipt text-primary"></i> MY<span class="text-info">SLIP</span>
                </h4>
                <p class="text-secondary small">
                    MYSLIP.IN is a cloud-based multi-tenant billing, inventory, GST compliance, and accounting application designed specifically for Indian small and medium enterprises.
                </p>
                <div class="d-flex gap-3 fs-5 text-secondary">
                    <i class="bi bi-shield-check text-success"></i>
                    <span class="small">100% Data Isolated & Encrypted</span>
                </div>
            </div>

            <div class="col-lg-2 col-6">
                <h6 class="text-white fw-bold mb-3">Product</h6>
                <ul class="list-unstyled text-secondary small">
                    <li class="mb-2"><a href="#features" class="text-secondary text-decoration-none">GST Billing</a></li>
                    <li class="mb-2"><a href="#features" class="text-secondary text-decoration-none">Inventory Control</a></li>
                    <li class="mb-2"><a href="#features" class="text-secondary text-decoration-none">Customer Ledger</a></li>
                    <li class="mb-2"><a href="#features" class="text-secondary text-decoration-none">GSTR Reports</a></li>
                    <li class="mb-2"><a href="#pricing" class="text-secondary text-decoration-none">Pricing Plans</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-6">
                <h6 class="text-white fw-bold mb-3">Print Formats</h6>
                <ul class="list-unstyled text-secondary small">
                    <li class="mb-2">A4 GST Tax Invoice</li>
                    <li class="mb-2">A5 Compact Bill</li>
                    <li class="mb-2">3-Inch Thermal POS Receipt</li>
                    <li class="mb-2">WhatsApp Invoice PDF</li>
                    <li class="mb-2">Excel Export (XLSX)</li>
                </ul>
            </div>

            <div class="col-lg-4">
                <h6 class="text-white fw-bold mb-3">Get Started</h6>
                <p class="text-secondary small">No credit card required. Experience 30 days of complete business billing without limits.</p>
                <a href="<?= url('/register') ?>" class="btn btn-primary fw-bold w-100 mb-2">
                    Start 30-Day Free Trial
                </a>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-secondary small">
            <p class="mb-0">&copy; <?= date('Y') ?> MYSLIP.IN. All Rights Reserved. Made for Indian Businesses.</p>
            <div class="d-flex gap-3 mt-2 mt-md-0">
                <span>Fast. Secure. GST Compliant.</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
