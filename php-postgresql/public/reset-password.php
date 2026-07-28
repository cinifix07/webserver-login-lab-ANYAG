<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_guest();

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$reset = find_valid_reset($token);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (!$reset) {
        flash('error', 'This password-reset link is invalid or has expired.');
        redirect('forgot-password.php');
    }
    if (!valid_password($password)) {
        flash('error', 'Use at least 8 characters with uppercase, lowercase, and a number.');
        redirect('reset-password.php?token=' . urlencode($token));
    }
    if ($password !== $confirmation) {
        flash('error', 'The passwords do not match.');
        redirect('reset-password.php?token=' . urlencode($token));
    }
    if (!reset_user_password($token, $password)) {
        flash('error', 'The password could not be updated. Request a new reset link.');
        redirect('forgot-password.php');
    }

    flash('success', 'Password updated successfully. Sign in with your new password.');
    redirect('index.php');
}

$flash = pull_flash();
render_auth_start(
    'Protect your account.',
    'Choose a strong new password to keep your account secure.',
    'CREATE PASSWORD'
);
?>
<a class="back-link" href="<?= e(app_url('forgot-password.php')) ?>">&larr; Request another link</a>
<h2>Choose a new password</h2>
<p class="lead">Use at least 8 characters and avoid reused passwords.</p>
<?php render_flash($flash); ?>
<?php if ($reset): ?>
    <form class="auth-form" method="post" action="<?= e(app_url('reset-password.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <label for="password">New password</label>
        <input id="password" name="password" type="password" placeholder="At least 8 characters" autocomplete="new-password" required autofocus>
        <label for="password_confirmation">Confirm new password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Enter the password again" autocomplete="new-password" required>
        <button class="primary-button" type="submit">Update password</button>
    </form>
<?php else: ?>
    <p class="notice error">This password-reset link is invalid or has expired.</p>
<?php endif; ?>
<?php render_auth_end(); ?>
