<?php
declare(strict_types=1);

if (!function_exists('config')) {
    function config(string $key, $default = null) {
        static $cfg = null;
        if ($cfg === null) {
            $cfg = require dirname(__DIR__, 2) . '/config/config.php';
        }
        $parts = explode('.', $key);
        $val = $cfg;
        foreach ($parts as $p) {
            if (!is_array($val) || !array_key_exists($p, $val)) return $default;
            $val = $val[$p];
        }
        return $val;
    }
}

if (!function_exists('e')) {
    function e($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $base = rtrim(config('app.url'), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = '') {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('now')) {
    function now(): string {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('money')) {
    function money($amount): string {
        return 'TZS ' . number_format((float)$amount, 0, '.', ',');
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = []): void {
        \App\Core\View::render($template, $data);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        $token = \App\Core\Csrf::token();
        return '<input type="hidden" name="_csrf" value="' . e($token) . '">';
    }
}

if (!function_exists('auth_id')) {
    function auth_id(): ?int {
        return isset($_SESSION['employee_id']) ? (int)$_SESSION['employee_id'] : null;
    }
}

if (!function_exists('auth_role')) {
    function auth_role(): ?string {
        return $_SESSION['role'] ?? null;
    }
}

if (!function_exists('auth_branch')) {
    function auth_branch(): int {
        $b = isset($_SESSION['branch_id']) ? (int)$_SESSION['branch_id'] : 0;
        if ($b === 0) {
            // Admin without branch → picker
            if (($_SESSION['role'] ?? '') === 'ADMIN') {
                redirect('/select-branch');
            }
            // Non-admin should always have branch; if not, log out
            session_destroy();
            redirect('/login');
        }
        return $b;
    }
}

if (!function_exists('require_auth')) {
    function require_auth(): void {
        if (!auth_id()) {
            redirect('/login');
        }
    }
}

if (!function_exists('require_role')) {
    function require_role(string ...$roles): void {
        if (!in_array(auth_role(), $roles, true)) {
            http_response_code(403);
            die('Forbidden — insufficient permissions.');
        }
    }
}

if (!function_exists('csrf_token_safe')) {
    function csrf_token_safe(): string {
        return \App\Core\Csrf::token();
    }
}
