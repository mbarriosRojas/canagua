<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programas - <?php echo Config::getAppName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        
        body { background-color: #f8f9fa; }
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
        .main-content { padding: 2rem; }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
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
                        <li class="nav-item"><a class="nav-link" href="/public/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/usuarios"><i class="fas fa-users"></i> Usuarios</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/programas"><i class="fas fa-list-alt"></i> Programas</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/instituciones"><i class="fas fa-building"></i> Instituciones</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/estudiantes"><i class="fas fa-user-graduate"></i> Estudiantes</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/personal"><i class="fas fa-chalkboard-teacher"></i> Personal</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/talleres"><i class="fas fa-tools"></i> Talleres</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/cursos"><i class="fas fa-book"></i> Cursos</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/inventario"><i class="fas fa-boxes"></i> Inventario</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/calificaciones"><i class="fas fa-chart-line"></i> Calificaciones</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="/public/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-list-alt me-2"></i>Gestión de Programas</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProgramaModal">
                            <i class="fas fa-plus me-2"></i>Nuevo Programa
                        </button>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <!-- Tabla de programas -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="programasTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Sub Área</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
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

    <!-- Modal Crear Programa -->
    <div class="modal fade" id="createProgramaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Nuevo Programa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createProgramaForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cod_programas" class="form-label">Código del Programa *</label>
                                <input type="text" class="form-control" id="cod_programas" name="cod_programas" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sub_area" class="form-label">Sub Área</label>
                                <input type="text" class="form-control" id="sub_area" name="sub_area">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="descripcion" class="form-label">Descripción *</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let programasTable;
            
            function initTable() {
                programasTable = $('#programasTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: '/public/programas/list',
                        type: 'GET'
                    },
                    columns: [
                        { data: 'cod_programas' },
                        { data: 'descripcion' },
                        { data: 'sub_area' },
                        { 
                            data: 'activo',
                            render: function(data, type, row) {
                                return data ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';
                            }
                        },
                        { 
                            data: 'fecha_creacion',
                            render: function(data, type, row) {
                                return data ? new Date(data).toLocaleDateString('es-ES') : '-';
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editPrograma(${row.id_programas})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePrograma(${row.id_programas})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    ],
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                    pageLength: 10,
                    responsive: true
                });
            }
            
            function showAlert(message, type) {
                const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
                $('#alertContainer').html(alertHtml);
                setTimeout(() => { $('#alertContainer .alert').alert('close'); }, 5000);
            }
            
            $('#createProgramaForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '/public/programas/create',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#createProgramaModal').modal('hide');
                            $('#createProgramaForm')[0].reset();
                            showAlert('Programa creado exitosamente', 'success');
                            programasTable.ajax.reload();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al crear programa', 'danger');
                    }
                });
            });
            
            window.editPrograma = function(id) {
                showAlert('Funcionalidad de edición en desarrollo', 'info');
            };
            
            window.deletePrograma = function(id) {
                if (confirm('¿Está seguro de que desea eliminar este programa?')) {
                    $.ajax({
                        url: `/public/programas/delete/${id}`,
                        method: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showAlert('Programa eliminado exitosamente', 'success');
                                programasTable.ajax.reload();
                            } else {
                                showAlert(response.message, 'danger');
                            }
                        },
                        error: function() {
                            showAlert('Error al eliminar programa', 'danger');
                        }
                    });
                }
            };
            
            initTable();
        });
    </script>
</body>
</html>
