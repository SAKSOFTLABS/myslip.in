<h4 class="fw-bold mb-3 text-center">Welcome Back</h4>
<p class="text-muted small text-center mb-4">Sign in to manage your invoices, stock & accounts</p>

<form id="loginForm" action="<?= url('/login') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="name@business.com" required autofocus>
        </div>
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <label class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
            <a href="<?= url('/forgot-password') ?>" class="small text-decoration-none">Forgot password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
    </div>

    <div class="mb-4 form-check">
        <input type="checkbox" class="form-check-input" id="rememberMe">
        <label class="form-check-label small text-muted" for="rememberMe">Remember my login</label>
    </div>

    <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Dashboard
    </button>
</form>

<div class="mt-4 pt-3 border-top text-center small text-muted">
    Don't have a business account yet?
    <a href="<?= url('/register') ?>" class="fw-bold text-primary text-decoration-none">Start 30-Day Free Trial</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    if (form) {
        form.addEventListener('submit', function() {
            localStorage.setItem('myslip_fullscreen_locked', '1');
            sessionStorage.setItem('myslip_auto_fullscreen', '1');
            sessionStorage.removeItem('myslip_fullscreen_disabled');
            try {
                const el = document.documentElement;
                const rfs = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
                if (!document.fullscreenElement && rfs) {
                    rfs.call(el).catch(function() {});
                }
            } catch(e) {}
        });
    }
});
</script>
