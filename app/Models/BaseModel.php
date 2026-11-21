<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

abstract class BaseModel
{
    protected static string $table = '';
    protected static string $pk    = 'id';

    public static function find(int $id): ?array
    {
        return Database::first("SELECT * FROM " . static::$table . " WHERE " . static::$pk . " = ? LIMIT 1", [$id]);
    }

    public static function all(?string $orderBy = null): array
    {
        $sql = "SELECT * FROM " . static::$table;
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        return Database::select($sql);
    }

    public static function count(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM " . static::$table);
    }

    public static function delete(int $id): bool
    {
        return Database::execute("DELETE FROM " . static::$table . " WHERE " . static::$pk . " = ?", [$id]) > 0;
    }

    protected static function insertRow(array $data): int
    {
        $cols = array_keys($data);
        $place = implode(',', array_fill(0, count($cols), '?'));
        $sql = "INSERT INTO " . static::$table . " (" . implode(',', $cols) . ") VALUES ($place)";
        return Database::insert($sql, array_values($data));
    }

    protected static function updateRow(int $id, array $data): bool
    {
        $set = implode(',', array_map(fn($c) => "$c = ?", array_keys($data)));
        $sql = "UPDATE " . static::$table . " SET $set WHERE " . static::$pk . " = ?";
        return Database::execute($sql, [...array_values($data), $id]) > 0;
    }
}
