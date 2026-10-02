<?php
declare(strict_types=1);
/**
 * Shared HTML layout — header.
 * Usage:  $pageTitle = 'Login'; include __DIR__ . '/../../views/layout/header.php';
 */
$pageTitle ??= 'PHP App';
$flash_error   = flash_get('error');
$flash_success = flash_get('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> — <?= htmlspecialchars(trans('app_name')) ?></title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/"><?= htmlspecialchars(trans('app_name')) ?></a>
        <div class="ms-auto">
            <?php if (auth_check()): ?>
                <a href="/dashboard.php" class="btn btn-outline-light btn-sm me-2"><?= htmlspecialchars(trans('nav_dashboard')) ?></a>
                <a href="/api/logout.php"  class="btn btn-light btn-sm"><?= htmlspecialchars(trans('nav_logout')) ?></a>
            <?php else: ?>
                <a href="/login.php"    class="btn btn-outline-light btn-sm me-2"><?= htmlspecialchars(trans('nav_login')) ?></a>
                <a href="/register.php" class="btn btn-light btn-sm"><?= htmlspecialchars(trans('nav_register')) ?></a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mb-3">
    <div class="d-flex justify-content-end align-items-center">
        <label for="lang-select" class="me-2 mb-0 text-muted"><?= htmlspecialchars(trans('lang_label')) ?></label>
        <select id="lang-select" class="form-select form-select-sm w-auto" aria-label="<?= htmlspecialchars(trans('lang_label')) ?>">
            <option value="en"<?= get_locale() === 'en' ? ' selected' : '' ?>>🇬🇧 <?= htmlspecialchars(trans('lang_en')) ?></option>
            <option value="gr"<?= get_locale() === 'gr' ? ' selected' : '' ?>>🇬🇷 <?= htmlspecialchars(trans('lang_gr')) ?></option>
        </select>
    </div>
</div>

<script>
// Preserve other query params and update lang; reload page.
document.getElementById('lang-select')?.addEventListener('change', function () {
    const val = this.value;
    const url = new URL(window.location.href);
    url.searchParams.set('lang', val);
    window.location.href = url.toString();
});
</script>

<script>
// Expose a small set of translated strings to frontend JS.
window.APP_TRANSLATIONS = window.APP_TRANSLATIONS || {};
window.APP_TRANSLATIONS.show_password = <?= json_encode(trans('show_password')) ?>;
window.APP_TRANSLATIONS.hide_password = <?= json_encode(trans('hide_password')) ?>;
</script>

<div class="container">
<?php if ($flash_error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash_error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($flash_success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash_success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
