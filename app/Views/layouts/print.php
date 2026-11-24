<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) : 'Invoice Print' ?> — MYSLIP.IN</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/print.css') ?>" rel="stylesheet">
</head>
<body>

<div class="no-print bg-dark text-white p-3 text-center d-flex justify-content-between align-items-center">
    <div>
        <a href="<?= url('/sales') ?>" class="btn btn-sm btn-outline-light me-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Invoices
        </a>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-light" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Invoice (Ctrl + P)
        </button>
        <button type="button" class="btn btn-sm btn-success" onclick="shareWhatsApp()">
            <i class="bi bi-whatsapp me-1"></i> Share WhatsApp
        </button>
    </div>
</div>

<div class="print-preview-wrapper">
    <?= $content ?>
</div>

<script>
function shareWhatsApp() {
    const text = encodeURIComponent("Hello, please find your invoice from " + (document.title || "MYSLIP.IN") + ". URL: " + window.location.href);
    window.open("https://api.whatsapp.com/send?text=" + text, "_blank");
}
</script>

</body>
</html>
