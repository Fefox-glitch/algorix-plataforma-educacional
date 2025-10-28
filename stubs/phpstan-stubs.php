<?php

declare(strict_types=1);

// PHPStan stubs: define missing globals used across src without executing runtime bootstrap.

// Constants expected by utils/render
if (!defined('APP_ROOT')) {
    // Arbitrary path for analysis only; runtime sets this in init.php
    define('APP_ROOT', __DIR__ . '/..');
}

// Base URL constant may be referenced indirectly; define safe default
if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

// Supabase configuration constants referenced in code
if (!defined('SUPABASE_URL')) {
    define('SUPABASE_URL', '');
}
if (!defined('SUPABASE_KEY')) {
    define('SUPABASE_KEY', '');
}

/**
 * Supabase helper used throughout controllers.
 *
 * @param string $method HTTP method
 * @param string $endpoint REST endpoint (e.g., 'users')
 * @param array<string,mixed>|null $data Payload for write operations
 * @param array<string,string> $filters Query filters
 * @return array{code:int, data:mixed, error:string|null}
 */
if (!function_exists('supabaseRequest')) {
    function supabaseRequest(string $method, string $endpoint, ?array $data = null, array $filters = []): array
    {
        return ['code' => 200, 'data' => null, 'error' => null];
    }
}

/**
 * Redirect helper defined in runtime bootstrap.
 * @param string $path Relative path (e.g., 'auth/login')
 * @return void
 */
if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
    }
}

/**
 * Authentication helpers from runtime.
 * @return bool
 */
if (!function_exists('isAuthenticated')) {
    function isAuthenticated(): bool
    {
        return false;
    }
}

/**
 * @param string|array<int,string> $roles
 * @return bool
 */
if (!function_exists('hasRole')) {
    function hasRole($roles): bool
    {
        return false;
    }
}

/**
 * Require a specific role for API access.
 * @param string $role
 * @return void
 */
if (!function_exists('requireAuthRole')) {
    function requireAuthRole(string $role): void
    {
    }
}

/**
 * View renderer (snake_case variant used in index).
 * @param string $view
 * @param array<string,mixed> $data
 * @return string
 */
if (!function_exists('render_view')) {
    function render_view(string $view, array $data = []): string
    {
        return '';
    }
}

/**
 * JSON response helper.
 * @param int $code
 * @param mixed $data
 * @param string|null $error
 * @return void
 */
if (!function_exists('json_response')) {
    function json_response(int $code, $data = null, ?string $error = null): void
    {
    }
}

/**
 * Base URL builder.
 * @param string $path
 * @return string
 */
if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        return $path;
    }
}
