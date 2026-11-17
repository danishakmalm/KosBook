<?php
/**
 * Komponen Reusable: Alert / Flash Message
 * Menerima array $flash dengan kunci: 'type' (success, danger, warning, info), 'message', 'icon'
 */
if (!empty($flash)):
    $type = $flash['type'] ?? 'info';
    $message = $flash['message'] ?? '';
    $icon = $flash['icon'] ?? ($type === 'success' ? 'bi-check-circle-fill' : ($type === 'danger' ? 'bi-exclamation-triangle-fill' : ($type === 'warning' ? 'bi-exclamation-circle-fill' : 'bi-info-circle-fill')));
?>
<div class="alert alert-<?= htmlspecialchars($type); ?> alert-dismissible fade show shadow-sm rounded-3 d-flex align-items-center mb-4" role="alert">
    <i class="bi <?= htmlspecialchars($icon); ?> fs-5 me-2 flex-shrink-0"></i>
    <div class="small fw-medium flex-grow-1">
        <?= $message; ?>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
