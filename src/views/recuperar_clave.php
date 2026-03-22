<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar clave - <?php echo Config::getAppName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: url('<?php echo BASE_URL; ?>/assets/images/fondo.jpeg') center center / cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 0;
        }
        body > .recovery-container { position: relative; z-index: 1; }
        .recovery-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 420px;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="text-center mb-4">
            <i class="fas fa-key fa-3x text-primary mb-3"></i>
            <h4>Recuperar contraseña</h4>
            <p class="text-muted small mb-0">Ingrese su usuario. Se generará una nueva contraseña y se enviará a su correo.</p>
        </div>

        <?php if (isset($success) && $success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
            </div>
            <div class="text-center mt-3">
                <a href="<?php echo BASE_URL; ?>/login" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Ir a iniciar sesión
                </a>
            </div>
        <?php else: ?>
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/recuperar-clave">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text"
                               class="form-control"
                               id="username"
                               name="username"
                               placeholder="Nombre de usuario"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               required
                               autocomplete="username">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-paper-plane me-2"></i>Enviar nueva contraseña al correo
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="<?php echo BASE_URL; ?>/login" class="text-decoration-none small">
                    <i class="fas fa-arrow-left me-1"></i>Volver al inicio de sesión
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php include __DIR__ . '/partials/uppercase-forms.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
