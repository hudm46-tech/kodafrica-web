<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class Sale extends BaseModel
{
    protected static string $table = 'sales';
    protected static string $pk    = 'sale_id';

    public const PAYMENT_METHODS = [
        'CASH'    => 'Cash',
        'MPESA'   => 'M-Pesa',
        'TIGO'    => 'Tigo Pesa',
        'AIRTEL'  => 'Airtel Money',
        'HALOPESA'=> 'HaloPesa',
        'CREDIT'  => 'Credit (Lipa Baadaye)',
    ];

    public static function sumBetween(int $branchId, string $from, string $to): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(total_amount), 0) FROM sales
             WHERE branch_id = ? AND sale_date BETWEEN ? AND ?",
            [$branchId, $from, $to]
        );
    }

    public static function profitBetween(int $branchId, string $from, string $to): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM((selling_price_at_sale - purchase_price_at_sale) * quantity_sold - discount), 0)
             FROM sales WHERE branch_id = ? AND sale_date BETWEEN ? AND ?",
            [$branchId, $from, $to]
        );
    }

    public static function countBetween(int $branchId, string $from, string $to): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM sales WHERE branch_id = ? AND sale_date BETWEEN ? AND ?",
            [$branchId, $from, $to]
        );
    }

    public static function recent(int $branchId, int $limit = 5): array
    {
        return Database::select(
            "SELECT sale_id, product_name, quantity_sold, total_amount, payment_method, sale_date
             FROM sales WHERE branch_id = ? ORDER BY sale_date DESC LIMIT ?",
            [$branchId, $limit]
        );
    }

    public static function byBranchBetween(int $branchId, string $from, string $to): array
    {
        return Database::select(
            "SELECT s.*, e.full_name AS cashier_name
             FROM sales s LEFT JOIN employees e ON e.employee_id = s.employee_id
             WHERE s.branch_id = ? AND s.sale_date BETWEEN ? AND ?
             ORDER BY s.sale_date DESC",
            [$branchId, $from, $to]
        );
    }

    public static function totalsByMethod(int $branchId, string $from, string $to): array
    {
        $rows = Database::select(
            "SELECT payment_method, SUM(total_amount) AS total, COUNT(*) AS cnt
             FROM sales WHERE branch_id = ? AND sale_date BETWEEN ? AND ?
             GROUP BY payment_method",
            [$branchId, $from, $to]
        );
        $out = [];
        foreach ($rows as $r) {
            $out[$r['payment_method']] = ['total' => (float)$r['total'], 'count' => (int)$r['cnt']];
        }
        return $out;
    }

    public static function topProducts(int $branchId, string $from, string $to, int $limit = 10): array
    {
        return Database::select(
            "SELECT product_name, SUM(quantity_sold) AS qty, SUM(total_amount) AS total
             FROM sales WHERE branch_id = ? AND sale_date BETWEEN ? AND ?
             GROUP BY product_name ORDER BY qty DESC LIMIT ?",
            [$branchId, $from, $to, $limit]
        );
    }

    /**
     * Get today's sale groups (each checkout = same sale_date timestamp + same employee).
     */
    public static function groupedToday(int $branchId): array
    {
        return Database::select(
            "SELECT DATE_FORMAT(sale_date, '%Y-%m-%d %H:%i:%s') AS group_ts,
                    employee_id, payment_method, COUNT(*) AS items,
                    SUM(total_amount) AS total, MIN(sale_date) AS first_ts
             FROM sales
             WHERE branch_id = ? AND DATE(sale_date) = CURDATE()
             GROUP BY group_ts, employee_id, payment_method
             ORDER BY first_ts DESC",
            [$branchId]
        );
    }

    /**
     * Group ALL sales in a period by sale_date timestamp.
     * Returns list of "sale groups" (each checkout = one group).
     */
    public static function groupedByPeriod(int $branchId, string $from, string $to, ?string $method = null, ?int $employeeId = null): array
    {
        $sql = "SELECT DATE_FORMAT(s.sale_date, '%Y-%m-%d %H:%i:%s') AS group_ts,
                       s.employee_id, e.full_name AS cashier_name,
                       s.payment_method, s.customer_name, s.customer_phone,
                       COUNT(*) AS items, SUM(s.total_amount) AS total,
                       MIN(s.sale_date) AS first_ts,
                       MIN(s.is_paid) AS all_paid
                FROM sales s
                LEFT JOIN employees e ON e.employee_id = s.employee_id
                WHERE s.branch_id = ? AND s.sale_date BETWEEN ? AND ?";
        $params = [$branchId, $from, $to];

        if ($method) { $sql .= " AND s.payment_method = ?"; $params[] = $method; }
        if ($employeeId) { $sql .= " AND s.employee_id = ?"; $params[] = $employeeId; }

        $sql .= " GROUP BY group_ts, s.employee_id, e.full_name, s.payment_method, s.customer_name, s.customer_phone
                  ORDER BY first_ts DESC";
        return Database::select($sql, $params);
    }

    /**
     * Unpaid credit sales (not yet marked as paid).
     */
    public static function unpaidCredits(int $branchId): array
    {
        return Database::select(
            "SELECT DATE_FORMAT(s.sale_date, '%Y-%m-%d %H:%i:%s') AS group_ts,
                    s.employee_id, e.full_name AS cashier_name,
                    s.customer_name, s.customer_phone,
                    COUNT(*) AS items, SUM(s.total_amount) AS total,
                    MIN(s.sale_date) AS first_ts,
                    DATEDIFF(NOW(), MIN(s.sale_date)) AS days_ago
             FROM sales s
             LEFT JOIN employees e ON e.employee_id = s.employee_id
             WHERE s.branch_id = ? AND s.payment_method = 'CREDIT' AND s.is_paid = 0
             GROUP BY group_ts, s.employee_id, e.full_name, s.customer_name, s.customer_phone
             ORDER BY first_ts DESC",
            [$branchId]
        );
    }

    /**
     * Mark all sale rows in a group as paid.
     */
    public static function markGroupAsPaid(int $branchId, string $groupTs): int
    {
        return Database::execute(
            "UPDATE sales SET is_paid = 1, last_updated = ? WHERE branch_id = ? AND sale_date = ? AND is_paid = 0",
            [now(), $branchId, $groupTs]
        );
    }

    /**
     * All sales belonging to a group timestamp.
     */
    public static function groupRows(int $branchId, string $groupTs): array
    {
        return Database::select(
            "SELECT * FROM sales WHERE branch_id = ? AND sale_date = ? ORDER BY sale_id ASC",
            [$branchId, $groupTs]
        );
    }
}
