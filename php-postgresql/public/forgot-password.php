<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_guest();

$developmentLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Enter a valid email address.');
        redirect('forgot-password.php');
    }

    $token = create_password_reset($email);
    flash('success', 'If an account matches that email, a password-reset link has been prepared.');

    if ($token && env('APP_ENV', 'local') === 'local') {
        $_SESSION['development_reset_link'] = app_url('reset-password.php?token=' . urlencode($token));
    }

    redirect('forgot-password.php');
}

$flash = pull_flash();

if (isset($_SESSION['development_reset_link'])) {
    $developmentLink = (string) $_SESSION['development_reset_link'];
    unset($_SESSION['development_reset_link']);
}

render_auth_start(
    'Recover access.',
    'Follow a clear and protected process to regain access to your account.',
    'ACCOUNT RECOVERY'
);
?>
<a class="back-link" href="<?= e(app_url('index.php')) ?>">&larr; Back to sign in</a>
<h2>Reset your password</h2>
<p class="lead">Enter your registered email address to continue.</p>
<?php render_flash($flash); ?>
<form class="auth-form" method="post" action="<?= e(app_url('forgot-password.php')) ?>">
    <?= csrf_field() ?>
    <label for="email">Email address</label>
    <input id="email" name="email" type="email" placeholder="name@example.com" autocomplete="email" required autofocus>
    <button class="primary-button" type="submit">Prepare reset link</button>
</form>
<?php if ($developmentLink): ?>
    <div class="dev-link">
        <strong>Local development reset link</strong>
        <a href="<?= e($developmentLink) ?>">Open password reset</a>
        <small>This appears only when APP_ENV is set to local.</small>
    </div>
<?php endif; ?>
<?php render_auth_end(); ?>
