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
    <title>Reporte de cursos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 20px; }
        .report-header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #333; }
        .report-header h1 { margin: 0 0 5px 0; font-size: 18px; }
        .report-title { font-size: 14px; font-weight: bold; margin: 5px 0; }
        .report-date { font-size: 10px; color: #555; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #333; color: #fff; font-weight: bold; }
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
        <p class="report-title">Reporte de cursos</p>
        <p class="report-date">Fecha: <?php echo date('d/m/Y H:i'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cédula participante</th>
                <th>Nombre participante</th>
                <th>Código curso</th>
                <th>Nombre curso</th>
                <th>Instructor</th>
                <th>Año</th>
                <th>Periodo</th>
                <th>Fecha creación</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reportResults as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['cedula_participante'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars(trim(($row['participante_apellido'] ?? '') . ' ' . ($row['participante_nombre'] ?? ''))); ?></td>
                <td><?php echo htmlspecialchars($row['cod_curso'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['nombre_curso'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['instructor'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['ano'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['periodo'] ?? '-'); ?></td>
                <td><?php echo !empty($row['fecha_creacion']) ? date('d/m/Y', strtotime($row['fecha_creacion'])) : '-'; ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($reportResults)): ?>
            <tr><td colspan="8" style="text-align:center;color:#666;">No se encontraron registros con los filtros indicados.</td></tr>
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
