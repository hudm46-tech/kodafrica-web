<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class User extends BaseModel
{
    protected static string $table = 'employees';
    protected static string $pk    = 'employee_id';

    public const ROLES = [
        'ADMIN'   => 'Owner / Admin',
        'MANAGER' => 'Manager',
        'CASHIER' => 'Cashier',
    ];

    public static function findByPhone(string $phone): ?array
    {
        return Database::first("SELECT * FROM employees WHERE phone_number = ? LIMIT 1", [$phone]);
    }

    public static function create(array $d): int
    {
        return self::insertRow([
            'branch_id'        => $d['branch_id'] ?? 0,
            'full_name'        => $d['full_name'],
            'phone_number'     => $d['phone_number'],
            'email'            => $d['email'] ?? null,
            'password_hash'    => password_hash($d['password'], PASSWORD_BCRYPT),
            'role'             => $d['role'] ?? 'CASHIER',
            'employment_type'  => $d['employment_type'] ?? 'PERMANENT',
            'monthly_salary'   => $d['monthly_salary'] ?? 0,
            'daily_rate'       => $d['daily_rate'] ?? 0,
            'hire_date'        => $d['hire_date'] ?? now(),
            'is_active'        => 1,
            'failed_login_count' => 0,
            'created_at'       => now(),
            'last_updated'     => now(),
        ]);
    }

    public static function update(int $id, array $d): bool
    {
        $fields = [
            'full_name'       => $d['full_name'] ?? null,
            'phone_number'    => $d['phone_number'] ?? null,
            'email'           => $d['email'] ?? null,
            'role'            => $d['role'] ?? null,
            'employment_type' => $d['employment_type'] ?? null,
            'monthly_salary'  => $d['monthly_salary'] ?? null,
            'daily_rate'      => $d['daily_rate'] ?? null,
            'hire_date'       => $d['hire_date'] ?? null,
            'is_active'       => $d['is_active'] ?? 1,
            'nssf_number'     => $d['nssf_number'] ?? null,
            'notes'           => $d['notes'] ?? null,
            'last_updated'    => now(),
        ];
        $fields = array_filter($fields, fn($v) => $v !== null);

        if (!empty($d['password'])) {
            $fields['password_hash'] = password_hash($d['password'], PASSWORD_BCRYPT);
        }

        return self::updateRow($id, $fields);
    }

    public static function attempt(string $phone, string $password): ?array
    {
        $user = self::findByPhone($phone);
        if (!$user) return null;
        if ((int)$user['is_active'] !== 1) return null;
        if (!password_verify($password, $user['password_hash'])) {
            Database::execute(
                "UPDATE employees SET failed_login_count = failed_login_count + 1, last_failed_login = ? WHERE employee_id = ?",
                [now(), $user['employee_id']]
            );
            return null;
        }
        Database::execute(
            "UPDATE employees SET failed_login_count = 0, last_failed_login = NULL WHERE employee_id = ?",
            [$user['employee_id']]
        );
        return $user;
    }

    public static function setBranch(int $id, int $branchId): void
    {
        Database::execute(
            "UPDATE employees SET branch_id = ?, last_updated = ? WHERE employee_id = ?",
            [$branchId, now(), $id]
        );
    }

    public static function listByBranch(int $branchId, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM employees WHERE branch_id = ?";
        if ($activeOnly) $sql .= " AND is_active = 1";
        $sql .= " ORDER BY role ASC, full_name ASC";
        return Database::select($sql, [$branchId]);
    }

    public static function toggleActive(int $id, int $active): void
    {
        Database::execute(
            "UPDATE employees SET is_active = ?, last_updated = ? WHERE employee_id = ?",
            [$active ? 1 : 0, now(), $id]
        );
    }

    public static function resetPassword(int $id, string $newPassword): void
    {
        Database::execute(
            "UPDATE employees SET password_hash = ?, failed_login_count = 0, last_failed_login = NULL, last_updated = ? WHERE employee_id = ?",
            [password_hash($newPassword, PASSWORD_BCRYPT), now(), $id]
        );
    }
}
