<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>MYSLIP.IN Billing Software</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
        }
        .auth-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 24px;
            text-align: center;
        }
        .auth-body {
            padding: 30px;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-header">
        <a href="<?= url('/') ?>" class="text-decoration-none">
            <h3 class="fw-bold mb-1 text-primary"><i class="bi bi-receipt"></i> MY<span class="text-dark">SLIP</span></h3>
        </a>
        <p class="text-muted small mb-0">Billing & Accounting Made Simple</p>
    </div>

    <div class="auth-body">
        <?php \App\Core\View::partial('alerts'); ?>
        <?= $content ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
