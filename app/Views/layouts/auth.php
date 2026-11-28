<?php
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$isRegister = str_contains($requestUri, 'register') || str_contains($requestUri, 'company/create');
?>
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
            max-width: <?= $isRegister ? '680px' : '480px' ?>;
            overflow: hidden;
            position: relative;
            transition: max-width 0.3s ease;
        }
        .auth-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 24px;
            text-align: center;
            position: relative;
        }
        .auth-body {
            padding: 30px;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-header">
        <?php if ($isRegister): ?>
            <button type="button" class="btn btn-sm btn-light border position-absolute top-0 end-0 m-3 d-flex align-items-center justify-content-center" id="btnAuthFs" title="Toggle Fullscreen" style="width: 32px; height: 32px;">
                <i class="bi bi-arrows-fullscreen" id="authFsIcon"></i>
            </button>
        <?php endif; ?>
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
<script src="<?= asset('js/app.js') ?>"></script>

<?php if ($isRegister): ?>
<script>
(function() {
    function requestFullscreen() {
        if (!document.fullscreenElement) {
            const el = document.documentElement;
            const rfs = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
            if (rfs) {
                rfs.call(el).catch(function() {});
            }
        }
    }

    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            localStorage.setItem('myslip_fullscreen_locked', '1');
            requestFullscreen();
        } else {
            localStorage.setItem('myslip_fullscreen_locked', '0');
            const efs = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
            if (efs) {
                efs.call(document).catch(function() {});
            }
        }
    }

    function updateIcon() {
        const icon = document.getElementById('authFsIcon');
        if (icon) {
            icon.className = document.fullscreenElement ? 'bi bi-fullscreen-exit text-primary' : 'bi bi-arrows-fullscreen';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Attempt fullscreen on registration page
        requestFullscreen();

        // Trigger on first click or touch interaction
        function onGesture() {
            if (!document.fullscreenElement) {
                requestFullscreen();
            }
        }
        document.addEventListener('click', onGesture, { passive: true });
        document.addEventListener('keydown', onGesture, { passive: true });

        const btn = document.getElementById('btnAuthFs');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleFullscreen();
            });
        }
    });

    document.addEventListener('fullscreenchange', updateIcon);
    document.addEventListener('webkitfullscreenchange', updateIcon);
})();
</script>
<?php endif; ?>

</body>
</html>
