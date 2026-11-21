<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\UtilityBill;
use App\Models\Branch;

final class DashboardController
{
    public function index(): void
    {
        if (!Auth::check()) redirect('/login');

        $user     = Auth::user();
        $branchId = (int)$user['branch_id'];

        if ($branchId === 0) redirect('/setup/branch');

        $branch = Branch::find($branchId);

        // Today's range
        $todayStart = date('Y-m-d 00:00:00');
        $now        = date('Y-m-d H:i:s');

        // Stats
        $todaySales    = Sale::sumBetween($branchId, $todayStart, $now);
        $todayProfit   = Sale::profitBetween($branchId, $todayStart, $now);
        $todayExpenses = Expense::sumBetween($branchId, $todayStart, $now);
        $todayTxnCount = Sale::countBetween($branchId, $todayStart, $now);

        // Alerts
        $lowStock    = Product::lowStock($branchId);
        $unpaidBills = UtilityBill::unpaidCount($branchId);
        $overdueBills = UtilityBill::overdueCount($branchId, $now);

        // Product summary
        $productCount    = Product::countByBranch($branchId);
        $stockValue      = Product::totalStockValue($branchId);

        view('dashboard/index', [
            'layout'         => 'app',
            'pageTitle'      => 'Dashboard',
            'user'           => $user,
            'branch'         => $branch,
            'todaySales'     => $todaySales,
            'todayProfit'    => $todayProfit,
            'todayExpenses'  => $todayExpenses,
            'todayTxnCount'  => $todayTxnCount,
            'lowStock'       => $lowStock,
            'unpaidBills'    => $unpaidBills,
            'overdueBills'   => $overdueBills,
            'productCount'   => $productCount,
            'stockValue'     => $stockValue,
        ]);
    }
}
