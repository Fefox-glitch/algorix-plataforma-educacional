<?php

class AuthController {

    public function login() {
        // Login demo offline mediante query: /auth/login?demo=student
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['demo']) && $_GET['demo'] === 'student') {
            if (function_exists('isOfflineModeEnabled') && isOfflineModeEnabled()) {
                // Intentar obtener el usuario demo del almacenamiento offline
                $demo = null;
                if (function_exists('offlineSupabaseRequest')) {
                    $res = offlineSupabaseRequest('GET', 'users?email=eq.' . urlencode('student1@example.com'));
                    if ($res['code'] === 200 && !empty($res['data'])) {
                        $demo = $res['data'][0];
                    }
                }
                // Si no existe, crear uno al vuelo
                if (!$demo) {
                    $demoData = [
                        'name' => 'Alice Student',
                        'email' => 'student1@example.com',
                        'password_hash' => password_hash('Algorix123!', PASSWORD_DEFAULT),
                        'role' => 'student'
                    ];
                    if (function_exists('offlineSupabaseRequest')) {
                        $created = offlineSupabaseRequest('POST', 'users', $demoData);
                        if ($created['code'] === 201 && !empty($created['data'])) {
                            $demo = $created['data'][0];
                        } else {
                            $demo = $demoData;
                        }
                    } else {
                        $demo = $demoData;
                    }
                }

                // Guardar sesión y redirigir al dashboard de estudiante
                $_SESSION['user'] = [
                    'id' => $demo['id'] ?? 'offline-student',
                    'name' => $demo['name'],
                    'email' => $demo['email'],
                    'role' => 'student'
                ];
                redirect('student/dashboard');
                return;
            }
        }

        // Login demo offline para profesor: /auth/login?demo=teacher
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['demo']) && $_GET['demo'] === 'teacher') {
            if (function_exists('isOfflineModeEnabled') && isOfflineModeEnabled()) {
                // Intentar obtener el usuario demo del almacenamiento offline
                $demo = null;
                if (function_exists('offlineSupabaseRequest')) {
                    $res = offlineSupabaseRequest('GET', 'users?email=eq.' . urlencode('teacher1@example.com'));
                    if ($res['code'] === 200 && !empty($res['data'])) {
                        $demo = $res['data'][0];
                    }
                }
                // Si no existe, crear uno al vuelo
                if (!$demo) {
                    $demoData = [
                        'name' => 'Tom Teacher',
                        'email' => 'teacher1@example.com',
                        'password_hash' => password_hash('Algorix123!', PASSWORD_DEFAULT),
                        'role' => 'teacher'
                    ];
                    if (function_exists('offlineSupabaseRequest')) {
                        $created = offlineSupabaseRequest('POST', 'users', $demoData);
                        if ($created['code'] === 201 && !empty($created['data'])) {
                            $demo = $created['data'][0];
                        } else {
                            $demo = $demoData;
                        }
                    } else {
                        $demo = $demoData;
                    }
                }

                // Guardar sesión y redirigir al dashboard de profesor
                $_SESSION['user'] = [
                    'id' => $demo['id'] ?? 'offline-teacher',
                    'name' => $demo['name'],
                    'email' => $demo['email'],
                    'role' => 'teacher'
                ];
                redirect('teacher/dashboard');
                return;
            }
        }

        // Login demo offline para admin: /auth/login?demo=admin
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['demo']) && $_GET['demo'] === 'admin') {
            if (function_exists('isOfflineModeEnabled') && isOfflineModeEnabled()) {
                $demo = null;
                if (function_exists('offlineSupabaseRequest')) {
                    $res = offlineSupabaseRequest('GET', 'users?email=eq.' . urlencode('admin1@example.com'));
                    if ($res['code'] === 200 && !empty($res['data'])) {
                        $demo = $res['data'][0];
                    }
                }
                if (!$demo) {
                    $demoData = [
                        'name' => 'Ada Admin',
                        'email' => 'admin1@example.com',
                        'password_hash' => password_hash('Algorix123!', PASSWORD_DEFAULT),
                        'role' => 'admin'
                    ];
                    if (function_exists('offlineSupabaseRequest')) {
                        $created = offlineSupabaseRequest('POST', 'users', $demoData);
                        if ($created['code'] === 201 && !empty($created['data'])) {
                            $demo = $created['data'][0];
                        } else {
                            $demo = $demoData;
                        }
                    } else {
                        $demo = $demoData;
                    }
                }
                $_SESSION['user'] = [
                    'id' => $demo['id'] ?? 'offline-admin',
                    'name' => $demo['name'],
                    'email' => $demo['email'],
                    'role' => 'admin'
                ];
                redirect('admin/dashboard');
                return;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificación CSRF
            $csrf = $_POST['csrf_token'] ?? '';
            if (!(class_exists('App\\Core\\Security') && \App\Core\Security::verifyCsrfToken($csrf))) {
                $_SESSION['message'] = 'Sesión expirada o token inválido. Recarga la página.';
                $_SESSION['message_type'] = 'error';
                redirect('auth/login');
                return;
            }

            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['message'] = 'Por favor completa todos los campos';
                $_SESSION['message_type'] = 'error';
                redirect('auth/login');
                return;
            }

            // Buscar usuario en Supabase por email
            $response = supabaseRequest('GET', 'users?email=eq.' . urlencode($email));

            if ($response['code'] === 200 && !empty($response['data'])) {
                $user = $response['data'][0];

                // Verificar contraseña (debe estar hasheada en la BD)
                if (password_verify($password, $user['password_hash'] ?? '')) {
                    // Guardar sesión
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];

                    // Redirigir según el rol
                    switch ($user['role']) {
                        case 'student':
                            redirect('student/dashboard');
                            break;
                        case 'teacher':
                            redirect('teacher/dashboard');
                            break;
                        case 'admin':
                            redirect('admin/dashboard');
                            break;
                        default:
                            redirect('auth/login');
                    }
                    return;
                }
            }

            $_SESSION['message'] = 'Email o contraseña incorrectos';
            $_SESSION['message_type'] = 'error';
            redirect('auth/login');
        } else {
            // Mostrar formulario de login
            $pageTitle = 'Iniciar Sesión';
            $content = renderView('auth/login');
            include __DIR__ . '/../../views/shared/layout.php';
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificación CSRF
            $csrf = $_POST['csrf_token'] ?? '';
            if (!(class_exists('App\\Core\\Security') && \App\Core\Security::verifyCsrfToken($csrf))) {
                $_SESSION['message'] = 'Sesión expirada o token inválido. Recarga la página.';
                $_SESSION['message_type'] = 'error';
                redirect('auth/register');
                return;
            }

            $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'student';
            $accessCode = strtoupper($_POST['accessCode'] ?? '');

            // Validar datos
            if (empty($name) || empty($email) || empty($password)) {
                $_SESSION['message'] = 'Por favor completa todos los campos obligatorios';
                $_SESSION['message_type'] = 'error';
                redirect('auth/register');
                return;
            }

            // Validar código de acceso para roles especiales
            $validCodes = [
                'teacher' => 'TEACH2024A1',
                'admin' => 'ADMIN2024B1'
            ];

            if (($role === 'teacher' || $role === 'admin') &&
                (!isset($validCodes[$role]) || $accessCode !== $validCodes[$role])) {
                $_SESSION['message'] = 'Código de acceso incorrecto para ' . $role;
                $_SESSION['message_type'] = 'error';
                redirect('auth/register');
                return;
            }

            // Verificar si el email ya existe
            $checkResponse = supabaseRequest('GET', 'users?email=eq.' . urlencode($email));
            if ($checkResponse['code'] === 200 && !empty($checkResponse['data'])) {
                $_SESSION['message'] = 'Este email ya está registrado';
                $_SESSION['message_type'] = 'error';
                redirect('auth/register');
                return;
            }

            // Crear usuario en Supabase
            $userData = [
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role
            ];

            $response = supabaseRequest('POST', 'users', $userData);

            if ($response['code'] === 201 && !empty($response['data'])) {
                $user = $response['data'][0];

                // Iniciar sesión automáticamente
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];

                $_SESSION['message'] = '¡Cuenta creada exitosamente!';
                $_SESSION['message_type'] = 'success';

                // Redirigir según rol
                switch ($user['role']) {
                    case 'student':
                        redirect('student/dashboard');
                        break;
                    case 'teacher':
                        redirect('teacher/dashboard');
                        break;
                    case 'admin':
                        redirect('admin/dashboard');
                        break;
                }
            } else {
                $_SESSION['message'] = 'Error al crear la cuenta. Intenta de nuevo.';
                $_SESSION['message_type'] = 'error';
                redirect('auth/register');
            }
        } else {
            // Mostrar formulario de registro
            $pageTitle = 'Crear Cuenta';
            $content = renderView('auth/register');
            include __DIR__ . '/../../views/shared/layout.php';
        }
    }

    public function logout() {
        session_destroy();
        redirect('auth/login');
    }
}
