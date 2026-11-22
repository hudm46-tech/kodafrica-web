<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class Purchase extends BaseModel
{
    protected static string $table = 'purchases';
    protected static string $pk    = 'purchase_id';

    public static function search(int $branchId, string $from, string $to, string $q = ''): array
    {
        $sql = "SELECT * FROM purchases WHERE branch_id = ? AND purchase_date BETWEEN ? AND ?";
        $params = [$branchId, $from, $to];
        if ($q !== '') {
            $sql .= " AND (product_name LIKE ? OR supplier_name LIKE ? OR receipt_number LIKE ?)";
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like);
        }
        $sql .= " ORDER BY purchase_date DESC, purchase_id DESC";
        return Database::select($sql, $params);
    }

    public static function suppliers(int $branchId): array
    {
        return array_column(Database::select(
            "SELECT supplier_name FROM purchases WHERE branch_id = ?
             UNION SELECT supplier FROM products WHERE branch_id = ? AND supplier IS NOT NULL AND supplier <> ''
             ORDER BY 1 ASC",
            [$branchId, $branchId]
        ), 'supplier_name');
    }

    public static function lastSupplierFor(int $productId): string
    {
        return (string) (Database::scalar(
            "SELECT supplier_name FROM purchases WHERE product_id = ? AND status = 'ACTIVE' ORDER BY purchase_id DESC LIMIT 1",
            [$productId]
        ) ?: '');
    }
}
