<?php
declare(strict_types=1);
namespace Madar;
defined('MADAR_BOOTED') || exit;

final class Database {
    public static function connection(): \PDO {
        static $pdo;
        if ($pdo instanceof \PDO) return $pdo;
        $file = dirname(__DIR__) . '/config.local.php';
        if (!is_file($file)) throw new \RuntimeException('Create config.local.php using config.example.php');
        $settings = require $file;
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $settings['host'], (int) ($settings['port'] ?? 3306), $settings['database']);
        return $pdo = new \PDO($dsn, $settings['username'], $settings['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
