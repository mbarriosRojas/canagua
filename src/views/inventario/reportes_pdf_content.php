<?php
$config = $config ?? [];
$reportResults = $reportResults ?? [];
$firmantes = $firmantes ?? [];
$nombrePlataforma = $config['nombre'] ?? 'Sistema';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de inventario</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 20px; }
        .report-header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #333; }
        .report-header h1 { margin: 0 0 5px 0; font-size: 18px; }
        .report-title { font-size: 14px; font-weight: bold; margin: 5px 0; }
        .report-date { font-size: 10px; color: #555; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 5px 6px; text-align: left; }
        th { background: #333; color: #fff; font-weight: bold; }
        tr.repair-row td { background-color: #f5f5f5; }
        .firmas-block { margin-top: 40px; padding-top: 20px; border-top: 1px solid #333; }
        .firmas-block p { margin: 0 0 15px 0; font-size: 10px; color: #555; }
        .firma-item { display: inline-block; text-align: center; margin-right: 40px; margin-bottom: 10px; min-width: 140px; }
        .firma-line { border-bottom: 1px solid #333; height: 30px; margin-bottom: 4px; }
        .firma-nombre { font-weight: bold; font-size: 10px; }
        .firma-cargo { font-size: 9px; color: #555; }
    </style>
</head>
<body>
    <div class="report-header">
        <h1><?php echo htmlspecialchars($nombrePlataforma); ?></h1>
        <p class="report-title">Reporte de inventario</p>
        <p class="report-date">Fecha: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Equipo</th>
                <th>Ubicación</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Estado</th>
                <th>Responsable</th>
                <th>Fecha creación</th>
                <th>Fecha reparación</th>
                <th>Motivo reparación</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reportResults)): ?>
            <tr><td colspan="10" style="text-align:center;color:#666;">No se encontraron registros con los filtros indicados.</td></tr>
            <?php else: ?>
            <?php foreach ($reportResults as $row):
                $reparaciones = $row['reparaciones'] ?? [];
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['cod_equipo'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['nombre'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['ubicacion'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['marca'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['modelo'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['estado'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['responsable'] ?? '-'); ?></td>
                <td><?php echo !empty($row['fecha_creacion']) ? date('d/m/Y', strtotime($row['fecha_creacion'])) : '-'; ?></td>
                <td><?php echo empty($reparaciones) ? '-' : ''; ?></td>
                <td><?php echo empty($reparaciones) ? '-' : ''; ?></td>
            </tr>
            <?php foreach ($reparaciones as $rep): ?>
            <tr class="repair-row">
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td><?php echo !empty($rep['fecha']) ? date('d/m/Y', strtotime($rep['fecha'])) : '-'; ?></td>
                <td><?php echo htmlspecialchars($rep['motivo'] ?? '-'); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if (!empty($firmantes)): ?>
    <div class="firmas-block">
        <p>Firmas:</p>
        <?php foreach ($firmantes as $f): ?>
        <div class="firma-item">
            <div class="firma-line"></div>
            <div class="firma-nombre"><?php echo htmlspecialchars(trim(($f['nombre'] ?? '') . ' ' . ($f['apellido'] ?? ''))); ?></div>
            <div class="firma-cargo"><?php echo htmlspecialchars($f['cargo'] ?? ''); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</body>
</html>
