<?php
declare(strict_types=1);

function save_upload(string $field, string $subdir, ?string $oldPath = null): ?string
{
    if (!isset($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $oldPath;
    }

    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(t('err.upload', ['mb' => env('UPLOAD_MAX_MB', '2')]));
    }

    $max = ((int) env('UPLOAD_MAX_MB', '2')) * 1024 * 1024;
    if (($file['size'] ?? 0) > $max) {
        throw new RuntimeException(t('err.upload', ['mb' => env('UPLOAD_MAX_MB', '2')]));
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($map[$mime])) {
        throw new RuntimeException(t('err.upload', ['mb' => env('UPLOAD_MAX_MB', '2')]));
    }

    $dir = UPLOAD_PATH . DIRECTORY_SEPARATOR . $subdir;
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $name = bin2hex(random_bytes(8)) . '.' . $map[$mime];
    $dest = $dir . DIRECTORY_SEPARATOR . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException(t('err.upload', ['mb' => env('UPLOAD_MAX_MB', '2')]));
    }

    if ($oldPath) {
        $oldFull = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $oldPath);
        if (is_file($oldFull) && str_contains($oldFull, DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR)) {
            @unlink($oldFull);
        }
    }

    return 'assets/uploads/' . $subdir . '/' . $name;
}

function public_image(?string $path, string $fallback = ''): string
{
    if ($path && is_file(ROOT_PATH . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path))) {
        return url($path);
    }
    if ($path && preg_match('#^https?://#', $path)) {
        return $path;
    }
    return $fallback;
}
