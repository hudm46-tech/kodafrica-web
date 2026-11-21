<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class Product extends BaseModel
{
    protected static string $table = 'products';
    protected static string $pk    = 'product_id';

    public static function listByBranch(int $branchId, ?string $search = null, ?string $category = null, bool $lowOnly = false): array
    {
        $sql = "SELECT * FROM products WHERE branch_id = ?";
        $params = [$branchId];

        if ($search) {
            $sql .= " AND name LIKE ?";
            $params[] = '%' . $search . '%';
        }
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        if ($lowOnly) {
            $sql .= " AND quantity <= minimum_stock";
        }

        $sql .= " ORDER BY name ASC";
        return Database::select($sql, $params);
    }

    public static function categories(int $branchId): array
    {
        return array_column(
            Database::select(
                "SELECT DISTINCT category FROM products WHERE branch_id = ? AND category IS NOT NULL AND category != '' ORDER BY category ASC",
                [$branchId]
            ),
            'category'
        );
    }

    public static function lowStock(int $branchId): array
    {
        return Database::select(
            "SELECT * FROM products WHERE branch_id = ? AND quantity <= minimum_stock ORDER BY quantity ASC",
            [$branchId]
        );
    }

    public static function countByBranch(int $branchId): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM products WHERE branch_id = ?", [$branchId]);
    }

    public static function lowStockCount(int $branchId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM products WHERE branch_id = ? AND quantity <= minimum_stock",
            [$branchId]
        );
    }

    public static function totalStockValue(int $branchId): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(quantity * selling_price), 0) FROM products WHERE branch_id = ?",
            [$branchId]
        );
    }

    public static function totalPurchaseValue(int $branchId): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(quantity * purchase_price), 0) FROM products WHERE branch_id = ?",
            [$branchId]
        );
    }

    public static function create(array $d): int
    {
        return self::insertRow([
            'branch_id'       => $d['branch_id'],
            'name'            => $d['name'],
            'unit_of_measure' => $d['unit_of_measure'] ?? 'PCS',
            'quantity'        => (int)$d['quantity'],
            'minimum_stock'   => (int)($d['minimum_stock'] ?? 5),
            'purchase_price'  => (float)$d['purchase_price'],
            'selling_price'   => (float)$d['selling_price'],
            'category'        => $d['category'] ?? null,
            'supplier'        => $d['supplier'] ?? null,
            'remarks'         => $d['remarks'] ?? null,
            'created_at'      => now(),
            'last_updated'    => now(),
        ]);
    }

    public static function update(int $id, array $d): bool
    {
        return self::updateRow($id, [
            'name'            => $d['name'],
            'unit_of_measure' => $d['unit_of_measure'] ?? 'PCS',
            'minimum_stock'   => (int)($d['minimum_stock'] ?? 5),
            'purchase_price'  => (float)$d['purchase_price'],
            'selling_price'   => (float)$d['selling_price'],
            'category'        => $d['category'] ?? null,
            'supplier'        => $d['supplier'] ?? null,
            'remarks'         => $d['remarks'] ?? null,
            'last_updated'    => now(),
        ]);
    }

    public static function setQuantity(int $id, int $qty): void
    {
        Database::execute(
            "UPDATE products SET quantity = ?, last_updated = ? WHERE product_id = ?",
            [$qty, now(), $id]
        );
    }

    public static function adjustQuantity(int $id, int $delta): void
    {
        Database::execute(
            "UPDATE products SET quantity = quantity + ?, last_updated = ? WHERE product_id = ?",
            [$delta, now(), $id]
        );
    }

    public static function seedDefaults(int $branchId): void
    {
        if (self::countByBranch($branchId) > 0) return;

        $items = [
            ['RED OXIDE 1LTR','PCS',7,3000,5000,'Paints'],
            ['SPRAY','PCS',1,2500,5000,'Paints'],
            ['MAROO 1LTR','PCS',5,3000,5000,'Paints'],
            ['FAGIO NGU MU','PCS',16,2000,3000,'Cleaning'],
            ['GLOVES','PAIR',5,2000,5000,'Safety'],
            ['WATER PROOF','PCS',22,1500,3000,'Paint'],
            ['BULB WT5','PCS',4,2500,3000,'Electrical'],
            ['BULB WT11','PCS',11,2500,3000,'Electrical'],
            ['BULB WT18','PCS',3,3500,5000,'Electrical'],
            ['ROUND/JUNCTION BOX','PCS',80,250,500,'Electrical'],
            ['SQUARE BOX BLACK','PCS',18,250,500,'Electrical'],
            ['GAL TRAP','PCS',3,2000,3500,'Plumbing'],
            ['BRUSH INCH 2','PCS',22,1000,2000,'Tools'],
            ['BRUSH INCH 4','PCS',11,3000,4000,'Tools'],
            ['ELBOW INCH 1.5','PCS',18,1000,1500,'Plumbing'],
            ['ELBOW INCH 2','PCS',12,1500,2000,'Plumbing'],
            ['MISUMARI ZEGE','BOX',30,1000,2000,'Hardware'],
            ['P TRAP/CHUJIO BAFUNI','PCS',19,1500,2000,'Plumbing'],
            ['KONO BAO KUBWA','PCS',9,2000,5000,'Plumbing'],
            ['KONO BAO NDOGO','PCS',8,1500,3000,'Plumbing'],
            ['FYEKEO/SLASHER','PCS',28,3000,5000,'Tools'],
            ['SQUORE UJENZI KUBWA','PCS',34,5000,7000,'Hardware'],
            ['SQUORE UJENZI NDOGO','PCS',6,2500,5000,'Hardware'],
            ['SCRAPER KUBWA','PCS',4,2000,2500,'Tools'],
            ['SCRAPER NDOGO','PCS',5,1500,2000,'Tools'],
            ['KOKI PEX 0.5 INCH','PCS',2,5000,10000,'Plumbing'],
            ['CLIP CABLE','BOX',5,1000,3000,'Electrical'],
            ['KOBIRO','PCS',14,3000,5000,'Tools'],
            ['SQUARE BOX CHUMA','PCS',6,1500,3000,'Electrical'],
            ['PASI UJENZI','PCS',1,3000,7000,'Tools'],
            ['STRAIGHT HOLDER','PCS',7,1000,2000,'Electrical'],
            ['ANGLE HOLDER','PCS',22,1000,2000,'Electrical'],
            ['TRED HOLDER','PCS',3,1000,2000,'Electrical'],
            ['MULTIPLUG AFRICABLE','PCS',1,3000,5000,'Electrical'],
            ['COPLIN','PCS',13,1000,2000,'Electrical'],
            ['MO 2 GANG SWITCH','PCS',31,3000,3500,'Electrical'],
            ['MO 1 GANG SWITCH','PCS',13,3000,3500,'Electrical'],
            ['TAN UK 3 GANG SWITCH','PCS',9,2500,3000,'Electrical'],
            ['HIPPO 1 GANG SWITCH','PCS',9,4000,5000,'Electrical'],
            ['HIPPO SWITCH SOCKET','PCS',8,3000,4000,'Electrical'],
            ['AFRICAB 3 GANG SWITCH','PCS',16,3000,4500,'Electrical'],
            ['T-IPS 3/4 INCH','PCS',22,1500,3000,'Plumbing'],
            ['T-IPS 1/2 INCH','PCS',5,1000,2000,'Plumbing'],
            ['NIPPLE PLUG','PCS',184,500,1000,'Plumbing'],
            ['NIPPLE 3/4 INCH','PCS',95,1000,2000,'Plumbing'],
            ['NIPPLE 1/2 INCH','PCS',112,500,1000,'Plumbing'],
            ['NIPPLE SOCKET','PCS',126,500,1000,'Plumbing'],
            ['TRAP','PCS',20,1500,2000,'Plumbing'],
            ['MISUMARI INCH 4','KG',6,2975,4000,'Hardware'],
            ['MISUMARI INCH 2','KG',23,2975,4000,'Hardware'],
            ['MISUMARI INCH 3','KG',3,2975,4000,'Hardware'],
            ['MISUMARI YA BATI','KG',4,4666,6000,'Hardware'],
            ['RINGI','PCS',148,500,800,'Hardware'],
            ['CONDUIT PIPE','PCS',12,1000,1500,'Electrical'],
            ['BATI','PCS',6,17000,23000,'Roofing'],
            ['PLUG','PCS',8,3000,4000,'Electrical'],
            ['MSUMENO FRAME','PCS',7,3000,6000,'Hardware'],
            ['PIMA MAJI NDOGO','PCS',3,3000,5000,'Tools'],
            ['PIMA MAJI KUBWA','PCS',7,3500,7000,'Tools'],
            ['MWIKO UJENZI INCH 6','PCS',8,1500,2500,'Hardware'],
            ['BINDING WIRE','PCS',28,1700,3000,'Electrical'],
            ['PANGA','PCS',12,3000,5000,'Tools'],
            ['KENJI WIRE','PCS',13,2000,3000,'Electrical'],
            ['MIKASI BATI','PCS',2,7000,8000,'Tools'],
            ['BAWABA GOLD OXFORD','PCS',20,3000,5000,'Tools'],
            ['GRINDING DISK NDOGO','PCS',9,3000,6000,'Tools'],
            ['GRINDING DISK KUBWA','PCS',8,5000,7000,'Tools'],
            ['TESTER','PCS',7,1000,2000,'Electrical'],
            ['PATASI','PCS',2,4000,5000,'Tools'],
            ['TAPE MEASURE 3 METRE','PCS',4,1500,3000,'Tools'],
            ['WIRE 1.5','METRE',24,1200,1700,'Electrical'],
            ['FLEXIBLE WIRE','METRE',42,500,1000,'Electrical'],
            ['MSASA SIZE 40','METRE',20,1000,2000,'Hardware'],
            ['MSASA SIZE 120','METRE',8,1000,2000,'Hardware'],
            ['FLAT BAR','PCS',3,10000,15000,'Hardware'],
            ['CORAL PAINT BURLEY BROWN 4L','PCS',4,22000,25000,'Paints'],
            ['GOLDSTAR MAJI WHITE 4L','PCS',4,9000,10000,'Paints'],
            ['GOLDSTAR SUMMER BLUE 4L','PCS',6,22000,25000,'Paints'],
            ['CORAL VANISH 4L','PCS',2,22000,28000,'Paints'],
            ['CORAL PAINT GREY 4L','PCS',1,22000,25000,'Paints'],
        ];

        $sql = "INSERT INTO products (branch_id, name, unit_of_measure, quantity, minimum_stock,
                                     purchase_price, selling_price, category, created_at, last_updated)
                VALUES (?, ?, ?, ?, 5, ?, ?, ?, ?, ?)";
        $now = now();
        foreach ($items as $it) {
            Database::execute($sql, [$branchId, $it[0], $it[1], $it[2], $it[3], $it[4], $it[5], $now, $now]);
        }
    }
}
