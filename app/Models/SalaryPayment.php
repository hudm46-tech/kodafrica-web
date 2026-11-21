<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class SalaryPayment extends BaseModel
{
    protected static string $table = 'salary_payments';
    protected static string $pk    = 'payment_id';

    public const TYPES = [
        'SALARY'    => 'Salary (Monthly)',
        'POSHO'     => 'Posho (Daily Wage)',
        'OVERTIME'  => 'Overtime',
        'ADVANCE'   => 'Advance',
        'BONUS'     => 'Bonus',
        'NSSF'      => 'NSSF Contribution',
    ];

    public const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    public static function listByBranch(int $branchId, string $month, int $year): array
    {
        return Database::select(
            "SELECT sp.*, e.full_name AS employee_name, e.role
             FROM salary_payments sp
             JOIN employees e ON e.employee_id = sp.employee_id
             WHERE sp.branch_id = ? AND sp.period_month = ? AND sp.period_year = ?
             ORDER BY sp.payment_date DESC",
            [$branchId, $month, $year]
        );
    }

    public static function listByEmployee(int $employeeId): array
    {
        return Database::select(
            "SELECT * FROM salary_payments WHERE employee_id = ? ORDER BY payment_date DESC",
            [$employeeId]
        );
    }

    public static function totalsByBranch(int $branchId, string $month, int $year): array
    {
        $row = Database::first(
            "SELECT COALESCE(SUM(amount_paid),0) AS total,
                    COALESCE(SUM(CASE WHEN payment_type = 'SALARY' THEN amount_paid ELSE 0 END),0) AS salary,
                    COALESCE(SUM(CASE WHEN payment_type = 'POSHO' THEN amount_paid ELSE 0 END),0) AS posho,
                    COALESCE(SUM(CASE WHEN payment_type = 'NSSF' THEN amount_paid ELSE 0 END),0) AS nssf,
                    COUNT(*) AS cnt
             FROM salary_payments
             WHERE branch_id = ? AND period_month = ? AND period_year = ?",
            [$branchId, $month, $year]
        );
        return $row ?: ['total' => 0, 'salary' => 0, 'posho' => 0, 'nssf' => 0, 'cnt' => 0];
    }

    public static function getTotalSalary(int $branchId, string $month, int $year): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(amount_paid),0) FROM salary_payments
             WHERE branch_id = ? AND period_month = ? AND period_year = ? AND payment_type = 'SALARY'",
            [$branchId, $month, $year]
        );
    }

    public static function getTotalPosho(int $branchId, string $month, int $year): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(amount_paid),0) FROM salary_payments
             WHERE branch_id = ? AND period_month = ? AND period_year = ? AND payment_type = 'POSHO'",
            [$branchId, $month, $year]
        );
    }

    public static function create(array $d): int
    {
        return self::insertRow([
            'employee_id'      => $d['employee_id'],
            'branch_id'        => $d['branch_id'],
            'payment_type'     => $d['payment_type'],
            'period_month'     => $d['period_month'],
            'period_year'      => $d['period_year'],
            'days_worked'      => $d['days_worked'] ?? 0,
            'daily_rate'       => $d['daily_rate'] ?? 0,
            'base_amount'      => $d['base_amount'] ?? 0,
            'overtime_hours'   => $d['overtime_hours'] ?? 0,
            'overtime_rate'    => $d['overtime_rate'] ?? 0,
            'overtime_amount'  => $d['overtime_amount'] ?? 0,
            'bonus'            => $d['bonus'] ?? 0,
            'deductions'       => $d['deductions'] ?? 0,
            'amount_paid'      => $d['amount_paid'],
            'payment_method'   => $d['payment_method'] ?? 'CASH',
            'payment_reference'=> $d['payment_reference'] ?? null,
            'payment_date'     => $d['payment_date'] ?? now(),
            'notes'            => $d['notes'] ?? null,
            'last_updated'     => now(),
        ]);
    }

    public static function delete(int $id): bool
    {
        return Database::execute("DELETE FROM salary_payments WHERE payment_id = ?", [$id]) > 0;
    }

    public static function withDetails(int $id): ?array
    {
        return Database::first(
            "SELECT sp.*, e.full_name AS employee_name, e.phone_number AS employee_phone,
                    e.role, e.employment_type
             FROM salary_payments sp
             JOIN employees e ON e.employee_id = sp.employee_id
             WHERE sp.payment_id = ? LIMIT 1",
            [$id]
        );
    }
}
