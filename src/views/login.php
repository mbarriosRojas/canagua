<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo Config::getAppName(); ?></title>
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
        body > .login-container {
            position: relative;
            z-index: 1;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo .logo-img {
            display: block;
            max-width: 160px;
            height: auto;
            margin: 0 auto 0.5rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .input-group-text {
            background: transparent;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        
        .form-control.with-icon {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.jpeg" alt="<?php echo htmlspecialchars(Config::getAppName()); ?>" class="logo-img">
            <h3 class="text-center mb-0 mt-2"><?php echo Config::getAppName(); ?></h3>
            <p class="text-muted text-center small mb-0">Sistema de Gestión Administrativa</p>
        </div>
        
        <form method="POST" action="<?php echo BASE_URL; ?>/login">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" 
                           class="form-control with-icon" 
                           name="username" 
                           placeholder="Usuario" 
                           required
                           autocomplete="username">
                </div>
            </div>
            
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control with-icon" 
                           name="password" 
                           placeholder="Contraseña" 
                           required
                           autocomplete="current-password">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-login w-100">
                <i class="fas fa-sign-in-alt me-2"></i>
                Iniciar Sesión
            </button>
        </form>
        
        <div class="text-center mt-3">
            <a href="<?php echo BASE_URL; ?>/recuperar-clave" class="text-decoration-none small">
                ¿Olvidó su contraseña? Recuperar clave perdida
            </a>
        </div>
        <div class="text-center mt-2">
            <small class="text-muted">
                <i class="fas fa-shield-alt me-1"></i>
                Acceso seguro y encriptado
            </small>
        </div>
    </div>
    
    <?php include __DIR__ . '/partials/uppercase-forms.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (isset($error)): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({ icon: 'error', title: 'Error', text: <?php echo json_encode($error); ?> }); });</script>
    <?php endif; ?>
</body>
</html>
