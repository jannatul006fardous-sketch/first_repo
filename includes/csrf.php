<?php
declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token = null): bool
{
    $token = $token ?? (string) ($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return hash_equals($_SESSION['_csrf'] ?? '', $token);
}

function require_csrf(): void
{
    if (!csrf_verify()) {
        http_response_code(419);
        flash('error', t('err.csrf'));
        $script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
        $dir = basename(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($dir === 'admin') {
            redirect('admin/' . $script);
        } elseif ($dir === 'member') {
            redirect('member/' . $script);
        }
        redirect($script === 'index.php' ? 'index.php' : $script);
    }
}
