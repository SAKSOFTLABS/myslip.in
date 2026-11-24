<h4 class="fw-bold mb-3 text-center">Reset Password</h4>
<p class="text-muted small text-center mb-4">Enter your registered email address to receive password recovery instructions.</p>

<form action="<?= url('/forgot-password') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="mb-4">
        <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="name@business.com" required autofocus>
        </div>
    </div>

    <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm mb-3">
        <i class="bi bi-send-fill me-1"></i> Send Password Reset Link
    </button>
</form>

<div class="text-center small text-muted">
    Remember your password?
    <a href="<?= url('/login') ?>" class="fw-bold text-primary text-decoration-none">Back to Login</a>
</div>
