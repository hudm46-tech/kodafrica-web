<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Models\Branch;
use App\Models\Sale;

final class SaleController
{
    // ---------- GET /sales ----------
    public function index(): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();

        $from   = $_GET['from']   ?? date('Y-m-01');
        $to     = $_GET['to']     ?? date('Y-m-d');
        $method = $_GET['method'] ?? null;
        $cashier = isset($_GET['cashier']) && $_GET['cashier'] !== '' ? (int)$_GET['cashier'] : null;

        $groups = Sale::groupedByPeriod($branchId, $from . ' 00:00:00', $to . ' 23:59:59', $method, $cashier);

        // Totals
        $totalAmount = 0; $totalItems = 0;
        foreach ($groups as $g) { $totalAmount += (float)$g['total']; $totalItems += (int)$g['items']; }
        $methodTotals = Sale::totalsByMethod($branchId, $from . ' 00:00:00', $to . ' 23:59:59');

        // Cashier list for filter
        $cashiers = Database::select(
            "SELECT DISTINCT e.employee_id, e.full_name
             FROM sales s JOIN employees e ON e.employee_id = s.employee_id
             WHERE s.branch_id = ? ORDER BY e.full_name",
            [$branchId]
        );

        // Credit section
        $unpaidCredits = Sale::unpaidCredits($branchId);
        $creditTotal = array_sum(array_map(fn($c) => (float)$c['total'], $unpaidCredits));

        view('sales/index', [
            'layout'         => 'app',
            'pageTitle'      => 'Sales History',
            'user'           => Auth::user(),
            'groups'         => $groups,
            'from'           => $from,
            'to'             => $to,
            'method'         => $method,
            'cashier'        => $cashier,
            'cashiers'       => $cashiers,
            'totalAmount'    => $totalAmount,
            'totalItems'     => $totalItems,
            'methodTotals'   => $methodTotals,
            'unpaidCredits'  => $unpaidCredits,
            'creditTotal'    => $creditTotal,
            'paymentMethods' => Sale::PAYMENT_METHODS,
        ]);
    }

    // ---------- GET /sales/{ts} ----------
    public function show(string $ts): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();

        $ts = urldecode($ts);
        $sales = Sale::groupRows($branchId, $ts);
        if (empty($sales)) {
            Flash::error('Sale not found.');
            redirect('/sales');
        }

        $branch   = Branch::find($branchId);
        $business = Database::first("SELECT * FROM app_settings WHERE id = 1");
        $cashier  = Auth::user();

        // Find the actual cashier of this sale
        $saleCashier = Database::first(
            "SELECT full_name FROM employees WHERE employee_id = ?",
            [$sales[0]['employee_id']]
        );

        $total = 0; $itemCount = 0;
        foreach ($sales as $s) { $total += (float)$s['total_amount']; $itemCount += (int)$s['quantity_sold']; }

        $firstSale = $sales[0];

        view('sales/receipt', [
            'layout'        => 'receipt',
            'pageTitle'     => 'Receipt',
            'branch'        => $branch,
            'business'      => $business,
            'cashier'       => $saleCashier ?? $cashier,
            'sales'         => $sales,
            'total'         => $total,
            'itemCount'     => $itemCount,
            'paymentMethod' => $firstSale['payment_method'],
            'customerName'  => $firstSale['customer_name'],
            'customerPhone' => $firstSale['customer_phone'],
            'reference'     => $firstSale['payment_reference'],
            'saleTs'        => $ts,
            'isPaid'        => (int)$firstSale['is_paid'] === 1,
        ]);
    }

    // ---------- POST /sales/mark-paid ----------
    public function markPaid(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER', 'CASHIER');

        $ts = $_POST['group_ts'] ?? '';
        if (!$ts) { Flash::error('Missing sale reference.'); redirect('/sales'); }

        $branchId = auth_branch();
        $count = Sale::markGroupAsPaid($branchId, $ts);

        if ($count > 0) {
            Flash::success("Marked $count credit item" . ($count === 1 ? '' : 's') . " as paid.");
        } else {
            Flash::error('No unpaid items found for this sale.');
        }
        redirect('/sales');
    }
}
