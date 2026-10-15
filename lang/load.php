<?php
declare(strict_types=1);

function t(string $key, array $replace = []): string
{
    static $strings = null;
    if ($strings === null) {
        $locale = $_SESSION['locale'] ?? 'bn';
        $file = ROOT_PATH . '/lang/' . $locale . '.php';
        if (!is_file($file)) {
            $file = ROOT_PATH . '/lang/bn.php';
        }
        $strings = require $file;
    }

    $text = $strings[$key] ?? $key;
    foreach ($replace as $search => $value) {
        $text = str_replace(':' . $search, (string) $value, $text);
    }
    return $text;
}
