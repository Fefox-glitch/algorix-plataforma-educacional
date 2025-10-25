<?php
namespace App\Controllers;

class AdminController {
    private function json($status, $data = null, $error = null) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode(['data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    }

    private function readJson($path) {
        if (file_exists($path)) {
            $raw = file_get_contents($path);
            $data = json_decode($raw, true);
            return is_array($data) ? $data : [];
        }
        return [];
    }

    // Fallback seguro cuando mbstring no está disponible
    private function strlen_utf8($s) {
        return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
    }

    // GET /api/admin/labs
    public function listLabs() {
        $offline = false; // offline eliminado
        $res = supabaseRequest('GET', 'computer_labs?select=id,name,location,capacity,is_active,created_by,updated_by,created_at,updated_at&deleted_at=is.null');
        $code = (int)$res['code'];
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        $error = $res['error'] ?? null;
        if ($code >= 400) { return $this->json($code, null, $error ?: 'upstream_error'); }
        return $this->json(200, $data);
    }

    // POST /api/admin/labs
    public function createLab() {
        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        // Validación estricta
        $name = isset($payload['name']) ? trim((string)$payload['name']) : '';
        $location = isset($payload['location']) ? trim((string)$payload['location']) : '';
        $capacity = isset($payload['capacity']) ? (int)$payload['capacity'] : 0;
        $isActiveRaw = $payload['is_active'] ?? false;
        $isActive = ($isActiveRaw === true || $isActiveRaw === 'true' || $isActiveRaw === 1 || $isActiveRaw === '1');
        if ($name === '' || $this->strlen_utf8($name) > 100) { return $this->json(400, null, 'name inválido'); }
        if ($location !== '' && $this->strlen_utf8($location) > 150) { return $this->json(400, null, 'location inválido'); }
        if ($capacity < 0 || $capacity > 10000) { return $this->json(400, null, 'capacity inválido'); }
        $createdBy = (isset($_SESSION['user']['id']) && is_string($_SESSION['user']['id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        $clean = [
            'name' => $name,
            'location' => ($location !== '' ? $location : null),
            'capacity' => $capacity,
            'is_active' => $isActive,
            'created_by' => $createdBy
        ];
        $offline = false; // offline eliminado
        $res = supabaseRequest('POST', 'computer_labs', $clean);
        return $this->json($res['code'], $res['data'], $res['error']);
    }

    // PATCH /api/admin/labs/update
    public function updateLab() {
        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = isset($payload['id']) ? (int)$payload['id'] : 0;
        if ($id <= 0) { return $this->json(400, null, 'id inválido'); }
        $fields = [];
        if (isset($payload['name'])) {
            $name = trim((string)$payload['name']);
            if ($name === '' || $this->strlen_utf8($name) > 100) { return $this->json(400, null, 'name inválido'); }
            $fields['name'] = $name;
        }
        if (array_key_exists('location', $payload)) {
            $location = trim((string)$payload['location']);
            if ($location !== '' && $this->strlen_utf8($location) > 150) { return $this->json(400, null, 'location inválido'); }
            $fields['location'] = ($location !== '' ? $location : null);
        }
        if (isset($payload['capacity'])) {
            $capacity = (int)$payload['capacity'];
            if ($capacity < 0 || $capacity > 10000) { return $this->json(400, null, 'capacity inválido'); }
            $fields['capacity'] = $capacity;
        }
        if (isset($payload['is_active'])) {
            $isActiveRaw = $payload['is_active'];
            $isActive = ($isActiveRaw === true || $isActiveRaw === 'true' || $isActiveRaw === 1 || $isActiveRaw === '1');
            $fields['is_active'] = $isActive;
        }
        $updatedBy = (isset($_SESSION['user']['id']) && is_string($_SESSION['user']['id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        $fields['updated_by'] = $updatedBy;
        $res = supabaseRequest('PATCH', 'computer_labs?id=eq.' . $id, $fields);
        return $this->json($res['code'], $res['data'], $res['error']);
    }

    // DELETE /api/admin/labs/delete
    public function deleteLab() {
        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = isset($payload['id']) ? (int)$payload['id'] : 0;
        if ($id <= 0) { return $this->json(400, null, 'id inválido'); }
        $deletedBy = (isset($_SESSION['user']['id']) && is_string($_SESSION['user']['id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        $fields = [ 'deleted_at' => date('c'), 'deleted_by' => $deletedBy ];
        $res = supabaseRequest('PATCH', 'computer_labs?id=eq.' . $id, $fields);
        return $this->json($res['code'], $res['data'] ?? null, $res['error'] ?? null);
    }

    // PATCH /api/admin/labs/restore
    public function restoreLab() {
        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = isset($payload['id']) ? (int)$payload['id'] : 0;
        if ($id <= 0) { return $this->json(400, null, 'id inválido'); }
        $updatedBy = (isset($_SESSION['user']['id']) && is_string($_SESSION['user']['id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        $fields = [ 'deleted_at' => null, 'deleted_by' => null, 'updated_by' => $updatedBy ];
        $res = supabaseRequest('PATCH', 'computer_labs?id=eq.' . $id, $fields);
        return $this->json($res['code'], $res['data'] ?? null, $res['error'] ?? null);
    }

    // GET /api/admin/computers
    public function listComputers() {
        $status = $_GET['status'] ?? null;
        $offline = false; // offline eliminado
        $endpoint = 'computers?select=id,name,lab_id,ip_address,mac_address,status,last_seen,last_boot,created_by,updated_by,updated_at&deleted_at=is.null';
        if ($status && strtolower($status) !== 'all') {
            $endpoint .= '&status=eq.' . urlencode($status);
        }
        $res = supabaseRequest('GET', $endpoint);
        $code = (int)$res['code'];
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        $error = $res['error'] ?? null;
        if ($code >= 400) { return $this->json($code, null, $error ?: 'upstream_error'); }
        return $this->json(200, $data);
    }

    // POST /api/admin/computers
    public function createComputer() {
        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        // Validación estricta
        $name = isset($payload['name']) ? trim((string)$payload['name']) : '';
        $labId = isset($payload['lab_id']) ? (int)$payload['lab_id'] : 0;
        $ip = isset($payload['ip_address']) ? trim((string)$payload['ip_address']) : '';
        $mac = isset($payload['mac_address']) ? trim((string)$payload['mac_address']) : '';
        $status = isset($payload['status']) ? strtolower(trim((string)$payload['status'])) : 'offline';
        $allowedStatus = ['online','offline','maintenance'];
        if ($name === '' || $this->strlen_utf8($name) > 100) { return $this->json(400, null, 'name inválido'); }
        if ($labId <= 0) { return $this->json(400, null, 'lab_id inválido'); }
        if ($ip !== '' && !filter_var($ip, FILTER_VALIDATE_IP)) { return $this->json(400, null, 'ip_address inválido'); }
        if ($mac !== '' && !preg_match('/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/', $mac)) { return $this->json(400, null, 'mac_address inválido'); }
        if (!in_array($status, $allowedStatus, true)) { return $this->json(400, null, 'status inválido'); }
        $createdBy = (isset($_SESSION['user']['id']) && is_string($_SESSION['user']['id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        $clean = [
            'name' => $name,
            'lab_id' => $labId,
            'ip_address' => ($ip !== '' ? $ip : null),
            'mac_address' => ($mac !== '' ? $mac : null),
            'status' => $status,
            'created_by' => $createdBy
        ];
        $offline = false; // offline eliminado
        $res = supabaseRequest('POST', 'computers', $clean);
        return $this->json($res['code'], $res['data'], $res['error']);
    }

    // PATCH /api/admin/computers/update
    public function updateComputer() {
        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = isset($payload['id']) ? (int)$payload['id'] : 0;
        if ($id <= 0) { return $this->json(400, null, 'id inválido'); }
        $fields = [];
        if (isset($payload['name'])) {
            $name = trim((string)$payload['name']);
            if ($name === '' || $this->strlen_utf8($name) > 100) { return $this->json(400, null, 'name inválido'); }
            $fields['name'] = $name;
        }
        if (isset($payload['lab_id'])) {
            $labId = (int)$payload['lab_id'];
            if ($labId <= 0) { return $this->json(400, null, 'lab_id inválido'); }
            $fields['lab_id'] = $labId;
        }
        if (array_key_exists('ip_address', $payload)) {
            $ip = trim((string)$payload['ip_address']);
            if ($ip !== '' && !filter_var($ip, FILTER_VALIDATE_IP)) { return $this->json(400, null, 'ip_address inválido'); }
            $fields['ip_address'] = ($ip !== '' ? $ip : null);
        }
        if (array_key_exists('mac_address', $payload)) {
            $mac = trim((string)$payload['mac_address']);
            if ($mac !== '' && !preg_match('/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/', $mac)) { return $this->json(400, null, 'mac_address inválido'); }
            $fields['mac_address'] = ($mac !== '' ? $mac : null);
        }
        if (isset($payload['status'])) {
            $status = strtolower(trim((string)$payload['status']));
            $allowedStatus = ['online','offline','maintenance'];
            if (!in_array($status, $allowedStatus, true)) { return $this->json(400, null, 'status inválido'); }
            $fields['status'] = $status;
        }
        if (array_key_exists('last_boot', $payload)) {
            $fields['last_boot'] = $payload['last_boot'] !== null ? (string)$payload['last_boot'] : null;
        }
        if (array_key_exists('last_seen', $payload)) {
            $fields['last_seen'] = $payload['last_seen'] !== null ? (string)$payload['last_seen'] : null;
        }
        $updatedBy = (isset($_SESSION['user']['id']) && is_string($_SESSION['user']['id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        $fields['updated_by'] = $updatedBy;
        $res = supabaseRequest('PATCH', 'computers?id=eq.' . $id, $fields);
        return $this->json($res['code'], $res['data'], $res['error']);
    }

    // DELETE /api/admin/computers/delete
    public function deleteComputer() {
        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = isset($payload['id']) ? (int)$payload['id'] : 0;
        if ($id <= 0) { return $this->json(400, null, 'id inválido'); }
        $deletedBy = (isset($_SESSION['user']['id']) && is_string($_SESSION['user']['id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        $fields = [ 'deleted_at' => date('c'), 'deleted_by' => $deletedBy ];
        $res = supabaseRequest('PATCH', 'computers?id=eq.' . $id, $fields);
        return $this->json($res['code'], $res['data'] ?? null, $res['error'] ?? null);
    }

    // PATCH /api/admin/computers/restore
    public function restoreComputer() {
        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = isset($payload['id']) ? (int)$payload['id'] : 0;
        if ($id <= 0) { return $this->json(400, null, 'id inválido'); }
        $updatedBy = (isset($_SESSION['user']['id']) && is_string($_SESSION['user']['id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        $fields = [ 'deleted_at' => null, 'deleted_by' => null, 'updated_by' => $updatedBy ];
        $res = supabaseRequest('PATCH', 'computers?id=eq.' . $id, $fields);
        return $this->json($res['code'], $res['data'] ?? null, $res['error'] ?? null);
    }

    // GET /api/admin/groups
    public function listGroups() {
        $offline = false; // offline eliminado
        $res = supabaseRequest('GET', 'student_groups?select=id,name,teacher_id,lab_id,is_active,created_by,updated_by,created_at,updated_at&deleted_at=is.null');
        $code = (int)$res['code'];
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        $error = $res['error'] ?? null;
        if ($code >= 400) { return $this->json($code, null, $error ?: 'upstream_error'); }
        return $this->json(200, $data);
    }

    // POST /api/admin/groups
    public function createGroup() {
        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        // Validación estricta
        $name = isset($payload['name']) ? trim((string)$payload['name']) : '';
        $teacherIdRaw = isset($payload['teacher_id']) ? trim((string)$payload['teacher_id']) : '';
        $labId = isset($payload['lab_id']) ? (int)$payload['lab_id'] : 0;
        $isActiveRaw = $payload['is_active'] ?? true;
        $isActive = ($isActiveRaw === true || $isActiveRaw === 'true' || $isActiveRaw === 1 || $isActiveRaw === '1');
        if ($name === '' || $this->strlen_utf8($name) > 100) { return $this->json(400, null, 'name inválido'); }
        if ($teacherIdRaw === '' || !preg_match('/^[0-9a-fA-F-]{36}$/', $teacherIdRaw)) { return $this->json(400, null, 'teacher_id inválido (UUID requerido)'); }
        if ($labId <= 0) { return $this->json(400, null, 'lab_id inválido'); }
        $createdBy = (isset($_SESSION['user']['id']) && is_string($_SESSION['user']['id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        $clean = [
            'name' => $name,
            'teacher_id' => $teacherIdRaw,
            'lab_id' => $labId,
            'is_active' => $isActive,
            'created_by' => $createdBy
        ];
        $offline = false; // offline eliminado
        $res = supabaseRequest('POST', 'student_groups', $clean);
        return $this->json($res['code'], $res['data'], $res['error']);
    }

    // PATCH /api/admin/groups/update
    public function updateGroup() {
        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = isset($payload['id']) ? (int)$payload['id'] : 0;
        if ($id <= 0) { return $this->json(400, null, 'id inválido'); }
        $fields = [];
        if (isset($payload['name'])) {
            $name = trim((string)$payload['name']);
            if ($name === '' || $this->strlen_utf8($name) > 100) { return $this->json(400, null, 'name inválido'); }
            $fields['name'] = $name;
        }
        if (isset($payload['teacher_id'])) {
            $teacherIdRaw = trim((string)$payload['teacher_id']);
            if ($teacherIdRaw === '' || !preg_match('/^[0-9a-fA-F-]{36}$/', $teacherIdRaw)) { return $this->json(400, null, 'teacher_id inválido (UUID requerido)'); }
            $fields['teacher_id'] = $teacherIdRaw;
        }
        if (isset($payload['lab_id'])) {
            $labId = (int)$payload['lab_id'];
            if ($labId <= 0) { return $this->json(400, null, 'lab_id inválido'); }
            $fields['lab_id'] = $labId;
        }
        if (isset($payload['is_active'])) {
            $isActiveRaw = $payload['is_active'];
            $isActive = ($isActiveRaw === true || $isActiveRaw === 'true' || $isActiveRaw === 1 || $isActiveRaw === '1');
            $fields['is_active'] = $isActive;
        }
        $updatedBy = (isset($_SESSION['user']['id']) && is_string($_SESSION['user']['id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        $fields['updated_by'] = $updatedBy;
        $res = supabaseRequest('PATCH', 'student_groups?id=eq.' . $id, $fields);
        return $this->json($res['code'], $res['data'], $res['error']);
    }

    // DELETE /api/admin/groups/delete
    public function deleteGroup() {
         $payload = json_decode(file_get_contents('php://input'), true) ?: [];
         $id = isset($payload['id']) ? (int)$payload['id'] : 0;
         if ($id <= 0) { return $this->json(400, null, 'id inválido'); }
         $deletedBy = (isset($_SESSION['user']['id']) && is_string($_SESSION['user']['id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
         $fields = [ 'deleted_at' => date('c'), 'deleted_by' => $deletedBy ];
         $res = supabaseRequest('PATCH', 'student_groups?id=eq.' . $id, $fields);
         return $this->json($res['code'], $res['data'] ?? null, $res['error'] ?? null);
     }

     // PATCH /api/admin/groups/restore
     public function restoreGroup() {
         $payload = json_decode(file_get_contents('php://input'), true) ?: [];
         $id = isset($payload['id']) ? (int)$payload['id'] : 0;
         if ($id <= 0) { return $this->json(400, null, 'id inválido'); }
         $updatedBy = (isset($_SESSION['user']['id']) && is_string($_SESSION['user']['id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
         $fields = [ 'deleted_at' => null, 'deleted_by' => null, 'updated_by' => $updatedBy ];
         $res = supabaseRequest('PATCH', 'student_groups?id=eq.' . $id, $fields);
         return $this->json($res['code'], $res['data'] ?? null, $res['error'] ?? null);
     }

    // GET /api/admin/teachers
    public function listTeachers() {
        $offline = false; // offline eliminado
        $res = supabaseRequest('GET', 'users?role=in.(teacher,admin)');
        $code = (int)$res['code'];
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        $error = $res['error'] ?? null;
        if ($code >= 400) { return $this->json($code, null, $error ?: 'upstream_error'); }
        return $this->json(200, $data);
    }

    // GET /api/admin/actions
    public function listActions() {
        $offline = false; // offline eliminado
        $res = supabaseRequest('GET', 'computer_actions?select=id,computer_id,action_type,performed_by,status,result,performed_at&order=performed_at.desc&limit=100');
        $code = (int)$res['code'];
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        $error = $res['error'] ?? null;
        if ($code >= 400) { return $this->json($code, null, $error ?: 'upstream_error'); }
        return $this->json(200, $data);
    }

    // POST /api/admin/actions/lab
    public function performLabAction() {
        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $labId = (int)($payload['lab_id'] ?? 0);
        $action = $payload['action_type'] ?? '';
        if (!$labId || !$action) {
            return $this->json(400, null, 'lab_id y action_type requeridos');
        }
        $offline = false; // offline eliminado
        // Obtener computadoras del laboratorio y crear acciones por cada una
        $res = supabaseRequest('GET', 'computers?lab_id=eq.' . $labId . '&select=id');
        if ((int)$res['code'] !== 200 || empty($res['data'])) {
            return $this->json((int)$res['code'], null, 'No se pudieron listar computadoras del laboratorio');
        }
        $created = [];
        foreach ($res['data'] as $c) {
            $item = [
                'computer_id' => $c['id'],
                'action_type' => $action,
                'performed_by' => $_SESSION['user']['id'] ?? null,
                'status' => 'pending',
                'result' => ''
            ];
            $ins = supabaseRequest('POST', 'computer_actions', $item);
            if ((int)$ins['code'] === 201) { $created[] = $ins['data'][0]; }
        }
        return $this->json(201, ['count' => count($created)]);
    }

    // GET /api/admin/sessions
    public function listSessions() {
        $offline = false; // offline eliminado
        $res = supabaseRequest('GET', 'lab_sessions?order=started_at.desc&limit=100');
        $code = (int)$res['code'];
        $data = is_array($res['data'] ?? null) ? $res['data'] : [];
        $error = $res['error'] ?? null;
        if ($code >= 400) { return $this->json($code, null, $error ?: 'upstream_error'); }
        return $this->json(200, $data);
    }

    // GET /api/admin/ai-config
    public function getAIConfig() {
        $isAdmin = isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'admin');
        if (!$isAdmin) { return $this->json(403, [], 'forbidden'); }
        $path = __DIR__ . '/../../storage/ai_config.json';
        $defaults = [
            'AI_ENDPOINT' => isset($_ENV['AI_ENDPOINT']) ? $_ENV['AI_ENDPOINT'] : 'https://oi-server.onrender.com/chat/completions',
            'AI_MODEL' => isset($_ENV['AI_MODEL']) ? $_ENV['AI_MODEL'] : 'openrouter/claude-sonnet-4',
            'AI_TOKEN' => isset($_ENV['AI_TOKEN']) ? $_ENV['AI_TOKEN'] : '',
            'AI_CUSTOMER_ID' => isset($_ENV['AI_CUSTOMER_ID']) ? $_ENV['AI_CUSTOMER_ID'] : '',
            'OFFLINE_MODE' => (isset($_ENV['OFFLINE_MODE']) && $_ENV['OFFLINE_MODE'] === 'true') ? 'true' : 'false'
        ];
        $cfg = $defaults;
        if (file_exists($path)) {
            $file = $this->readJson($path);
            if (is_array($file) && !empty($file)) { $cfg = array_merge($defaults, $file); }
        }
        return $this->json(200, $cfg);
    }

    // POST /api/admin/ai-config
    public function saveAIConfig() {
        $isAdmin = isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'admin');
        if (!$isAdmin) { return $this->json(403, [], 'forbidden'); }
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];
        $cfg = [
            'AI_ENDPOINT' => isset($data['AI_ENDPOINT']) ? trim($data['AI_ENDPOINT']) : '',
            'AI_MODEL' => isset($data['AI_MODEL']) ? trim($data['AI_MODEL']) : '',
            'AI_TOKEN' => isset($data['AI_TOKEN']) ? trim($data['AI_TOKEN']) : '',
            'AI_CUSTOMER_ID' => isset($data['AI_CUSTOMER_ID']) ? trim($data['AI_CUSTOMER_ID']) : '',
            'OFFLINE_MODE' => (isset($data['OFFLINE_MODE']) && ($data['OFFLINE_MODE'] === true || $data['OFFLINE_MODE'] === 'true')) ? 'true' : 'false'
        ];
        $dir = __DIR__ . '/../../storage';
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        $ok = @file_put_contents($dir . '/ai_config.json', json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if ($ok === false) { return $this->json(500, [], 'write_error'); }
        return $this->json(201, $cfg);
    }
}