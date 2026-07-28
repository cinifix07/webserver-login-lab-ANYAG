<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_guest();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    store_old(['email' => $email]);

    if (login_is_throttled()) {
        flash('error', 'Too many sign-in attempts. Please wait 30 seconds and try again.');
        redirect('index.php');
    }

    if (attempt_login($email, $password)) {
        clear_old();
        redirect('dashboard.php');
    }

    flash('error', 'The email address or password is incorrect.');
    redirect('index.php');
}

$flash = pull_flash();
render_auth_start(
    'Welcome back.',
    'Sign in to manage your account through a clean and secure authentication experience.',
    'ACCOUNT ACCESS'
);
?>
<h2>Sign in to your account</h2>
<p class="lead">Enter your registered email address and password.</p>
<?php render_flash($flash); ?>
<form class="auth-form" method="post" action="<?= e(app_url('index.php')) ?>">
    <?= csrf_field() ?>
    <label for="email">Email address</label>
    <input id="email" name="email" type="email" value="<?= old('email') ?>" placeholder="name@example.com" autocomplete="email" required autofocus>

    <div class="label-row">
        <label for="password">Password</label>
        <a href="<?= e(app_url('forgot-password.php')) ?>">Forgot password?</a>
    </div>
    <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>

    <button class="primary-button" type="submit">Sign in </button>
</form>
<p class="switch-line">New to Student Login? <a href="<?= e(app_url('register.php')) ?>">Create an account</a></p>
<?php render_auth_end(); ?>
