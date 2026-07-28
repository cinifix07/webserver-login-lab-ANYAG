<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_url(string $path = ''): string
{
    return rtrim((string) env('APP_URL', ''), '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';

    if (!is_string($submitted) || !is_string($stored) || !hash_equals($stored, $submitted)) {
        http_response_code(419);
        exit('Your form session expired. Go back, refresh the page, and try again.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

function old(string $key, string $default = ''): string
{
    return e((string) ($_SESSION['old'][$key] ?? $default));
}

function store_old(array $values): void
{
    $_SESSION['old'] = $values;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

function require_guest(): void
{
    if (isset($_SESSION['user_id'])) {
        redirect('dashboard.php');
    }
}

function require_auth(): void
{
    if (!isset($_SESSION['user_id'])) {
        flash('error', 'Please sign in to access that page.');
        redirect('index.php');
    }
}

function current_user(): ?array
{
    static $user = false;

    if ($user !== false) {
        return $user;
    }

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $statement = database()->prepare(
        'SELECT id, full_name, email, last_login_at, created_at
         FROM users WHERE id = :id LIMIT 1'
    );
    $statement->execute(['id' => (int) $_SESSION['user_id']]);
    $record = $statement->fetch();
    $user = $record ?: null;

    if ($user === null) {
        unset($_SESSION['user_id']);
    }

    return $user;
}

function initials(string $fullName): string
{
    $parts = preg_split('/\s+/', trim($fullName)) ?: [];
    $letters = '';

    foreach (array_slice(array_filter($parts), 0, 2) as $part) {
        $letters .= mb_strtoupper(mb_substr($part, 0, 1));
    }

    return $letters ?: 'U';
}

function valid_password(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/\d/', $password);
}

function password_algorithm(): string|int|null
{
    return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
}
