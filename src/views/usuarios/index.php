<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - <?php echo Config::getAppName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .badge {
            font-size: 0.75rem;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-graduation-cap fa-2x text-white mb-2"></i>
                        <h5 class="text-white"><?php echo Config::getAppName(); ?></h5>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/public/dashboard">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link active" href="/public/usuarios">
                                <i class="fas fa-users"></i>
                                Usuarios
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/programas">
                                <i class="fas fa-list-alt"></i>
                                Programas
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/instituciones">
                                <i class="fas fa-building"></i>
                                Instituciones
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/estudiantes">
                                <i class="fas fa-user-graduate"></i>
                                Estudiantes
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/personal">
                                <i class="fas fa-chalkboard-teacher"></i>
                                Personal
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/talleres">
                                <i class="fas fa-tools"></i>
                                Talleres
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/cursos">
                                <i class="fas fa-book"></i>
                                Cursos
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/inventario">
                                <i class="fas fa-boxes"></i>
                                Inventario
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/calificaciones">
                                <i class="fas fa-chart-line"></i>
                                Calificaciones
                            </a>
                        </li>
                        
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="/public/logout">
                                <i class="fas fa-sign-out-alt"></i>
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg">
                    <div class="container-fluid">
                        <h4 class="mb-0">Gestión de Usuarios</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                            <i class="fas fa-plus me-2"></i>
                            Nuevo Usuario
                        </button>
                    </div>
                </nav>
                
                <!-- Users Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>
                            Lista de Usuarios
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="usersTable" class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Nombre Completo</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Último Acceso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>
                        Crear Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createUserForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-control" id="rol" name="rol" required>
                                    <option value="">Seleccionar rol</option>
                                    <option value="operador">Operador</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-key me-2"></i>
                        Cambiar Contraseña
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="changePasswordForm">
                    <div class="modal-body">
                        <input type="hidden" id="userId" name="userId">
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            var table = $('#usersTable').DataTable({
                ajax: {
                    url: '/public/usuarios/list',
                    dataSrc: ''
                },
                columns: [
                    { data: 'id_usuario' },
                    { data: 'username' },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return row.nombre + ' ' + row.apellido;
                        }
                    },
                    { data: 'email' },
                    { 
                        data: 'rol',
                        render: function(data, type, row) {
                            var badgeClass = data === 'admin' ? 'bg-danger' : 
                                           data === 'supervisor' ? 'bg-warning' : 'bg-info';
                            return '<span class="badge ' + badgeClass + '">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                        }
                    },
                    { 
                        data: 'activo',
                        render: function(data, type, row) {
                            return data ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';
                        }
                    },
                    { 
                        data: 'ultimo_acceso',
                        render: function(data, type, row) {
                            return data ? new Date(data).toLocaleString() : 'Nunca';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changePassword(${row.id_usuario})">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-${row.activo ? 'warning' : 'success'}" onclick="toggleUserStatus(${row.id_usuario})">
                                        <i class="fas fa-${row.activo ? 'ban' : 'check'}"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                responsive: true
            });
            
            // Crear usuario
            $('#createUserForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '/public/usuarios/create',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#createUserModal').modal('hide');
                            table.ajax.reload();
                            showAlert('Usuario creado exitosamente', 'success');
                            $('#createUserForm')[0].reset();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al crear usuario', 'danger');
                    }
                });
            });
            
            // Cambiar contraseña
            $('#changePasswordForm').on('submit', function(e) {
                e.preventDefault();
                
                var newPassword = $('#newPassword').val();
                var confirmPassword = $('#confirmPassword').val();
                
                if (newPassword !== confirmPassword) {
                    showAlert('Las contraseñas no coinciden', 'danger');
                    return;
                }
                
                $.ajax({
                    url: '/public/usuarios/change-password',
                    method: 'POST',
                    data: {
                        userId: $('#userId').val(),
                        newPassword: newPassword
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#changePasswordModal').modal('hide');
                            showAlert('Contraseña cambiada exitosamente', 'success');
                            $('#changePasswordForm')[0].reset();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al cambiar contraseña', 'danger');
                    }
                });
            });
        });
        
        function changePassword(userId) {
            $('#userId').val(userId);
            $('#changePasswordModal').modal('show');
        }
        
        function toggleUserStatus(userId) {
            if (confirm('¿Está seguro de cambiar el estado de este usuario?')) {
                $.ajax({
                    url: '/public/usuarios/toggle-status',
                    method: 'POST',
                    data: { userId: userId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#usersTable').DataTable().ajax.reload();
                            showAlert('Estado del usuario actualizado', 'success');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al actualizar estado', 'danger');
                    }
                });
            }
        }
        
        function showAlert(message, type) {
            var alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('.main-content').prepend(alertHtml);
            
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        }
    </script>
</body>
</html>
