<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="<?php echo base_url('styles/main.css'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>
<body>
    <header class="bg-primary text-white p-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Algorix</h1>
                <nav>
                    <ul class="nav">
                        <li class="nav-item"><a href="<?php echo base_url(); ?>" class="nav-link text-white">Inicio</a></li>
                        <?php if (isAuthenticated()): ?>
                            <li class="nav-item"><a href="<?php echo base_url('auth/logout'); ?>" class="nav-link text-white">Cerrar sesión</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a href="<?php echo base_url('auth/login'); ?>" class="nav-link text-white">Iniciar sesión</a></li>
                            <li class="nav-item"><a href="<?php echo base_url('auth/register'); ?>" class="nav-link text-white">Registrarse</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container my-4">
        <?php echo $content; ?>
    </main>

    <footer class="bg-dark text-white p-3 mt-5">
        <div class="container">
            <p class="text-center mb-0">Algorix &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>