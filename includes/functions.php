<?php
declare(strict_types=1);

function e($value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    if (APP_URL !== '') {
        return $path === '' ? APP_URL . '/' : APP_URL . '/' . $path;
    }

    $root = str_replace('\\', '/', ROOT_PATH);
    $doc = str_replace('\\', '/', rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/'));
    $base = '';
    if ($doc !== '' && strncasecmp($root, $doc, strlen($doc)) === 0) {
        $base = trim(substr($root, strlen($doc)), '/');
        $base = $base === '' ? '' : '/' . $base;
    }

    if ($path === '') {
        return $base === '' ? '/' : $base . '/';
    }
    return ($base === '' ? '/' : $base . '/') . $path;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): void
{
    if (preg_match('#^https?://#i', $path)) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . url($path));
    }
    exit;
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

function request(string $key, $default = '')
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function old(string $key, $default = ''): string
{
    if (isset($_SESSION['_old'][$key])) {
        $val = (string) $_SESSION['_old'][$key];
        unset($_SESSION['_old'][$key]);
        return $val;
    }
    return (string) $default;
}

function flash_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function flashes(): array
{
    $all = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $all;
}

function setting(string $key, ?string $default = null): ?string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $rows = db_query('SELECT setting_key, setting_value FROM settings')->fetchAll();
            foreach ($rows as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            return $default;
        }
    }
    return $cache[$key] ?? $default;
}

function current_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return $uri;
}

function nav_active(string $file): string
{
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    return $script === $file ? 'is-active' : '';
}

function excerpt(string $text, int $len = 160): string
{
    $plain = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
    $length = function_exists('mb_strlen') ? mb_strlen($plain) : strlen($plain);
    if ($length <= $len) {
        return $plain;
    }
    $cut = function_exists('mb_substr') ? mb_substr($plain, 0, $len) : substr($plain, 0, $len);
    return $cut . '…';
}

function bn_date(?string $datetime, string $format = 'd M Y'): string
{
    if (!$datetime) {
        return '';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return '';
    }
    $en = date($format, $ts);
    $map = ['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯'];
    $months = [
        'Jan' => 'জানু', 'Feb' => 'ফেব', 'Mar' => 'মার্চ', 'Apr' => 'এপ্রিল',
        'May' => 'মে', 'Jun' => 'জুন', 'Jul' => 'জুলাই', 'Aug' => 'আগস্ট',
        'Sep' => 'সেপ্ট', 'Oct' => 'অক্টো', 'Nov' => 'নভে', 'Dec' => 'ডিসে',
    ];
    $en = strtr($en, $months);
    return strtr($en, $map);
}

function flag_emoji(?string $iso): string
{
    if (!$iso || strlen($iso) !== 2) {
        return '🌍';
    }
    $iso = strtoupper($iso);
    $out = '';
    foreach (str_split($iso) as $ch) {
        $cp = 0x1F1E6 + ord($ch) - ord('A');
        if (function_exists('mb_chr')) {
            $out .= mb_chr($cp, 'UTF-8') ?: '';
        } else {
            $out .= iconv('UTF-32BE', 'UTF-8', pack('N', $cp)) ?: '';
        }
    }
    return $out !== '' ? $out : '🌍';
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function rate_limited(string $action, int $max = 5, int $windowSeconds = 900): bool
{
    $ip = client_ip();
    $windowSeconds = max(1, (int) $windowSeconds);
    db_query(
        'DELETE FROM login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 DAY)'
    );
    $stmt = db_query(
        "SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND action = ? AND created_at > DATE_SUB(NOW(), INTERVAL {$windowSeconds} SECOND)",
        [$ip, $action]
    );
    $count = (int) $stmt->fetchColumn();
    return $count >= $max;
}

function rate_hit(string $action): void
{
    db_query(
        'INSERT INTO login_attempts (ip_address, action) VALUES (?, ?)',
        [client_ip(), $action]
    );
}

function valid_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function slugify(string $text): string
{
    $lower = function_exists('mb_strtolower') ? mb_strtolower($text) : strtolower($text);
    $text = trim($lower);
    $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text) ?? $text;
    $text = trim($text, '-');
    if ($text === '') {
        $text = 'item';
    }
    return function_exists('mb_substr') ? mb_substr($text, 0, 160) : substr($text, 0, 160);
}

function unique_slug(string $table, string $slug, ?int $ignoreId = null): string
{
    $allowed = ['countries' => true, 'news' => true];
    if (!isset($allowed[$table])) {
        throw new InvalidArgumentException('Invalid table');
    }
    $base = $slug;
    $i = 1;
    while (true) {
        $sql = 'SELECT id FROM ' . $table . ' WHERE slug = ?';
        $params = [$slug];
        if ($ignoreId) {
            $sql .= ' AND id != ?';
            $params[] = $ignoreId;
        }
        $exists = db_query($sql, $params)->fetch();
        if (!$exists) {
            return $slug;
        }
        $slug = $base . '-' . $i;
        $i++;
    }
}

function require_post_fields(array $fields): array
{
    $errors = [];
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = trim((string) ($_POST[$field] ?? ''));
        if ($data[$field] === '') {
            $errors[$field] = t('err.required');
        }
    }
    return [$data, $errors];
}

function countries(bool $activeOnly = true): array
{
    $sql = 'SELECT * FROM countries';
    if ($activeOnly) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY is_hq DESC, name_bn ASC';
    return db_query($sql)->fetchAll();
}

function country_by_slug(string $slug): ?array
{
    $row = db_query('SELECT * FROM countries WHERE slug = ? AND is_active = 1', [$slug])->fetch();
    return $row ?: null;
}

function member_count_by_country(int $countryId): int
{
    return (int) db_query(
        "SELECT COUNT(*) FROM members WHERE country_id = ? AND status = 'approved'",
        [$countryId]
    )->fetchColumn();
}

function random_password(int $length = 10): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $out;
}

function nl2p(string $text): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }
    $parts = preg_split('/\n\s*\n/', $text) ?: [$text];
    $html = '';
    foreach ($parts as $p) {
        $html .= '<p>' . nl2br(e($p), false) . '</p>';
    }
    return $html;
}

function page_meta(string $title, ?string $description = null): array
{
    return [
        'title' => $title . ' · ' . t('site.name_bn'),
        'description' => $description ?? t('meta.home'),
    ];
}
