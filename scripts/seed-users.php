<?php
require_once __DIR__ . '/../config.php';

// Simple seeding script to create test users in Supabase
// Uses direct INSERTs (no prior SELECT checks) to avoid RLS read issues

$users = [
    [
        'name' => 'Alice Student',
        'email' => 'student1@example.com',
        'password' => 'Algorix123!',
        'role' => 'student'
    ],
    [
        'name' => 'Tom Teacher',
        'email' => 'teacher1@example.com',
        'password' => 'Algorix123!',
        'role' => 'teacher'
    ],
    [
        'name' => 'Ann Admin',
        'email' => 'admin1@example.com',
        'password' => 'Algorix123!',
        'role' => 'admin'
    ]
];

function seedUser($user) {
    $payload = [
        'name' => $user['name'],
        'email' => $user['email'],
        'password_hash' => password_hash($user['password'], PASSWORD_DEFAULT),
        'role' => $user['role']
    ];

    $resp = supabaseRequest('POST', 'users', $payload);

    if ($resp['code'] === 201 && !empty($resp['data'])) {
        $created = $resp['data'][0];
        echo "[CREATED] {$created['email']} ({$created['role']}) | id={$created['id']}\n";
        return true;
    } elseif ($resp['code'] === 409) { // unique violation
        echo "[SKIP] {$user['email']} already exists (409).\n";
        return true;
    } else {
        echo "[ERROR] {$user['email']} | HTTP {$resp['code']} | ";
        if (!empty($resp['data'])) {
            echo json_encode($resp['data']);
        } else {
            echo ($resp['error'] ?: 'unknown error');
        }
        echo "\n";
        return false;
    }
}

$ok = true;
foreach ($users as $u) {
    $ok = seedUser($u) && $ok;
}

echo "\nDone. Success=" . ($ok ? 'true' : 'false') . "\n";