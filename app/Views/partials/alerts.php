<?php
$flashes = \App\Core\Session::getFlashes();
if (!empty($flashes)):
?>
<div class="alerts-wrapper mb-3">
<?php
    foreach ($flashes as $type => $messages):
        $alertClass = match($type) {
            'error', 'danger' => 'danger',
            'success' => 'success',
            'warning' => 'warning',
            default => 'info'
        };
        $iconClass = match($type) {
            'error', 'danger' => 'bi-exclamation-triangle-fill',
            'success' => 'bi-check-circle-fill',
            'warning' => 'bi-exclamation-circle-fill',
            default => 'bi-info-circle-fill'
        };
        $title = match($type) {
            'error', 'danger' => 'Error',
            'success' => 'Success',
            'warning' => 'Attention',
            default => 'Notice'
        };
        foreach ($messages as $msg):
?>
    <!-- Dismissible Banner Alert -->
    <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show d-flex align-items-start gap-2 shadow-sm mb-3 border-start border-4 border-<?= $alertClass ?>" role="alert">
        <i class="bi <?= $iconClass ?> fs-5 text-<?= $alertClass ?> flex-shrink-0 mt-1"></i>
        <div class="flex-grow-1">
            <strong><?= $title ?>:</strong> <?= e($msg) ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Floating Toast Pop-up Notification -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;">
        <div class="toast align-items-center text-white bg-<?= $alertClass === 'danger' ? 'danger' : ($alertClass === 'success' ? 'success' : 'primary') ?> border-0 shadow-lg show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="<?= $alertClass === 'danger' ? 'false' : 'true' ?>" data-bs-delay="6000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2 py-3 px-3 fs-6">
                    <i class="bi <?= $iconClass ?> fs-4 flex-shrink-0"></i>
                    <div>
                        <div class="fw-bold small text-uppercase opacity-75"><?= $title ?></div>
                        <div><?= e($msg) ?></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
<?php 
        endforeach;
    endforeach;
?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        var toastElements = document.querySelectorAll('.toast');
        toastElements.forEach(function(toastEl) {
            var bsToast = bootstrap.Toast.getInstance(toastEl) || new bootstrap.Toast(toastEl);
            bsToast.show();
        });
    }
});
</script>
<?php
endif;
?>
