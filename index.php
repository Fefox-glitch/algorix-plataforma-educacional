<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/src/Utils/functions.php';
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/config.php';
// require_once __DIR__ . '/src/Controllers/TeacherController.php';

use App\Controllers\TeacherController;

function requireAuthRole($role) {
    if (!isset($_SESSION['user'])) { http_response_code(401); echo json_encode(['error' => 'unauthenticated']); exit; }
    $r = $_SESSION['user']['role'] ?? '';
    if ($r !== $role) { http_response_code(403); echo json_encode(['error' => 'forbidden']); exit; }
}

function checkCsrf() {
    // Moved from previous context: ensure CSRF token is validated for POST-like methods
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!class_exists('App\\Core\\Security')) {
            // Fallback simple check
            if (empty($token) || !isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
                http_response_code(419); echo json_encode(['error' => 'csrf_invalid']); exit;
            }
        } else {
            if (!App\Core\Security::verifyCsrfToken($token)) {
                http_response_code(419); echo json_encode(['error' => 'csrf_invalid']); exit;
            }
        }
    }
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Rutas Auth
if ($path === '/auth/login') {
    $auth = new \AuthController();
    $auth->login();
    exit;
}
if ($path === '/auth/register') {
    $auth = new \AuthController();
    $auth->register();
    exit;
}
if ($path === '/auth/logout') {
    $auth = new \AuthController();
    $auth->logout();
    exit;
}

// Rutas API Teacher
if (strpos($path, '/api/teacher/') === 0) {
    requireAuthRole('teacher');
    checkCsrf();
    $teacher = new TeacherController();
    if ($path === '/api/teacher/users') { $teacher->listStudents(); exit; }
    if ($path === '/api/teacher/analytics') { $teacher->analytics(); exit; }
    if ($path === '/api/teacher/communications' && $_SERVER['REQUEST_METHOD'] === 'GET') { $teacher->listCommunications(); exit; }
    if ($path === '/api/teacher/communications' && $_SERVER['REQUEST_METHOD'] === 'POST') { $teacher->addCommunication(); exit; }
    if ($path === '/api/teacher/groups') { $teacher->listGroups(); exit; }
    // Nuevos endpoints para profesor
    if ($path === '/api/teacher/labs' && $_SERVER['REQUEST_METHOD'] === 'GET') { $teacher->listLabs(); exit; }
    if ($path === '/api/teacher/sessions' && $_SERVER['REQUEST_METHOD'] === 'GET') { $teacher->listSessions(); exit; }
    if ($path === '/api/teacher/computers' && $_SERVER['REQUEST_METHOD'] === 'GET') { $teacher->listComputers(); exit; }
    if ($path === '/api/teacher/assignments' && $_SERVER['REQUEST_METHOD'] === 'GET') { $teacher->listAssignments(); exit; }
    if ($path === '/api/teacher/actions/computers' && $_SERVER['REQUEST_METHOD'] === 'POST') { $teacher->performComputerActions(); exit; }
    // Sistema de Notas
    if ($path === '/api/teacher/courses' && $_SERVER['REQUEST_METHOD'] === 'GET') { $teacher->listTeacherCourses(); exit; }
    if ($path === '/api/teacher/exercises' && $_SERVER['REQUEST_METHOD'] === 'GET') { $teacher->listExercises(); exit; }
    if ($path === '/api/teacher/grades' && $_SERVER['REQUEST_METHOD'] === 'GET') { $teacher->listGrades(); exit; }
    if ($path === '/api/teacher/grades' && $_SERVER['REQUEST_METHOD'] === 'POST') { $teacher->createGrade(); exit; }
    if ($path === '/api/teacher/grades' && $_SERVER['REQUEST_METHOD'] === 'PATCH') { $teacher->updateGrade(); exit; }
    if ($path === '/api/teacher/grades' && $_SERVER['REQUEST_METHOD'] === 'DELETE') { $teacher->deleteGrade(); exit; }
    http_response_code(404); echo json_encode(['error' => 'not_found']); exit;
}

// Rutas API Admin
if (strpos($path, '/api/admin/') === 0) {
    requireAuthRole('admin');
    checkCsrf();
    $admin = new \AdminController();
    // Labs
    if ($path === '/api/admin/labs' && $_SERVER['REQUEST_METHOD'] === 'GET') { $admin->listLabs(); exit; }
    if ($path === '/api/admin/labs' && $_SERVER['REQUEST_METHOD'] === 'POST') { $admin->createLab(); exit; }
    // NUEVO: Update/Delete/Restore Labs
    if ($path === '/api/admin/labs/update' && $_SERVER['REQUEST_METHOD'] === 'PATCH') { $admin->updateLab(); exit; }
    if ($path === '/api/admin/labs/delete' && $_SERVER['REQUEST_METHOD'] === 'PATCH') { $admin->deleteLab(); exit; }
    if ($path === '/api/admin/labs/restore' && $_SERVER['REQUEST_METHOD'] === 'PATCH') { $admin->restoreLab(); exit; }

    // Computers
    if ($path === '/api/admin/computers' && $_SERVER['REQUEST_METHOD'] === 'GET') { $admin->listComputers(); exit; }
    if ($path === '/api/admin/computers' && $_SERVER['REQUEST_METHOD'] === 'POST') { $admin->createComputer(); exit; }
    // NUEVO: Update/Delete/Restore Computers
    if ($path === '/api/admin/computers/update' && $_SERVER['REQUEST_METHOD'] === 'PATCH') { $admin->updateComputer(); exit; }
    if ($path === '/api/admin/computers/delete' && $_SERVER['REQUEST_METHOD'] === 'PATCH') { $admin->deleteComputer(); exit; }
    if ($path === '/api/admin/computers/restore' && $_SERVER['REQUEST_METHOD'] === 'PATCH') { $admin->restoreComputer(); exit; }

    // Groups
    if ($path === '/api/admin/groups' && $_SERVER['REQUEST_METHOD'] === 'GET') { $admin->listGroups(); exit; }
    if ($path === '/api/admin/groups' && $_SERVER['REQUEST_METHOD'] === 'POST') { $admin->createGroup(); exit; }
    // NUEVO: Update/Delete/Restore Groups
    if ($path === '/api/admin/groups/update' && $_SERVER['REQUEST_METHOD'] === 'PATCH') { $admin->updateGroup(); exit; }
    if ($path === '/api/admin/groups/delete' && $_SERVER['REQUEST_METHOD'] === 'PATCH') { $admin->deleteGroup(); exit; }
    if ($path === '/api/admin/groups/restore' && $_SERVER['REQUEST_METHOD'] === 'PATCH') { $admin->restoreGroup(); exit; }

    // Teachers
    if ($path === '/api/admin/teachers' && $_SERVER['REQUEST_METHOD'] === 'GET') { $admin->listTeachers(); exit; }

    // Actions
    if ($path === '/api/admin/actions' && $_SERVER['REQUEST_METHOD'] === 'GET') { $admin->listActions(); exit; }
    if ($path === '/api/admin/actions/lab' && $_SERVER['REQUEST_METHOD'] === 'POST') { $admin->performLabAction(); exit; }

    // Sessions
    if ($path === '/api/admin/sessions' && $_SERVER['REQUEST_METHOD'] === 'GET') { $admin->listSessions(); exit; }
    
    // AI Config
    if ($path === '/api/admin/ai-config' && $_SERVER['REQUEST_METHOD'] === 'GET') { $admin->getAIConfig(); exit; }
    if ($path === '/api/admin/ai-config' && $_SERVER['REQUEST_METHOD'] === 'POST') { $admin->saveAIConfig(); exit; }

    http_response_code(404); echo json_encode(['error' => 'not_found']); exit;
}

// Ruta raíz: mostrar Home con layout
if ($path === '/' || $path === '/index.php') {
    $pageTitle = 'Bienvenido a Algorix';
    $content = render_view('home');
    include __DIR__ . '/views/shared/layout.php';
    exit;
}

// Vistas con layout
if ($path === '/student/dashboard') {
    if (!isAuthenticated() || !hasRole('student')) { redirect('auth/login'); exit; }
    $pageTitle = 'Panel de Estudiante';
    $userRole = 'student';
    $user = (isset($_SESSION['user']) && is_array($_SESSION['user'])) ? $_SESSION['user'] : ['name' => 'Estudiante', 'role' => 'student'];
    $content = render_view('student/dashboard', ['user' => $user]);
    include __DIR__ . '/views/shared/layout.php';
    exit;
}

if ($path === '/teacher/dashboard') {
    if (!isAuthenticated() || !hasRole('teacher')) { redirect('auth/login'); exit; }
    $pageTitle = 'Panel de Profesor';
    $userRole = 'teacher';
    $name = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Profesor';
    $content = render_view('teacher/dashboard', ['name' => $name]);
    include __DIR__ . '/views/shared/layout.php';
    exit;
}

if ($path === '/admin/dashboard') {
    if (!isAuthenticated() || !hasRole('admin')) { redirect('auth/login'); exit; }
    $pageTitle = 'Panel de Administrador';
    $userRole = 'admin';
    $name = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Administrador';
    $content = render_view('admin/dashboard', ['name' => $name]);
    include __DIR__ . '/views/shared/layout.php';
    exit;
}

// Sirve estáticos de /styles directamente para evitar fallos del servidor embebido
if (strpos($path, '/styles/') === 0) {
    $cssPath = __DIR__ . $path;
    if (file_exists($cssPath)) {
        header('Content-Type: text/css; charset=utf-8');
        readfile($cssPath);
        exit;
    }
}

// Static assets (cuando se usa php -S con docroot)
$static = __DIR__ . $path;
if (file_exists($static)) { return false; }

http_response_code(404);
echo 'Not Found';