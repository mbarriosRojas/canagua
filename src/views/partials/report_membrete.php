<?php
$config = $config ?? [];
$reportTitle = $reportTitle ?? 'Reporte';
$nombrePlataforma = $config['nombre'] ?? 'Sistema';
?>
<div class="report-header">
    <h2 class="mb-0"><?php echo htmlspecialchars($nombrePlataforma); ?></h2>
    <p class="report-title"><?php echo htmlspecialchars($reportTitle); ?></p>
    <p class="text-muted small mb-0">Fecha: <?php echo date('d/m/Y H:i'); ?></p>
</div>
