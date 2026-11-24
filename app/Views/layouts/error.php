<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?= (int)$code ?> — MYSLIP.IN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="text-center p-5 bg-white rounded-4 shadow-sm" style="max-width: 500px;">
        <div class="text-danger mb-3">
            <i class="bi bi-exclamation-triangle-fill display-1"></i>
        </div>
        <h1 class="display-4 fw-bold"><?= (int)$code ?></h1>
        <h5 class="fw-bold mb-3"><?= htmlspecialchars($message) ?></h5>
        <p class="text-muted small mb-4">
            The requested operation could not be completed. If this persists, please contact support.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="<?= rtrim($config['url'] ?? '/', '/') ?>" class="btn btn-primary fw-semibold">
                <i class="bi bi-house-door me-1"></i> Back to Home
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                Go Back
            </button>
        </div>
    </div>
</body>
</html>
