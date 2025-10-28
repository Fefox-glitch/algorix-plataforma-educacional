<?php

namespace App\Controllers;

class StartCodesController
{
    private $storagePath;

    public function __construct()
    {
        $this->storagePath = __DIR__ . '/../../storage/start_codes.json';
        if (!is_dir(dirname($this->storagePath))) {
            mkdir(dirname($this->storagePath), 0755, true);
        }
        if (!file_exists($this->storagePath)) {
            file_put_contents($this->storagePath, json_encode([]));
        }
    }

    private function loadAll()
    {
        try {
            $raw = file_get_contents($this->storagePath);
            $data = json_decode($raw, true);
            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            \App\Core\Logger::getInstance()->error('start_codes load error', ['msg' => $e->getMessage()]);
            return [];
        }
    }

    private function saveAll(array $items)
    {
        file_put_contents($this->storagePath, json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private function json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function requireAdmin()
    {
        if (!function_exists('hasRole') || !hasRole('admin')) {
            $this->json(['error' => 'forbidden'], 403);
            return false;
        }
        return true;
    }

    // POST /api/start-codes  { codes: [...] }
    public function saveBulk()
    {
        if (!$this->requireAdmin()) {
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $codes = isset($input['codes']) && is_array($input['codes']) ? $input['codes'] : [];
        $existing = $this->loadAll();
        $map = [];
        foreach ($existing as $it) {
            $map[$it['code']] = $it;
        }
        foreach ($codes as $it) {
            if (!isset($it['code'])) {
                continue;
            }
            $code = strtoupper(trim($it['code']));
            if (!preg_match('/^[A-Z0-9_-]{3,64}$/', $code)) {
                continue;
            }
            $role = strtolower(trim($it['role'] ?? 'teacher'));
            if (!in_array($role, ['teacher','student','admin'])) {
                $role = 'teacher';
            }
            $map[$code] = [
                'role' => $role,
                'code' => $code,
                'created_at' => $it['created_at'] ?? date('c'),
                'expires_at' => $it['expires_at'] ?? '',
                'used' => (bool)($it['used'] ?? false)
            ];
        }
        $merged = array_values($map);
        $this->saveAll($merged);
        $this->json(['ok' => true, 'count' => count($merged)]);
    }

    // GET /api/start-codes
    public function listAll()
    {
        if (!$this->requireAdmin()) {
            return;
        }
        $this->json($this->loadAll());
    }

    // DELETE /api/start-codes
    public function clearAll()
    {

        if (!$this->requireAdmin()) {
            return;
        }
        $this->saveAll([]);
        $this->json(['ok' => true]);
    }

    // GET /api/start-codes/validate?code=ABC
    public function validate()
    {
        $code = strtoupper(trim($_GET['code'] ?? ''));
        $code = preg_replace('/[^A-Z0-9_-]/', '', $code);
        if ($code === '') {
            return $this->json(['valid' => false, 'reason' => 'missing'], 400);
        }
        $items = $this->loadAll();
        $found = null;
        foreach ($items as $it) {
            if (($it['code'] ?? '') === $code) {
                $found = $it;
                break;
            }
        }
        if (!$found) {
            return $this->json(['valid' => false, 'reason' => 'not_found']);
        }
        if (!empty($found['used'])) {
            return $this->json(['valid' => false, 'reason' => 'used']);
        }
        if (!empty($found['expires_at'])) {
            $t = strtotime($found['expires_at']);
            if ($t && time() > $t) {
                return $this->json(['valid' => false, 'reason' => 'expired']);
            }
        }
        $this->json(['valid' => true, 'role' => $found['role'], 'expires_at' => $found['expires_at'] ?? '']);
    }

    // POST /api/start-codes/use  { code: 'ABC' }
    public function useCode()
    {

        if (!$this->requireAdmin()) {
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $code = strtoupper(trim($input['code'] ?? ''));
        $code = preg_replace('/[^A-Z0-9_-]/', '', $code);
        if ($code === '') {
            return $this->json(['ok' => false, 'error' => 'missing_code'], 400);
        }
        $items = $this->loadAll();
        $updated = false;
        foreach ($items as &$it) {
            if (($it['code'] ?? '') === $code) {
                    $it['used'] = true;
                    $updated = true;
                    break;
            }
        }
        unset($it);
        if (!$updated) {
            return $this->json(['ok' => false, 'error' => 'not_found'], 404);
        }
        $this->saveAll($items);
        $this->json(['ok' => true]);
    }

    // GET /api/start-codes/ping
    public function ping()
    {
        $this->json(['ok' => true]);
    }
}
