<?php
declare(strict_types=1);
namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) return self::$pdo;

        $cfg = config('db');
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']
        );

        try {
            $opts = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ];
            if (!empty($cfg['ssl'])) {
                // Managed MySQL hosts (TiDB Cloud, Aiven...) require TLS.
                $opts[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
                $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
            self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $opts);
        } catch (PDOException $e) {
            if (config('app.debug')) {
                die('DB connection error: ' . $e->getMessage());
            }
            die('Database unavailable. Please try again later.');
        }

        return self::$pdo;
    }

    public static function select(string $sql, array $params = []): array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function first(string $sql, array $params = []): ?array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function scalar(string $sql, array $params = [])
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchColumn();
    }

    public static function execute(string $sql, array $params = []): int
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    public static function insert(string $sql, array $params = []): int
    {
        self::execute($sql, $params);
        return (int) self::pdo()->lastInsertId();
    }

    public static function begin(): void    { self::pdo()->beginTransaction(); }
    public static function commit(): void   { self::pdo()->commit(); }
    public static function rollback(): void { if (self::pdo()->inTransaction()) self::pdo()->rollBack(); }
}
