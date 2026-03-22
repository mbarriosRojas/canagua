<?php
$firmantes = $firmantes ?? [];
if (!empty($firmantes)):
?>
<div class="firmas-block">
    <p class="small text-muted mb-2">Firmas:</p>
    <?php foreach ($firmantes as $f): ?>
    <div class="firma-item">
        <div class="firma-line"></div>
        <div class="firma-nombre"><?php echo htmlspecialchars(trim(($f['nombre'] ?? '') . ' ' . ($f['apellido'] ?? ''))); ?></div>
        <div class="firma-cargo"><?php echo htmlspecialchars($f['cargo'] ?? ''); ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
