<?php
// Partial: sidebar con submenús para programas, instituciones, estudiantes, personal, talleres, cursos, inventario
// Variables: $currentModule = 'programas'|'instituciones'|'estudiantes'|'personal'|'talleres'|'cursos'|'inventario'|null
//           $currentSection = 'index'|'create'|'edit'|'reportes' (solo si $currentModule está en los 7 módulos)
$currentModule = $currentModule ?? null;
$currentSection = $currentSection ?? 'index';
$modulesWithSubmenu = ['programas', 'instituciones', 'estudiantes', 'participantes', 'talleres', 'calificaciones', 'cursos', 'personal', 'inventario'];
$menuItems = [
    'programas'     => ['icon' => 'fa-list-alt',   'label' => 'Programas',           'url' => 'programas'],
    'instituciones' => ['icon' => 'fa-building',   'label' => 'Instituciones',       'url' => 'instituciones'],
    'estudiantes'   => ['icon' => 'fa-user-graduate','label' => 'Estudiantes',       'url' => 'estudiantes'],
    'participantes' => ['icon' => 'fa-user-friends','label' => 'Participantes',      'url' => 'participantes'],
    'talleres'      => ['icon' => 'fa-tools',      'label' => 'Talleres',            'url' => 'talleres'],
    'calificaciones'=> ['icon' => 'fa-chart-line', 'label' => 'Calificaciones',      'url' => 'calificaciones'],
    'cursos'        => ['icon' => 'fa-book',       'label' => 'Cursos',              'url' => 'cursos'],
    'personal'      => ['icon' => 'fa-chalkboard-teacher', 'label' => 'Personal',    'url' => 'personal'],
    'inventario'    => ['icon' => 'fa-boxes',      'label' => 'Inventario',          'url' => 'inventario'],
];
?>
<nav class="col-md-3 col-lg-2 d-md-block sidebar">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <i class="fas fa-graduation-cap fa-2x text-white mb-2"></i>
            <h5 class="text-white"><?php echo Config::getAppName(); ?></h5>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link <?php echo $currentModule === null && ($currentSection === 'dashboard' || $currentSection === 'index') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/usuarios"><i class="fas fa-users"></i> Usuarios</a></li>
            <?php foreach ($menuItems as $key => $item): 
                $isActive = ($currentModule === $key);
                $collapseId = 'submenu-' . $key;
                $expanded = $isActive ? 'true' : 'false';
                $showClass = $isActive ? 'show' : '';
            ?>
            <li class="nav-item">
                <a class="nav-link sidebar-toggle <?php echo $isActive ? 'active' : ''; ?>" href="#" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="<?php echo $expanded; ?>">
                    <i class="fas <?php echo $item['icon']; ?>"></i> <?php echo $item['label']; ?>
                    <i class="fas fa-chevron-down ms-auto small toggle-icon"></i>
                </a>
                <div class="collapse <?php echo $showClass; ?>" id="<?php echo $collapseId; ?>">
                    <ul class="nav flex-column ps-4 pb-1">
                        <li class="nav-item">
                            <a class="nav-link py-1 <?php echo $isActive && in_array($currentSection, ['index', 'edit'], true) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/<?php echo $item['url']; ?>">
                                <i class="fas fa-list"></i> Listado
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 <?php echo $isActive && $currentSection === 'create' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/<?php echo $item['url']; ?>/create">
                                <i class="fas fa-plus"></i> Crear
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 <?php echo $isActive && $currentSection === 'reportes' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/<?php echo $item['url']; ?>/reportes">
                                <i class="fas fa-file-alt"></i> Reportes
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <?php endforeach; ?>
            <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/configuracion">
                    <i class="fas fa-cog"></i> Configuración
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/backup">
                    <i class="fas fa-database"></i> Respaldo BD
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/logs">
                    <i class="fas fa-history"></i> Trazas / Logs de ingreso
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item mt-4"><a class="nav-link" href="<?php echo BASE_URL; ?>/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>
</nav>
