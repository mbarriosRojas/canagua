<?php
$config = $config ?? [];
$usuarios = $usuarios ?? [];
$firmantes = $firmantes ?? [];
$nombrePlataforma = $config['nombre'] ?? 'Sistema';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de usuarios</title>
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
        <p class="report-title">Reporte de usuarios</p>
        <p class="report-date">Fecha: <?php echo date('d/m/Y H:i'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Nombre completo</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Último acceso</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?php echo (int)($u['id_usuario'] ?? 0); ?></td>
                <td><?php echo htmlspecialchars($u['username'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars(trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? ''))); ?></td>
                <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($u['rol'] ?? ''); ?></td>
                <td><?php echo !empty($u['activo']) ? 'Activo' : 'Inactivo'; ?></td>
                <td><?php echo !empty($u['ultimo_acceso']) ? date('d/m/Y H:i', strtotime($u['ultimo_acceso'])) : 'Nunca'; ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($usuarios)): ?>
            <tr><td colspan="7" style="text-align:center;color:#666;">No hay usuarios registrados.</td></tr>
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
