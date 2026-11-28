<h4 class="fw-bold mb-3 text-center">Start 30-Day Free Trial</h4>
<p class="text-muted small text-center mb-4">No credit card required. Full feature access.</p>

<form id="registerForm" action="<?= url('/register') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
            <input type="text" name="full_name" class="form-control" placeholder="e.g. Ramesh Kumar" value="<?= e(old('full_name')) ?>" required autofocus>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="name@business.com" value="<?= e(old('email')) ?>" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold small">Mobile Number <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-phone"></i></span>
            <input type="tel" name="mobile" id="reg_mobile" class="form-control" placeholder="98XXXXXXXX (10 digits)" maxlength="10" pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number" value="<?= e(old('mobile')) ?>" required>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="reg_password" class="form-control" placeholder="••••••••" minlength="6" required>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Confirm Password <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                <input type="password" name="confirm_password" id="reg_confirm_password" class="form-control" placeholder="••••••••" minlength="6" required>
            </div>
        </div>
    </div>

    <div class="mb-4 form-check">
        <input type="checkbox" class="form-check-input" id="termsCheck" required checked>
        <label class="form-check-label small text-muted" for="termsCheck">
            I accept the <a href="#" class="text-decoration-none">Terms of Service</a> & <a href="#" class="text-decoration-none">Privacy Policy</a>
        </label>
    </div>

    <button type="submit" id="btnSubmitRegister" class="btn btn-primary fw-bold w-100 py-2 shadow-sm">
        <i class="bi bi-rocket-takeoff-fill me-1"></i> Register & Start 30-Day Trial
    </button>
</form>

<div class="mt-4 pt-3 border-top text-center small text-muted">
    Already have an account?
    <a href="<?= url('/login') ?>" class="fw-bold text-primary text-decoration-none">Log In</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileEl = document.getElementById('reg_mobile');
    if (mobileEl) {
        mobileEl.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });
    }

    const formEl = document.getElementById('registerForm');
    if (formEl) {
        formEl.addEventListener('submit', function(e) {
            const pass = document.getElementById('reg_password').value;
            const confirmPass = document.getElementById('reg_confirm_password').value;
            if (pass !== confirmPass) {
                e.preventDefault();
                alert('Passwords do not match. Please ensure both passwords match before submitting.');
                document.getElementById('reg_confirm_password').focus();
                return false;
            }
            sessionStorage.setItem('myslip_auto_fullscreen', '1');
            sessionStorage.removeItem('myslip_fullscreen_disabled');
            try {
                const el = document.documentElement;
                const rfs = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
                if (!document.fullscreenElement && rfs) {
                    rfs.call(el).catch(function() {});
                }
            } catch(err) {}
        });
    }
});
</script>
