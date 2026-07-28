<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_auth();

$user = current_user();

if (!$user) {
    flash('error', 'Your account session is no longer available. Please sign in again.');
    redirect('index.php');
}

render_app_start('Dashboard', $user);
?>
<section class="welcome-card">
    <div>
        <p class="step-label">ACCOUNT OVERVIEW</p>
        <h1>Good day, <?= e(explode(' ', $user['full_name'])[0]) ?>.</h1>
        <p>Your account is active and your current session is protected.</p>
    </div>
    <span class="large-avatar"><?= e(initials($user['full_name'])) ?></span>
</section>

<section class="stats-grid">
    <article><span>&check;</span><div><small>Account status</small><strong>Active</strong></div></article>
    <article><span>◎</span><div><small>Last sign-in</small><strong><?= $user['last_login_at'] ? e(date('M j, Y g:i A', strtotime($user['last_login_at']))) : 'Current session' ?></strong></div></article>
    <article><span>●</span><div><small>Session security</small><strong>Protected</strong></div></article>
</section>

<section class="dashboard-grid">
    <article class="content-card">
        <div class="card-title">
            <div><small>PROFILE INFORMATION</small><h2>Your account details</h2></div>
            <a href="<?= e(app_url('profile.php')) ?>">Edit profile</a>
        </div>
        <dl>
            <div><dt>Full name</dt><dd><?= e($user['full_name']) ?></dd></div>
            <div><dt>Email address</dt><dd><?= e($user['email']) ?></dd></div>
            <div><dt>Account type</dt><dd>Standard user</dd></div>
            <div><dt>Member since</dt><dd><?= e(date('F j, Y', strtotime($user['created_at']))) ?></dd></div>
        </dl>
    </article>
    <article class="content-card">
        <div class="card-title"><div><small>RECENT ACTIVITY</small><h2>Security log</h2></div></div>
        <ul class="activity-list">
            <li><span></span><div><strong>Successful sign-in</strong><small>Current protected session</small></div></li>
            <li><span class="muted"></span><div><strong>Password protected</strong><small>Secure hashing is active</small></div></li>
        </ul>
    </article>
</section>
<?php render_app_end(); ?>
