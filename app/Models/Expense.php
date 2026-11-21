<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class Expense extends BaseModel
{
    protected static string $table = 'expenses';
    protected static string $pk    = 'expense_id';

    // ---- Categories (matching Android Expense.java) ----
    public const CATEGORIES = [
        'STAFF' => [
            'STAFF_SALARY'      => 'Salary',
            'STAFF_POSHO'       => 'Posho',
            'STAFF_OVERTIME'    => 'Overtime',
            'STAFF_ADVANCE'     => 'Advance',
            'STAFF_NSSF'        => 'NSSF',
            'STAFF_UNIFORM'     => 'Uniform',
            'STAFF_MEDICAL'     => 'Medical',
        ],
        'UTILITY' => [
            'UTILITY_ELECTRICITY' => 'Electricity (TANESCO)',
            'UTILITY_WATER'       => 'Water (DAWASA)',
            'UTILITY_INTERNET'    => 'Internet',
            'UTILITY_AIRTIME'     => 'Airtime',
            'UTILITY_RENT'        => 'Rent',
            'UTILITY_SECURITY'    => 'Security',
            'UTILITY_GARBAGE'     => 'Garbage Collection',
        ],
        'STOCK' => [
            'STOCK_TRANSPORT_IN'  => 'Transport In',
            'STOCK_TRANSPORT_OUT' => 'Transport Out',
            'STOCK_OFFLOADING'    => 'Offloading',
            'STOCK_PACKAGING'     => 'Packaging',
            'STOCK_FUEL'          => 'Fuel',
            'STOCK_VEHICLE_REPAIR' => 'Vehicle Repair',
        ],
        'GOVERNMENT' => [
            'GOV_LICENSE'    => 'Business License',
            'GOV_TAX'        => 'Tax Payment',
            'GOV_LEVY'       => 'Levy',
            'GOV_FIRE_SAFETY' => 'Fire Safety',
            'GOV_HEALTH_PERMIT' => 'Health Permit',
        ],
        'SHOP' => [
            'SHOP_REPAIRS'    => 'Shop Repairs',
            'SHOP_CLEANING'   => 'Cleaning Supplies',
            'SHOP_BANK_CHARGES' => 'Bank Charges',
            'SHOP_MARKETING'  => 'Marketing',
            'SHOP_STATIONERY' => 'Stationery',
            'SHOP_FOOD'       => 'Food & Refreshments',
        ],
        'OTHER' => [
            'OTHER' => 'Other',
        ],
    ];

    public static function allCategories(): array
    {
        $flat = [];
        foreach (self::CATEGORIES as $group => $items) {
            foreach ($items as $key => $label) {
                $flat[$key] = ['label' => $label, 'group' => $group];
            }
        }
        return $flat;
    }

    public static function categoryLabel(string $key): string
    {
        return self::allCategories()[$key]['label'] ?? $key;
    }

    public static function categoryGroup(string $key): string
    {
        return self::allCategories()[$key]['group'] ?? 'OTHER';
    }

    // ---- Queries ----
    public static function listByBranch(int $branchId, ?string $from = null, ?string $to = null, ?string $group = null, ?string $efdFilter = null): array
    {
        $sql = "SELECT e.*, emp.full_name AS employee_name
                FROM expenses e
                LEFT JOIN employees emp ON emp.employee_id = e.employee_id
                WHERE e.branch_id = ?";
        $params = [$branchId];

        if ($from) { $sql .= " AND e.expense_date >= ?"; $params[] = $from . ' 00:00:00'; }
        if ($to)   { $sql .= " AND e.expense_date <= ?"; $params[] = $to . ' 23:59:59'; }

        if ($group && isset(self::CATEGORIES[$group])) {
            $keys = array_keys(self::CATEGORIES[$group]);
            $place = implode(',', array_fill(0, count($keys), '?'));
            $sql .= " AND e.category IN ($place)";
            $params = array_merge($params, $keys);
        }

        if ($efdFilter === 'with') {
            $sql .= " AND e.has_efd_receipt = 1";
        } elseif ($efdFilter === 'without') {
            $sql .= " AND e.has_efd_receipt = 0";
        }

        $sql .= " ORDER BY e.expense_date DESC";
        return Database::select($sql, $params);
    }

    public static function sumBetween(int $branchId, string $from, string $to): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(amount), 0) FROM expenses
             WHERE branch_id = ? AND expense_date BETWEEN ? AND ?",
            [$branchId, $from, $to]
        );
    }

    public static function byCategory(int $branchId, string $from, string $to): array
    {
        $rows = Database::select(
            "SELECT category, SUM(amount) AS total, COUNT(*) AS cnt FROM expenses
             WHERE branch_id = ? AND expense_date BETWEEN ? AND ?
             GROUP BY category ORDER BY total DESC",
            [$branchId, $from, $to]
        );
        foreach ($rows as &$r) {
            $r['label'] = self::categoryLabel($r['category']);
            $r['group'] = self::categoryGroup($r['category']);
        }
        return $rows;
    }

    public static function byGroup(int $branchId, string $from, string $to): array
    {
        $byCategory = self::byCategory($branchId, $from, $to);
        $groups = [];
        foreach ($byCategory as $r) {
            $g = $r['group'];
            if (!isset($groups[$g])) $groups[$g] = ['total' => 0, 'count' => 0, 'items' => []];
            $groups[$g]['total'] += (float)$r['total'];
            $groups[$g]['count'] += (int)$r['cnt'];
            $groups[$g]['items'][] = $r;
        }
        return $groups;
    }

    public static function create(array $d): int
    {
        return self::insertRow([
            'branch_id'          => $d['branch_id'],
            'employee_id'        => $d['employee_id'] ?? auth_id() ?? 0,
            'category'           => $d['category'],
            'description'        => $d['description'],
            'amount'             => $d['amount'],
            'receipt_image_path' => $d['receipt_image_path'] ?? null,
            'receipt_pdf_path'   => $d['receipt_pdf_path'] ?? null,
            'expense_date'       => $d['expense_date'],
            'has_efd_receipt'    => $d['has_efd_receipt'] ?? 0,
            'efd_receipt_number' => $d['efd_receipt_number'] ?? null,
            'notes'              => $d['notes'] ?? null,
            'last_updated'       => now(),
        ]);
    }

    public static function update(int $id, array $d): bool
    {
        return self::updateRow($id, [
            'category'     => $d['category'],
            'description'  => $d['description'],
            'amount'       => $d['amount'],
            'expense_date'       => $d['expense_date'],
            'has_efd_receipt'    => $d['has_efd_receipt'] ?? 0,
            'efd_receipt_number' => $d['efd_receipt_number'] ?? null,
            'notes'              => $d['notes'] ?? null,
            'last_updated'       => now(),
        ]);
    }

    // ===== EFD helpers =====
    public static function sumWithEfd(int $branchId, string $from, string $to): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(amount), 0) FROM expenses
             WHERE branch_id = ? AND expense_date BETWEEN ? AND ? AND has_efd_receipt = 1",
            [$branchId, $from, $to]
        );
    }

    public static function sumWithoutEfd(int $branchId, string $from, string $to): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(amount), 0) FROM expenses
             WHERE branch_id = ? AND expense_date BETWEEN ? AND ? AND has_efd_receipt = 0",
            [$branchId, $from, $to]
        );
    }

    public static function countWithEfd(int $branchId, string $from, string $to): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM expenses
             WHERE branch_id = ? AND expense_date BETWEEN ? AND ? AND has_efd_receipt = 1",
            [$branchId, $from, $to]
        );
    }

    public static function countWithoutEfd(int $branchId, string $from, string $to): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM expenses
             WHERE branch_id = ? AND expense_date BETWEEN ? AND ? AND has_efd_receipt = 0",
            [$branchId, $from, $to]
        );
    }
}
