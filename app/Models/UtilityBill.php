<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class UtilityBill extends BaseModel
{
    protected static string $table = 'utility_bills';
    protected static string $pk    = 'bill_id';

    public static function unpaidCount(int $branchId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM utility_bills WHERE branch_id = ? AND is_paid = 0",
            [$branchId]
        );
    }

    public static function overdueCount(int $branchId, string $today): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM utility_bills
             WHERE branch_id = ? AND is_paid = 0 AND due_date IS NOT NULL AND due_date <= ?",
            [$branchId, $today]
        );
    }
}
