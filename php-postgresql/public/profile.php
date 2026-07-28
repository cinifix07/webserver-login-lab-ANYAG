<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_auth();

$user = current_user();

if (!$user) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));

    if (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 120) {
        flash('error', 'Enter a valid full name.');
        redirect('profile.php');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        flash('error', 'Enter a valid email address.');
        redirect('profile.php');
    }

    $existing = find_user_by_email($email);
    if ($existing && (int) $existing['id'] !== (int) $user['id']) {
        flash('error', 'That email address is already connected to another account.');
        redirect('profile.php');
    }

    $statement = database()->prepare(
        'UPDATE users
         SET full_name = :full_name, email = :email, updated_at = CURRENT_TIMESTAMP
         WHERE id = :id'
    );
    $statement->execute([
        'full_name' => $fullName,
        'email' => mb_strtolower($email),
        'id' => $user['id'],
    ]);

    flash('success', 'Your profile has been updated.');
    redirect('profile.php');
}

$flash = pull_flash();
render_app_start('My Profile', $user);
?>
<section class="profile-hero">
    <span class="large-avatar"><?= e(initials($user['full_name'])) ?></span>
    <div><p class="step-label">PERSONAL INFORMATION</p><h1>Manage your profile</h1><p>Keep your account information accurate and up to date.</p></div>
</section>

<section class="content-card profile-card">
    <div class="card-title"><div><small>ACCOUNT DETAILS</small><h2>Basic information</h2></div></div>
    <?php render_flash($flash); ?>
    <form class="profile-form" method="post" action="<?= e(app_url('profile.php')) ?>">
        <?= csrf_field() ?>
        <div>
            <label for="full_name">Full name</label>
            <input id="full_name" name="full_name" type="text" value="<?= e($user['full_name']) ?>" maxlength="120" required>
        </div>
        <div>
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="<?= e($user['email']) ?>" maxlength="190" required>
        </div>
        <div class="form-actions">
            <a class="secondary-button" href="<?= e(app_url('dashboard.php')) ?>">Cancel</a>
            <button class="primary-button" type="submit">Save changes</button>
        </div>
    </form>
</section>
<?php render_app_end(); ?>
