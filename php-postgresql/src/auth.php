<?php

declare(strict_types=1);

function find_user_by_email(string $email): ?array
{
    $statement = database()->prepare(
        'SELECT id, full_name, email, password_hash, last_login_at, created_at
         FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1'
    );
    $statement->execute(['email' => trim($email)]);
    $user = $statement->fetch();

    return $user ?: null;
}

function register_user(string $fullName, string $email, string $password): int
{
    $statement = database()->prepare(
        'INSERT INTO users (full_name, email, password_hash)
         VALUES (:full_name, :email, :password_hash)
         RETURNING id'
    );
    $statement->execute([
        'full_name' => trim($fullName),
        'email' => mb_strtolower(trim($email)),
        'password_hash' => password_hash($password, password_algorithm()),
    ]);

    return (int) $statement->fetchColumn();
}

function login_is_throttled(): bool
{
    $lockedUntil = (int) ($_SESSION['login_locked_until'] ?? 0);
    return $lockedUntil > time();
}

function record_login_failure(): void
{
    $failures = (int) ($_SESSION['login_failures'] ?? 0) + 1;
    $_SESSION['login_failures'] = $failures;

    if ($failures >= 5) {
        $_SESSION['login_locked_until'] = time() + 30;
        $_SESSION['login_failures'] = 0;
    }
}

function attempt_login(string $email, string $password): bool
{
    if (login_is_throttled()) {
        return false;
    }

    $user = find_user_by_email($email);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        record_login_failure();
        return false;
    }

    if (password_needs_rehash($user['password_hash'], password_algorithm())) {
        $rehash = database()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $rehash->execute([
            'hash' => password_hash($password, password_algorithm()),
            'id' => $user['id'],
        ]);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['login_failures'] = 0;
    unset($_SESSION['login_locked_until']);

    $statement = database()->prepare(
        'UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id'
    );
    $statement->execute(['id' => $user['id']]);

    return true;
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}

function create_password_reset(string $email): ?string
{
    $user = find_user_by_email($email);

    if (!$user) {
        return null;
    }

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    database()->prepare(
        'UPDATE password_reset_tokens
         SET used_at = CURRENT_TIMESTAMP
         WHERE user_id = :user_id AND used_at IS NULL'
    )->execute(['user_id' => $user['id']]);

    $statement = database()->prepare(
        "INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
         VALUES (:user_id, :token_hash, CURRENT_TIMESTAMP + INTERVAL '30 minutes')"
    );
    $statement->execute([
        'user_id' => $user['id'],
        'token_hash' => $tokenHash,
    ]);

    return $token;
}

function find_valid_reset(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }

    $statement = database()->prepare(
        'SELECT prt.id AS reset_id, prt.user_id, u.email
         FROM password_reset_tokens prt
         INNER JOIN users u ON u.id = prt.user_id
         WHERE prt.token_hash = :token_hash
           AND prt.used_at IS NULL
           AND prt.expires_at > CURRENT_TIMESTAMP
         LIMIT 1'
    );
    $statement->execute(['token_hash' => hash('sha256', $token)]);
    $reset = $statement->fetch();

    return $reset ?: null;
}

function reset_user_password(string $token, string $password): bool
{
    $reset = find_valid_reset($token);

    if (!$reset) {
        return false;
    }

    $connection = database();
    $connection->beginTransaction();

    try {
        $statement = $connection->prepare(
            'UPDATE users
             SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute([
            'password_hash' => password_hash($password, password_algorithm()),
            'id' => $reset['user_id'],
        ]);

        $used = $connection->prepare(
            'UPDATE password_reset_tokens
             SET used_at = CURRENT_TIMESTAMP
             WHERE id = :id AND used_at IS NULL'
        );
        $used->execute(['id' => $reset['reset_id']]);

        $connection->commit();
        return true;
    } catch (Throwable $exception) {
        $connection->rollBack();
        error_log($exception->getMessage());
        return false;
    }
}
