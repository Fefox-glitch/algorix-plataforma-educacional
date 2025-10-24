<?php
// Simple smoke tests for routes and render
$base = 'http://localhost:8000';

function httpRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true); // include headers
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // do not follow redirects
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($err) {
        return [
            'error' => $err,
            'status' => 0,
            'headers' => '',
            'body' => ''
        ];
    }

    $header_size = $info['header_size'] ?? 0;
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    $status = $info['http_code'] ?? 0;

    return [
        'status' => $status,
        'headers' => $headers,
        'body' => $body,
        'error' => null
    ];
}

function httpRequestMethod($url, $method = 'GET', $headers = [], $body = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($err) {
        return [
            'error' => $err,
            'status' => 0,
            'headers' => '',
            'body' => ''
        ];
    }

    $header_size = $info['header_size'] ?? 0;
    $headersStr = substr($response, 0, $header_size);
    $bodyStr = substr($response, $header_size);
    $status = $info['http_code'] ?? 0;

    return [
        'status' => $status,
        'headers' => $headersStr,
        'body' => $bodyStr,
        'error' => null
    ];
}

function hasHeaderLocation($headers, $expectedPath) {
    foreach (explode("\n", $headers) as $line) {
        $line = trim($line);
        if (stripos($line, 'Location:') === 0) {
            // extract value
            $loc = trim(substr($line, strlen('Location:')));
            return $loc === $expectedPath || $loc === '/' . ltrim($expectedPath, '/');
        }
    }
    return false;
}

function printResult($name, $ok, $detail = '') {
    $status = $ok ? 'OK' : 'FAIL';
    $emoji = $ok ? '✅' : '❌';
    echo "$emoji $name: $status" . ($detail ? " - $detail" : '') . "\n";
}

$total = 0; $passed = 0;

// Tests definitions
$tests = [
    function() use ($base) {
        $r = httpRequest($base . '/');
        $ok = ($r['status'] === 200) && (strpos($r['body'], '<div class="main-container">') !== false);
        printResult('Home renders', $ok, 'status=' . $r['status']);
        return $ok;
    },
    function() use ($base) {
        $r = httpRequest($base . '/auth/login');
        $ok = ($r['status'] === 200) && (strpos($r['body'], 'class="auth-screen"') !== false || strpos($r['body'], 'class="auth-screen ') !== false);
        printResult('Login renders', $ok, 'status=' . $r['status']);
        return $ok;
    },
    function() use ($base) {
        $r = httpRequest($base . '/auth/register');
        $ok = ($r['status'] === 200) && (strpos($r['body'], 'class="auth-screen') !== false) && (strpos($r['body'], 'auth-register') !== false);
        printResult('Register renders', $ok, 'status=' . $r['status']);
        return $ok;
    },
    function() use ($base) {
        $r = httpRequest($base . '/teacher/dashboard');
        $ok = ($r['status'] === 302) && hasHeaderLocation($r['headers'], '/auth/login');
        printResult('Teacher redirects when unauthenticated', $ok, 'status=' . $r['status']);
        return $ok;
    },
    function() use ($base) {
        $r = httpRequest($base . '/admin/dashboard');
        $ok = ($r['status'] === 302) && hasHeaderLocation($r['headers'], '/auth/login');
        printResult('Admin redirects when unauthenticated', $ok, 'status=' . $r['status']);
        return $ok;
    },
    function() use ($base) {
        $r = httpRequest($base . '/no-existe');
        $ok = ($r['status'] === 404) && (strpos($r['body'], '404') !== false);
        printResult('Unknown route returns 404', $ok, 'status=' . $r['status']);
        return $ok;
    },
    // Restore endpoints: method and CSRF enforcement
    function() use ($base) {
        $r = httpRequest($base . '/api/admin/labs/restore');
        $ok = ($r['status'] === 405);
        printResult('Labs restore rejects GET', $ok, 'status=' . $r['status']);
        return $ok;
    },
    function() use ($base) {
        $r = httpRequestMethod($base . '/api/admin/labs/restore', 'PATCH', ['Content-Type: application/json'], '{"id":1}');
        $ok = ($r['status'] === 403);
        printResult('Labs restore requires CSRF', $ok, 'status=' . $r['status']);
        return $ok;
    },
    function() use ($base) {
        $r = httpRequest($base . '/api/admin/computers/restore');
        $ok = ($r['status'] === 405);
        printResult('Computers restore rejects GET', $ok, 'status=' . $r['status']);
        return $ok;
    },
    function() use ($base) {
        $r = httpRequestMethod($base . '/api/admin/computers/restore', 'PATCH', ['Content-Type: application/json'], '{"id":1}');
        $ok = ($r['status'] === 403);
        printResult('Computers restore requires CSRF', $ok, 'status=' . $r['status']);
        return $ok;
    },
    function() use ($base) {
        $r = httpRequest($base . '/api/admin/groups/restore');
        $ok = ($r['status'] === 405);
        printResult('Groups restore rejects GET', $ok, 'status=' . $r['status']);
        return $ok;
    },
    function() use ($base) {
        $r = httpRequestMethod($base . '/api/admin/groups/restore', 'PATCH', ['Content-Type: application/json'], '{"id":1}');
        $ok = ($r['status'] === 403);
        printResult('Groups restore requires CSRF', $ok, 'status=' . $r['status']);
        return $ok;
    }
];

foreach ($tests as $t) { $total++; if ($t()) $passed++; }

echo "\nSummary: $passed/$total passed\n";
if ($passed !== $total) { exit(1); }