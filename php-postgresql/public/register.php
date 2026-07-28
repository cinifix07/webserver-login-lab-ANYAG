<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_guest();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');
    store_old(['full_name' => $fullName, 'email' => $email]);

    $errors = [];

    if (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 120) {
        $errors[] = 'Enter a valid full name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        $errors[] = 'Enter a valid email address.';
    }
    if (!valid_password($password)) {
        $errors[] = 'Use at least 8 characters with uppercase, lowercase, and a number.';
    }
    if ($password !== $confirmation) {
        $errors[] = 'The passwords do not match.';
    }
    if (find_user_by_email($email)) {
        $errors[] = 'An account with this email address already exists.';
    }

    if ($errors) {
        flash('error', implode(' ', $errors));
        redirect('register.php');
    }

    try {
        register_user($fullName, $email, $password);
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        flash('error', 'The account could not be created. Please verify your details and try again.');
        redirect('register.php');
    }

    clear_old();
    flash('success', 'Account created successfully. You can now sign in.');
    redirect('index.php');
}

$flash = pull_flash();
render_auth_start(
    'Start securely.',
    'Create one account for simple, reliable access to your personal dashboard.',
    'NEW ACCOUNT'
);
?>
<a class="back-link" href="<?= e(app_url('index.php')) ?>">&larr; Back to sign in</a>
<h2>Create your account</h2>
<p class="lead">Complete the form below to get started.</p>
<?php render_flash($flash); ?>
<form class="auth-form compact" method="post" action="<?= e(app_url('register.php')) ?>">
    <?= csrf_field() ?>
    <label for="full_name">Full name</label>
    <input id="full_name" name="full_name" type="text" value="<?= old('full_name') ?>" placeholder="Your complete name" maxlength="120" required autofocus>

    <label for="email">Email address</label>
    <input id="email" name="email" type="email" value="<?= old('email') ?>" placeholder="name@example.com" maxlength="190" autocomplete="email" required>

    <label for="password">Password</label>
    <input id="password" name="password" type="password" placeholder="At least 8 characters" autocomplete="new-password" required>
    <small class="field-help">Include uppercase, lowercase, and a number.</small>

    <label for="password_confirmation">Confirm password</label>
    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Enter the password again" autocomplete="new-password" required>

    <button class="primary-button" type="submit">Create account</button>
</form>
<?php render_auth_end(); ?>
