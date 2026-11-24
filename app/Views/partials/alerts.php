<?php
$flashes = \App\Core\Session::getFlashes();
if (!empty($flashes)):
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
        foreach ($messages as $msg):
?>
    <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
        <i class="bi <?= $iconClass ?> fs-5"></i>
        <div><?= e($msg) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php 
        endforeach;
    endforeach;
endif;
?>
