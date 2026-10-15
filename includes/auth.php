<?php
declare(strict_types=1);

function current_member(): ?array
{
    if (empty($_SESSION['member_id'])) {
        return null;
    }
    static $member = false;
    if ($member === false) {
        $row = db_query('SELECT * FROM members WHERE id = ?', [$_SESSION['member_id']])->fetch();
        $member = $row ?: null;
        if ($member && $member['status'] !== 'approved') {
            unset($_SESSION['member_id']);
            $member = null;
        }
    }
    return $member;
}

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    static $admin = false;
    if ($admin === false) {
        $row = db_query('SELECT * FROM admins WHERE id = ?', [$_SESSION['admin_id']])->fetch();
        $admin = $row ?: null;
    }
    return $admin;
}

function require_member(): array
{
    $member = current_member();
    if (!$member) {
        flash('error', t('err.unauthorized'));
        redirect('login.php');
    }
    return $member;
}

function require_admin(): array
{
    $admin = current_admin();
    if (!$admin) {
        flash('error', t('err.unauthorized'));
        redirect('admin/login.php');
    }
    return $admin;
}

function login_member(array $member): void
{
    session_regenerate_id(true);
    $_SESSION['member_id'] = (int) $member['id'];
}

function login_admin(array $admin): void
{
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
}

function logout_member(): void
{
    unset($_SESSION['member_id']);
}

function logout_admin(): void
{
    unset($_SESSION['admin_id']);
}

function max_attempts(): int
{
    return (int) env('LOGIN_MAX_ATTEMPTS', '5');
}

function attempt_window(): int
{
    return ((int) env('LOGIN_WINDOW_MINUTES', '15')) * 60;
}
