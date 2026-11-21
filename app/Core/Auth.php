<?php
declare(strict_types=1);
namespace App\Core;

final class Auth
{
    public static function login(array $user): void
    {
        Session::regenerate();
        Session::put('employee_id', (int)$user['employee_id']);
        Session::put('role',        $user['role']);
        Session::put('full_name',   $user['full_name']);
        Session::put('phone',       $user['phone_number']);

        // Non-admin: branch is fixed from employee record
        // Admin: branch is chosen via picker after login
        if ($user['role'] === 'ADMIN') {
            Session::forget('branch_id');
        } else {
            Session::put('branch_id', (int)$user['branch_id']);
        }
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['employee_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) return null;
        return \App\Models\User::find((int)$_SESSION['employee_id']);
    }
}
