<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = env('DB_HOST', 'localhost');
    $port = env('DB_PORT', '3306');
    $name = env('DB_NAME', 'refabdco_main');
    $user = env('DB_USER', 'refabdco_main');
    $pass = env('DB_PASS', '');

    $dsns = [];
    if ($host === 'localhost' || $host === '127.0.0.1') {
        $dsns[] = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $name);
    }
    $dsns[] = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);
    $dsns = array_values(array_unique($dsns));

    $last = null;
    foreach ($dsns as $dsn) {
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return $pdo;
        } catch (PDOException $e) {
            $last = $e;
        }
    }

    $message = $last ? $last->getMessage() : 'unknown';
    if (defined('APP_DEBUG') && APP_DEBUG) {
        exit('ডাটাবেজ সংযোগ ব্যর্থ: ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    }
    throw new PDOException('ডাটাবেজ সংযোগ করা যায়নি: ' . $message);
}

function db_query(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
