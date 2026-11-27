<h4 class="fw-bold mb-3 text-center">Start 30-Day Free Trial</h4>
<p class="text-muted small text-center mb-4">No credit card required. Full feature access.</p>

<form action="<?= url('/register') ?>" method="POST">
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
            <input type="tel" name="mobile" class="form-control" placeholder="98XXXXXXXX" pattern="[0-9]{10}" value="<?= e(old('mobile')) ?>" required>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="••••••••" minlength="6" required>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Confirm Password <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" minlength="6" required>
            </div>
        </div>
    </div>

    <div class="mb-4 form-check">
        <input type="checkbox" class="form-check-input" id="termsCheck" required checked>
        <label class="form-check-label small text-muted" for="termsCheck">
            I accept the <a href="#" class="text-decoration-none">Terms of Service</a> & <a href="#" class="text-decoration-none">Privacy Policy</a>
        </label>
    </div>

    <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm">
        <i class="bi bi-rocket-takeoff-fill me-1"></i> Register & Start 30-Day Trial
    </button>
</form>

<div class="mt-4 pt-3 border-top text-center small text-muted">
    Already have an account?
    <a href="<?= url('/login') ?>" class="fw-bold text-primary text-decoration-none">Log In</a>
</div>
