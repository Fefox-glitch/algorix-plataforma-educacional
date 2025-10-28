<?php

namespace App\Controllers;

class TeacherController
{
    private function readJson($path)
    {
        if (!file_exists($path)) {
            return [];
        }
        $fp = @fopen($path, 'rb');
        if (!$fp) {
            return [];
        }
        $raw = '';
        if (flock($fp, LOCK_SH)) {
            $raw = stream_get_contents($fp) ?: '';
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        if ($raw === '') {
            return [];
        }
        $j = json_decode($raw, true);
        return is_array($j) ? $j : [];
    }

    private function writeJson($path, $data)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $tmp = $path . '.tmp';
        $fp = @fopen($tmp, 'wb');
        if (!$fp) {
            return;
        }
        $payload = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, $payload);
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        @rename($tmp, $path);
    }

    private function supabaseAvailable()
    {
        return !empty(\SUPABASE_URL) && !empty(\SUPABASE_KEY);
    }

    public function listStudents()
    {
        header('Content-Type: application/json');
        $role = strtolower($_GET['role'] ?? 'student');
        $q = trim($_GET['q'] ?? '');
        $groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : null;
        $estado = strtolower($_GET['estado'] ?? '');
        $limit = isset($_GET['limit']) ? max(1, min(200, (int)$_GET['limit'])) : 100;

        if ($this->supabaseAvailable()) {
            $base = 'users?select=id,name,email,role';
            $filters = [];
            if ($role && $role !== 'all') {
                $filters['role'] = 'eq.' . $role;
            }
            if ($q !== '') {
                $filters['or'] = sprintf('(name.ilike.*%s*,email.ilike.*%s*)', urlencode($q), urlencode($q));
            }

            // Filtro por grupo/estado usando group_members
            if (($groupId && $groupId > 0) || in_array($estado, ['with_group','without_group'], true)) {
                $gmEndpoint = 'group_members?select=student_id';
                if ($groupId && $groupId > 0) {
                    $gmEndpoint .= '&group_id=eq.' . $groupId;
                }
                $gmRes = supabaseRequest('GET', $gmEndpoint);
                $ids = [];
                if ($gmRes['code'] >= 200 && is_array($gmRes['data'])) {
                    foreach ($gmRes['data'] as $row) {
                        if (!empty($row['student_id'])) {
                            $ids[$row['student_id']] = true;
                        }
                    }
                }
                $idList = implode(',', array_keys($ids));
                if ($idList !== '') {
                    if ($estado === 'without_group') {
                        $filters['id'] = 'not.in.(' . $idList . ')';
                    } else {
                        $filters['id'] = 'in.(' . $idList . ')';
                    }
                    if (!$role || $role === 'all') {
                        $filters['role'] = 'eq.student';
                    }
                } else {
                    if ($estado === 'with_group') {
                        echo json_encode(['data' => []]);
                        return;
                    }
                    if (!$role || $role === 'all') {
                        $filters['role'] = 'eq.student';
                    }
                }
            }

            $filters['limit'] = $limit;
            $res = supabaseRequest('GET', $base, null, $filters);
            $data = (is_array($res['data']) ? $res['data'] : []);
            echo json_encode(['data' => $data]);
            return;
        }

        // Fallback local
        $users = $this->readJson(__DIR__ . '/../../storage/users.json');
        $list = array_values(array_filter($users, function ($u) use ($role, $q) {
            $r = strtolower($u['role'] ?? '');
            if ($role && $role !== 'all' && $r !== $role) {
                return false;
            }
            if ($q !== '' && stripos(($u['name'] ?? '') . ' ' . ($u['email'] ?? ''), $q) === false) {
                return false;
            }
            return true;
        }));
        echo json_encode(['data' => $list]);
    }

    public function analytics()
    {
        header('Content-Type: application/json');
        $students = 0;
        $teachers = 0;
        $sessions = 0;
        $exercises = 0;

        if ($this->supabaseAvailable()) {
            $resUsers = supabaseRequest('GET', 'users?select=id,role');
            if ($resUsers['code'] >= 200 && is_array($resUsers['data'])) {
                foreach ($resUsers['data'] as $u) {
                    $r = $u['role'] ?? '';
                    if ($r === 'student') {
                        $students++;
                    } elseif ($r === 'teacher') {
                        $teachers++;
                    }
                }
            }
            $resSess = supabaseRequest('GET', 'lab_sessions?select=id&limit=100');
            if ($resSess['code'] >= 200 && is_array($resSess['data'])) {
                $sessions = count($resSess['data']);
            }
        } else {
            $users = $this->readJson(__DIR__ . '/../../storage/users.json');
            foreach ($users as $u) {
                $r = $u['role'] ?? '';
                if ($r === 'student') {
                    $students++;
                } elseif ($r === 'teacher') {
                    $teachers++;
                }
            }
        }
        $startCodes = $this->readJson(__DIR__ . '/../../storage/start_codes.json');
        $exercises = is_array($startCodes) ? count($startCodes) : 0;

        echo json_encode(['data' => [
            'students_count' => $students,
            'teachers_count' => $teachers,
            'sessions_count' => $sessions,
            'exercises_count' => $exercises,
        ]]);
    }

    public function listCommunications()
    {
        header('Content-Type: application/json');
        $msgs = $this->readJson(__DIR__ . '/../../storage/communications.json');
        echo json_encode(['data' => $msgs]);
    }

    public function addCommunication()
    {
        header('Content-Type: application/json');
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $msg = trim($body['message'] ?? '');
        if ($msg === '') {
            http_response_code(400);
            echo json_encode(['error' => 'empty_message']);
            return;
        }
        $list = $this->readJson(__DIR__ . '/../../storage/communications.json');
        $item = [
            'id' => uniqid('msg_', true),
            'author_id' => $_SESSION['user']['id'] ?? null,
            'author_role' => $_SESSION['user']['role'] ?? null,
            'message' => $msg,
            'created_at' => date('c')
        ];
        $list[] = $item;
        $this->writeJson(__DIR__ . '/../../storage/communications.json', $list);
        echo json_encode(['data' => $item]);
    }

    // Nuevo: listado de grupos del profesor
    public function listGroups()
    {
        header('Content-Type: application/json');
        if ($this->supabaseAvailable()) {
            $teacherId = $_SESSION['user']['id'] ?? null;
            $endpoint = 'student_groups?select=id,name';
            if ($teacherId) {
                $endpoint .= '&teacher_id=eq.' . urlencode($teacherId);
            }
            $res = supabaseRequest('GET', $endpoint);
            $data = is_array($res['data']) ? $res['data'] : [];
            echo json_encode(['data' => $data]);
            return;
        }
        echo json_encode(['data' => []]);
    }

    // Nuevo: listado de laboratorios (visible para profesor)
    public function listLabs()
    {
        header('Content-Type: application/json');
        $endpoint = 'computer_labs?select=id,name,location,capacity,is_active&deleted_at=is.null';
        $res = supabaseRequest('GET', $endpoint);
        $code = (int)$res['code'];
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        $error = $res['error'] ?? null;
        if ($code >= 400) {
            echo json_encode(['error' => $error ?: 'upstream_error']);
            return;
        }
        echo json_encode(['data' => $data]);
    }

    // Nuevo: historial de sesiones del profesor
    public function listSessions()
    {
        header('Content-Type: application/json');
        $teacherId = $_SESSION['user']['id'] ?? null;
        $endpoint = 'lab_sessions?order=started_at.desc&limit=100';
        if ($teacherId) {
            $endpoint .= '&teacher_id=eq.' . urlencode($teacherId);
        }
        $res = supabaseRequest('GET', $endpoint);
        $code = (int)$res['code'];
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        $error = $res['error'] ?? null;
        if ($code >= 400) {
            echo json_encode(['error' => $error ?: 'upstream_error']);
            return;
        }
        echo json_encode(['data' => $data]);
    }

    public function listComputers()
    {
        header('Content-Type: application/json');
        $labId = isset($_GET['lab_id']) ? (int)$_GET['lab_id'] : 0;
        $endpoint = 'computers?select=id,name,lab_id,ip_address,mac_address,status,last_seen,last_boot' .
            '&deleted_at=is.null';
        if ($labId > 0) {
            $endpoint .= '&lab_id=eq.' . $labId;
        }
        $res = supabaseRequest('GET', $endpoint);
        $code = (int)$res['code'];
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        $error = $res['error'] ?? null;
        if ($code >= 400) {
            echo json_encode(['error' => $error ?: 'upstream_error']);
            return;
        }
        echo json_encode(['data' => $data]);
    }

    public function listAssignments()
    {
        header('Content-Type: application/json');
        $groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
        $endpoint = 'computer_assignments?order=assigned_at.desc&limit=200';
        if ($groupId > 0) {
            $endpoint .= '&group_id=eq.' . $groupId;
        }
        $res = supabaseRequest('GET', $endpoint);
        $code = (int)$res['code'];
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        $error = $res['error'] ?? null;
        if ($code >= 400) {
            echo json_encode(['error' => $error ?: 'upstream_error']);
            return;
        }
        echo json_encode(['data' => $data]);
    }

    public function performComputerActions()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'method_not_allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $action = isset($input['action_type']) ? trim((string)$input['action_type']) : '';
        $ids = (
            isset($input['computer_ids']) &&
            is_array($input['computer_ids'])
        )
            ? array_map('intval', $input['computer_ids'])
            : [];
        if (!$action || empty($ids)) {
            http_response_code(400);
            echo json_encode(['error' => 'action_type y computer_ids requeridos']);
            return;
        }
        $allowed = ['power_on','power_off','restart','lock','unlock'];
        if (!in_array($action, $allowed, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'action_type inválido']);
            return;
        }
        $created = 0;
        $errors = [];
        foreach ($ids as $cid) {
            if ($cid <= 0) {
                continue;
            }
            $payload = [
                'computer_id' => $cid,
                'action_type' => $action,
                'performed_by' => $_SESSION['user']['id'] ?? null,
                'status' => 'pending',
                'result' => ''
            ];
            $res = supabaseRequest('POST', 'computer_actions', $payload);
            if ((int)$res['code'] === 201) {
                $created++;
            } else {
                $errors[] = $res['error'] ?? 'error';
            }
        }
        if (!empty($errors) && $created === 0) {
            http_response_code(500);
            echo json_encode(['error' => 'no_actions_created', 'details' => $errors]);
            return;
        }
        http_response_code(201);
        echo json_encode(['ok' => true, 'created' => $created]);
    }

    // ===== Sistema de Notas =====
    public function listTeacherCourses()
    {
        header('Content-Type: application/json');
        // Fallback offline: generar cursos demo si no hay Supabase
        if (!$this->supabaseAvailable()) {
            $teacherId = $_SESSION['user']['id'] ?? null;
            $path = __DIR__ . '/../../storage/courses.json';
            $courses = $this->readJson($path);
            if (!is_array($courses) || count($courses) === 0) {
                // Crear 2 cursos de ejemplo ligados al profesor actual
                $courses = [
                    ['id' => 1, 'title' => 'Curso Demo', 'teacher_id' => $teacherId],
                    ['id' => 2, 'title' => 'Curso Prácticas', 'teacher_id' => $teacherId]
                ];
                $this->writeJson($path, $courses);
            }
            $filtered = array_values(array_filter($courses, function ($c) use ($teacherId) {
                return !$teacherId || (($c['teacher_id'] ?? null) === $teacherId);
            }));
            // Responder sólo con id y title como espera el frontend
            $data = array_map(function ($c) {
                return ['id' => $c['id'], 'title' => $c['title']];
            }, $filtered);
            echo json_encode(['data' => $data]);
            return;
        }
        // Camino normal con Supabase
        $teacherId = $_SESSION['user']['id'] ?? null;
        $endpoint = 'courses?select=id,title';
        if ($teacherId) {
            $endpoint .= '&teacher_id=eq.' . urlencode($teacherId);
        }
        $res = supabaseRequest('GET', $endpoint);
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        echo json_encode(['data' => $data]);
    }

    public function listExercises()
    {
        header('Content-Type: application/json');
        $courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
        if ($courseId <= 0) {
            echo json_encode(['data' => []]);
            return;
        }
        // Fallback offline: ejercicios simples
        if (!$this->supabaseAvailable()) {
            $modsPath = __DIR__ . '/../../storage/modules.json';
            $exPath = __DIR__ . '/../../storage/exercises.json';
            $mods = $this->readJson($modsPath);
            $exercises = $this->readJson($exPath);
            if (!is_array($mods) || count($mods) === 0) {
                $mods = [ ['id' => 101, 'title' => 'Introducción', 'course_id' => 1] ];
                $this->writeJson($modsPath, $mods);
            }
            if (!is_array($exercises) || count($exercises) === 0) {
                $exercises = [
                    ['id' => 1001, 'title' => 'Variables básicas', 'module_id' => 101],
                    ['id' => 1002, 'title' => 'Condicionales', 'module_id' => 101]
                ];
                $this->writeJson($exPath, $exercises);
            }
            $modIds = array_column(array_values(array_filter($mods, function ($m) use ($courseId) {
                return (int)($m['course_id'] ?? 0) === $courseId;
            })), 'id');
            if (empty($modIds)) {
                echo json_encode(['data' => []]);
                return;
            }
            $out = array_values(array_filter($exercises, function ($e) use ($modIds) {
                return in_array($e['module_id'] ?? 0, $modIds, true);
            }));
            echo json_encode(['data' => array_map(function ($e) {
                return ['id' => $e['id'],'title' => $e['title'],'module_id' => $e['module_id']];
            }, $out)]);
            return;
        }
        // Camino normal con Supabase
        // Obtener módulos del curso
        $modsRes = supabaseRequest('GET', 'modules?select=id,title&course_id=eq.' . $courseId);
        $mods = is_array($modsRes['data'] ?? null) ? $modsRes['data'] : [];
        $modIds = array_map(function ($m) {
            return (int)($m['id'] ?? 0);
        }, $mods);
        $modIds = array_values(array_filter($modIds, function ($v) {
            return $v > 0;
        }));
        if (empty($modIds)) {
            echo json_encode(['data' => []]);
            return;
        }
        $idList = implode(',', $modIds);
        $exRes = supabaseRequest('GET', 'exercises?select=id,title,module_id&module_id=in.(' . $idList . ')');
        $exercises = is_array($exRes['data'] ?? null) ? $exRes['data'] : [];
        echo json_encode(['data' => $exercises]);
    }

    public function listGrades()
    {
        header('Content-Type: application/json');
        $courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
        $exerciseId = isset($_GET['exercise_id']) ? (int)$_GET['exercise_id'] : 0;
        // Fallback offline: devolver arreglo vacío o leer storage si existe
        if (!$this->supabaseAvailable()) {
            $gradesPath = __DIR__ . '/../../storage/grades.json';
            $grades = $this->readJson($gradesPath);
            if (!is_array($grades)) {
                $grades = [];
            }
            if ($exerciseId > 0) {
                $data = array_values(array_filter($grades, function ($g) use ($exerciseId) {
                    return (int)($g['exercise_id'] ?? 0) === $exerciseId;
                }));
                echo json_encode(['data' => $data]);
                return;
            }
            if ($courseId > 0) {
                $data = array_values(array_filter($grades, function ($g) use ($courseId) {
                    return (int)($g['course_id'] ?? 0) === $courseId;
                }));
                echo json_encode(['data' => $data]);
                return;
            }
            echo json_encode(['data' => []]);
            return;
        }
        // Camino normal con Supabase
        if ($exerciseId > 0) {
            // Notas por ejercicio: usar submissions (score/grade)
            $endpoint = 'submissions?select=id,user_id,exercise_id,score,grade,updated_at' .
            '&exercise_id=eq.' . $exerciseId;
            $res = supabaseRequest('GET', $endpoint);
            $data = is_array($res['data'] ?? null) ? $res['data'] : [];
            echo json_encode(['data' => $data]);
            return;
        }
        if ($courseId > 0) {
            // Notas finales del curso (tabla grades)
            $endpoint = 'grades?select=id,user_id,course_id,module_id,final_grade,updated_at' .
            '&course_id=eq.' . $courseId;
            $res = supabaseRequest('GET', $endpoint);
            $data = is_array($res['data'] ?? null) ? $res['data'] : [];
            echo json_encode(['data' => $data]);
            return;
        }
        echo json_encode(['data' => []]);
    }

    // CRUD de notas (grades)
    public function createGrade()
    {
        header('Content-Type: application/json');
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = $payload['user_id'] ?? null;
        $courseId = $payload['course_id'] ?? null;
        $final = $payload['final_grade'] ?? null;
        $moduleId = $payload['module_id'] ?? null;
        $exerciseId = $payload['exercise_id'] ?? null; // opcional
        if (!$userId || !$courseId || ($final === null)) {
            echo json_encode(['error' => 'missing_fields']);
            return;
        }

        // Modo offline: escribir en storage/grades.json
        if (!$this->supabaseAvailable()) {
            $gradesPath = __DIR__ . '/../../storage/grades.json';
            $grades = $this->readJson($gradesPath);
            if (!is_array($grades)) {
                $grades = [];
            }
            $nextId = 1;
            foreach ($grades as $g) {
                $gid = (int)($g['id'] ?? 0);
                if ($gid >= $nextId) {
                    $nextId = $gid + 1;
                }
            }
            $item = [
                'id' => $nextId,
                'user_id' => (int)$userId,
                'course_id' => (int)$courseId,
                'final_grade' => (float)$final,
                'updated_at' => date('c')
            ];
            if ($moduleId !== null) {
                $item['module_id'] = (int)$moduleId;
            }
            if ($exerciseId !== null) {
                $item['exercise_id'] = (int)$exerciseId;
            }
            $grades[] = $item;
            $this->writeJson($gradesPath, $grades);
            http_response_code(201);
            echo json_encode(['data' => $item]);
            return;
        }

        // Camino normal con Supabase
        $data = [ 'user_id' => $userId, 'course_id' => $courseId, 'final_grade' => $final ];
        if ($moduleId !== null) {
            $data['module_id'] = $moduleId;
        }
        $res = supabaseRequest('POST', 'grades', $data);
        $code = (int)$res['code'];
        $dataOut = is_array($res['data'] ?? null) ? $res['data'] : null;
        $err = $res['error'] ?? null;
        if ($code >= 400) {
            http_response_code($code);
            echo json_encode(['error' => $err ?: 'upstream_error']);
            return;
        }
        echo json_encode(['data' => $dataOut]);
    }

    public function updateGrade()
    {
        header('Content-Type: application/json');
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($payload['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'id_required']);
            return;
        }

        // Modo offline
        if (!$this->supabaseAvailable()) {
            $gradesPath = __DIR__ . '/../../storage/grades.json';
            $grades = $this->readJson($gradesPath);
            $found = false;
            for ($i = 0; $i < count($grades); $i++) {
                if ((int)($grades[$i]['id'] ?? 0) === $id) {
                    foreach (['user_id','course_id','module_id','final_grade','exercise_id'] as $k) {
                        if (array_key_exists($k, $payload)) {
                            $grades[$i][$k] = $payload[$k];
                        }
                    }
                    $grades[$i]['updated_at'] = date('c');
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                http_response_code(404);
                echo json_encode(['error' => 'not_found']);
                return;
            }
            $this->writeJson($gradesPath, $grades);
            echo json_encode(['data' => $grades[array_search($id, array_column($grades, 'id'))]]);
            return;
        }

        // Camino normal con Supabase
        $data = [];
        foreach (['user_id','course_id','module_id','final_grade'] as $k) {
            if (array_key_exists($k, $payload)) {
                $data[$k] = $payload[$k];
            }
        }
        if (empty($data)) {
            echo json_encode(['error' => 'no_changes']);
            return;
        }
        $endpoint = 'grades?id=eq.' . $id;
        $res = supabaseRequest('PATCH', $endpoint, $data);
        $code = (int)$res['code'];
        $dataOut = is_array($res['data'] ?? null) ? $res['data'] : null;
        $err = $res['error'] ?? null;
        if ($code >= 400) {
            http_response_code($code);
            echo json_encode(['error' => $err ?: 'upstream_error']);
            return;
        }
        echo json_encode(['data' => $dataOut]);
    }

    public function deleteGrade()
    {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'id_required']);
            return;
        }

        // Modo offline
        if (!$this->supabaseAvailable()) {
            $gradesPath = __DIR__ . '/../../storage/grades.json';
            $grades = $this->readJson($gradesPath);
            $before = count($grades);
            $grades = array_values(array_filter($grades, function ($g) use ($id) {
                return (int)($g['id'] ?? 0) !== $id;
            }));
            if (count($grades) === $before) {
                http_response_code(404);
                echo json_encode(['error' => 'not_found']);
                return;
            }
            $this->writeJson($gradesPath, $grades);
            echo json_encode(['data' => ['deleted' => true]]);
            return;
        }

        // Camino normal con Supabase
        $endpoint = 'grades?id=eq.' . $id;
        $res = supabaseRequest('DELETE', $endpoint);
        $code = (int)$res['code'];
        $err = $res['error'] ?? null;
        if ($code >= 400) {
            http_response_code($code);
            echo json_encode(['error' => $err ?: 'upstream_error']);
            return;
        }
        echo json_encode(['data' => ['deleted' => true]]);
    }
}
