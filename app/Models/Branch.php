<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class Branch extends BaseModel
{
    protected static string $table = 'branches';
    protected static string $pk    = 'branch_id';

    public static function listAll(bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM branches";
        if ($activeOnly) $sql .= " WHERE is_active = 1";
        $sql .= " ORDER BY is_active DESC, branch_name ASC";
        return Database::select($sql);
    }

    public static function create(array $d): int
    {
        return self::insertRow([
            'branch_name'         => $d['branch_name'],
            'location'            => $d['location'],
            'phone_number'        => $d['phone_number'],
            'tin_number'          => $d['tin_number'] ?? '',
            'license_number'      => $d['license_number'],
            'license_expiry_date' => $d['license_expiry_date'],
            'license_pdf_path'    => $d['license_pdf_path'] ?? null,
            'is_active'           => 1,
            'created_at'          => now(),
            'last_updated'        => now(),
        ]);
    }

    public static function update(int $id, array $d): bool
    {
        return self::updateRow($id, [
            'branch_name'         => $d['branch_name'],
            'location'            => $d['location'],
            'phone_number'        => $d['phone_number'],
            'tin_number'          => $d['tin_number'] ?? '',
            'license_number'      => $d['license_number'],
            'license_expiry_date' => $d['license_expiry_date'],
            'last_updated'        => now(),
        ]);
    }

    public static function toggle(int $id, bool $active): void
    {
        Database::execute(
            "UPDATE branches SET is_active = ?, last_updated = ? WHERE branch_id = ?",
            [$active ? 1 : 0, now(), $id]
        );
    }

    public static function hasProducts(int $id): bool
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM products WHERE branch_id = ?", [$id]) > 0;
    }
}
