<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - <?php echo Config::getAppName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #667eea; --secondary-color: #764ba2; }
        body { background-color: #f8f9fa; }
        .sidebar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); min-height: 100vh; }
        .sidebar .nav-link { color: rgba(255, 255, 255, 0.8); padding: 12px 20px; border-radius: 8px; margin: 5px 10px; transition: all 0.3s ease; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: white; background-color: rgba(255, 255, 255, 0.2); transform: translateX(5px); }
        .main-content { padding: 2rem; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border: none; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $currentModule = 'inventario'; $currentSection = 'index'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-boxes me-2"></i>Gestión de Inventario</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?php echo BASE_URL; ?>/inventario/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Registrar
                        </a>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="inventarioTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Equipo</th>
                                        <th>Ubicación</th>
                                        <th>Marca</th>
                                        <th>Modelo</th>
                                        <th>Serial</th>
                                        <th>Cantidad</th>
                                        <th>Estado</th>
                                        <th>Responsable</th>
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

    <?php include __DIR__ . '/../partials/uppercase-forms.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let inventarioTable;
            
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') === '1') {
                Swal.fire({ icon: 'success', title: 'Éxito', text: 'Operación realizada correctamente.' });
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            if (urlParams.get('error') === 'notfound') {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Item de inventario no encontrado.' });
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            function initTable() {
                inventarioTable = $('#inventarioTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: { url: '<?php echo BASE_URL; ?>/inventario/list', type: 'GET' },
                    columns: [
                        { data: 'cod_equipo' },
                        { data: 'nombre', defaultContent: '-' },
                        { data: 'ubicacion', defaultContent: '-' },
                        { data: 'marca', defaultContent: '-' },
                        { data: 'modelo', defaultContent: '-' },
                        { data: 'serial', defaultContent: '-' },
                        { data: 'cantidad' },
                        { 
                            data: 'estado',
                            render: function(data, type, row) {
                                let badgeClass = 'secondary';
                                if (data === 'Bueno') badgeClass = 'success';
                                else if (data === 'Regular') badgeClass = 'warning';
                                else if (data === 'Malo') badgeClass = 'danger';
                                else if (data === 'Fuera de Servicio') badgeClass = 'dark';
                                
                                return `<span class="badge bg-${badgeClass}">${data}</span>`;
                            }
                        },
                        { data: 'responsable', defaultContent: '-' },
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group" role="group">
                                        <a href="<?php echo BASE_URL; ?>/inventario/edit/${row.id_inventario}" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/inventario/reparar/${row.id_inventario}" class="btn btn-sm btn-outline-secondary" title="Registrar reparación">
                                            <i class="fas fa-wrench"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteInventario(${row.id_inventario})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    ],
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                    paging: false,
                    info: false,
                    responsive: true
                });
            }
            
            function showAlert(message, type) {
                const icon = type === 'success' ? 'success' : type === 'danger' ? 'error' : type === 'warning' ? 'warning' : 'info';
                const title = type === 'success' ? 'Éxito' : type === 'danger' ? 'Error' : type === 'warning' ? 'Aviso' : 'Información';
                Swal.fire({ icon: icon, title: title, text: message });
            }
            
            window.deleteInventario = function(id) {
                Swal.fire({
                    title: '¿Eliminar item?',
                    text: '¿Está seguro de que desea eliminar este item del inventario? Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `<?php echo BASE_URL; ?>/inventario/delete/${id}`,
                            method: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    showAlert('Item de inventario eliminado exitosamente', 'success');
                                    inventarioTable.ajax.reload();
                                } else {
                                    showAlert(response.message, 'danger');
                                }
                            },
                            error: function() {
                                showAlert('Error al eliminar item de inventario', 'danger');
                            }
                        });
                    }
                });
            };
            
            initTable();
        });
    </script>
</body>
</html>
