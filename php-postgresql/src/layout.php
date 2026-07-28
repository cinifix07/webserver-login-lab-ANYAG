<?php

declare(strict_types=1);

function render_head(string $title): void
{
    $appName = env('APP_NAME', 'Student Login System');
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <title><?= e($title) ?> | <?= e($appName) ?></title>
        <link rel="stylesheet" href="<?= e(app_url('assets/app.css')) ?>">
    </head>
    <body>
    <?php
}

function render_auth_start(string $headline, string $description, string $eyebrow): void
{
    render_head($headline);
    ?>
    <main class="auth-shell">
        <section class="brand-panel">
            <a class="brand" href="<?= e(app_url('index.php')) ?>">
                <span class="brand-mark">CA</span><span>STUDENT LOGIN</span>
            </a>
            <div class="brand-copy">
                <p class="eyebrow">SECURE &bull; STUDENT &bull; RELIABLE</p>
                <h1><?= e($headline) ?></h1>
                <p><?= e($description) ?></p>
            </div>
            <div class="security-note">
                <span class="check">&check;</span>
                <div><strong>Your information is protected</strong><p>Secure sessions and encrypted passwords keep your account safe.</p></div>
            </div>
        </section>
        <section class="form-panel">
            <div class="form-wrap">
                <a class="mobile-brand brand" href="<?= e(app_url('index.php')) ?>">
                    <span class="brand-mark">C</span><span>Student Login</span>
                </a>
                <p class="step-label"><?= e($eyebrow) ?></p>
    <?php
}

function render_auth_end(): void
{
    ?>
            </div>
            <footer><span>&copy; 2026 DEVELOPED BY 
                <a href="https://www.facebook.com/cinanyag" target="_blank" rel="noopener noreferrer">CINIFIX TECHNOLOGY</a>
            </span><span>Privacy &amp; Security</span></footer>
        </section>
    </main>
    </body>
    </html>
    <?php
}

function render_flash(?array $flash): void
{
    if (!$flash) {
        return;
    }
    $type = ($flash['type'] ?? '') === 'error' ? 'error' : 'success';
    ?>
    <p class="notice <?= e($type) ?>" role="status"><?= e((string) ($flash['message'] ?? '')) ?></p>
    <?php
}

function render_app_start(string $pageTitle, array $user): void
{
    render_head($pageTitle);
    $avatar = initials($user['full_name']);
    ?>
    <main class="app-shell">
        <aside class="sidebar">
            <a class="brand app-brand" href="<?= e(app_url('dashboard.php')) ?>">
                <span class="brand-mark">C</span><span>Student Login</span>
            </a>
            <nav>
                <a class="<?= $pageTitle === 'Dashboard' ? 'active' : '' ?>" href="<?= e(app_url('dashboard.php')) ?>">Dashboard</a>
                <a class="<?= $pageTitle === 'My Profile' ? 'active' : '' ?>" href="<?= e(app_url('profile.php')) ?>">My profile</a>
            </nav>
            <div class="sidebar-user">
                <span class="avatar"><?= e($avatar) ?></span>
                <div><strong><?= e($user['full_name']) ?></strong><small><?= e($user['email']) ?></small></div>
                <form method="post" action="<?= e(app_url('logout.php')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit">Sign out</button>
                </form>
            </div>
        </aside>
        <section class="workspace">
            <header><div><small>Student login System</small><strong><?= e($pageTitle) ?></strong></div><span class="avatar small"><?= e($avatar) ?></span></header>
            <div class="workspace-content">
    <?php
}

function render_app_end(): void
{
    ?>
            </div>
        </section>
    </main>
    </body>
    </html>
    <?php
}
