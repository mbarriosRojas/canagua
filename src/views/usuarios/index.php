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
        :root { --primary-color: #667eea; --secondary-color: #764ba2; }
        body { background-color: #f8f9fa; }
        .sidebar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); min-height: 100vh; box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1); }
        .sidebar .nav-link { color: rgba(255, 255, 255, 0.8); padding: 12px 20px; border-radius: 8px; margin: 5px 10px; transition: all 0.3s ease; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: white; background-color: rgba(255, 255, 255, 0.2); transform: translateX(5px); }
        .main-content { padding: 2rem; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border: none; border-radius: 8px; }
        .form-control { border-radius: 8px; border: 2px solid #e9ecef; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
        .modal-content { border-radius: 15px; border: none; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $currentModule = null; $currentSection = 'usuarios'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-users me-2"></i>Gestión de Usuarios</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?php echo BASE_URL; ?>/usuarios/reportes-pdf" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-print me-2"></i>Imprimir
                        </a>
                        <a href="<?php echo BASE_URL; ?>/usuarios/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nuevo Usuario
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="usersTable" class="table table-hover">
                                <thead class="table-dark">
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
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
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
    
    <?php include __DIR__ . '/../partials/uppercase-forms.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') === '1') {
                showAlert('Usuario guardado correctamente.', 'success');
                if (window.history.replaceState) window.history.replaceState({}, '', '<?php echo BASE_URL; ?>/usuarios');
            }
            if (urlParams.get('error') === 'notfound') {
                showAlert('Usuario no encontrado.', 'danger');
                if (window.history.replaceState) window.history.replaceState({}, '', '<?php echo BASE_URL; ?>/usuarios');
            }
            // Inicializar DataTable
            var table = $('#usersTable').DataTable({
                ajax: {
                    url: '<?php echo BASE_URL; ?>/usuarios/list',
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
                            var currentUserId = <?php echo json_encode($_SESSION['user_id'] ?? 0); ?>;
                            var isAdmin = <?php echo json_encode(($_SESSION['rol'] ?? '') === 'admin'); ?>;
                            var canDelete = isAdmin && currentUserId !== row.id_usuario;
                            var fullName = (row.nombre || '') + ' ' + (row.apellido || '');
                            return `
                                <div class="btn-group" role="group">
                                    <a href="<?php echo BASE_URL; ?>/usuarios/edit/${row.id_usuario}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changePassword(${row.id_usuario})" title="Cambiar contraseña">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-${row.activo ? 'warning' : 'success'}" onclick="toggleUserStatus(${row.id_usuario})" title="${row.activo ? 'Desactivar' : 'Activar'}">
                                        <i class="fas fa-${row.activo ? 'ban' : 'check'}"></i>
                                    </button>
                                    ${canDelete ? `<button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" data-user-id="${row.id_usuario}" data-user-name="${fullName.replace(/"/g, '&quot;')}" onclick="deleteUser(this)"><i class="fas fa-trash-alt"></i></button>` : ''}
                                </div>
                            `;
                        }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                paging: false,
                info: false,
                responsive: true
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
                    url: '<?php echo BASE_URL; ?>/usuarios/change-password',
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
            Swal.fire({
                title: '¿Cambiar estado?',
                text: '¿Está seguro de que desea cambiar el estado de este usuario?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?php echo BASE_URL; ?>/usuarios/toggle-status',
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
            });
        }
        
        function deleteUser(btn) {
            var userId = btn.getAttribute('data-user-id');
            var userName = (btn.getAttribute('data-user-name') || '').replace(/&quot;/g, '"');
            var msg = userName ? '¿Está seguro de que desea eliminar al usuario ' + userName + '? Esta acción no se puede deshacer.' : '¿Está seguro de que desea eliminar este usuario? Esta acción no se puede deshacer.';
            Swal.fire({
                title: '¿Eliminar usuario?',
                text: msg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?php echo BASE_URL; ?>/usuarios/delete',
                        method: 'POST',
                        data: { userId: userId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                $('#usersTable').DataTable().ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: 'El usuario ha sido eliminado correctamente.'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'No se pudo eliminar el usuario.'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al eliminar el usuario.'
                            });
                        }
                    });
                }
            });
        }
        
        function showAlert(message, type) {
            var icon = type === 'success' ? 'success' : type === 'danger' ? 'error' : type === 'warning' ? 'warning' : 'info';
            var title = type === 'success' ? 'Éxito' : type === 'danger' ? 'Error' : type === 'warning' ? 'Aviso' : 'Información';
            Swal.fire({ icon: icon, title: title, text: message });
        }
    </script>
</body>
</html>
