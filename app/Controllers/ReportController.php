<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Product;
use App\Models\SalaryPayment;

final class ReportController
{
    // ---------- GET /reports ----------
    public function index(): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();

        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');

        $stats = [
            'today_sales'   => Sale::sumBetween($branchId, "$today 00:00:00", "$today 23:59:59"),
            'month_sales'   => Sale::sumBetween($branchId, "$monthStart 00:00:00", "$today 23:59:59"),
            'month_profit'  => Sale::profitBetween($branchId, "$monthStart 00:00:00", "$today 23:59:59"),
            'month_expense' => Expense::sumBetween($branchId, "$monthStart 00:00:00", "$today 23:59:59"),
            'low_stock'     => Product::lowStockCount($branchId),
        ];

        view('reports/index', [
            'layout'    => 'app',
            'pageTitle' => 'Reports',
            'user'      => Auth::user(),
            'stats'     => $stats,
            'today'     => $today,
            'monthStart'=> $monthStart,
        ]);
    }

    // ---------- GET /reports/sales ----------
    public function sales(): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();

        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        $fromDt = "$from 00:00:00";
        $toDt   = "$to 23:59:59";

        // Daily breakdown
        $daily = Database::select(
            "SELECT DATE(sale_date) AS day,
                    COUNT(DISTINCT CONCAT(sale_date, employee_id)) AS txns,
                    SUM(quantity_sold) AS items,
                    SUM(total_amount) AS total
             FROM sales
             WHERE branch_id = ? AND sale_date BETWEEN ? AND ?
             GROUP BY DATE(sale_date)
             ORDER BY day DESC",
            [$branchId, $fromDt, $toDt]
        );

        // Payment method breakdown
        $byMethod = Sale::totalsByMethod($branchId, $fromDt, $toDt);

        // Cashier breakdown
        $byCashier = Database::select(
            "SELECT e.full_name, COUNT(*) AS items, SUM(s.total_amount) AS total
             FROM sales s JOIN employees e ON e.employee_id = s.employee_id
             WHERE s.branch_id = ? AND s.sale_date BETWEEN ? AND ?
             GROUP BY e.employee_id, e.full_name
             ORDER BY total DESC",
            [$branchId, $fromDt, $toDt]
        );

        $totalSales = Sale::sumBetween($branchId, $fromDt, $toDt);
        $totalItems = (int) Database::scalar(
            "SELECT COALESCE(SUM(quantity_sold),0) FROM sales WHERE branch_id=? AND sale_date BETWEEN ? AND ?",
            [$branchId, $fromDt, $toDt]
        );

        view('reports/sales', [
            'layout'    => 'app',
            'pageTitle' => 'Sales Report',
            'user'      => Auth::user(),
            'from'      => $from,
            'to'        => $to,
            'daily'     => $daily,
            'byMethod'  => $byMethod,
            'byCashier' => $byCashier,
            'totalSales'=> $totalSales,
            'totalItems'=> $totalItems,
            'paymentMethods' => Sale::PAYMENT_METHODS,
        ]);
    }

    // ---------- GET /reports/profit ----------
    public function profit(): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        $fromDt = "$from 00:00:00";
        $toDt   = "$to 23:59:59";

        $revenue   = Sale::sumBetween($branchId, $fromDt, $toDt);
        $gross     = Sale::profitBetween($branchId, $fromDt, $toDt);
        $expenses  = Expense::sumBetween($branchId, $fromDt, $toDt);
        $net       = $gross - $expenses;

        $daily = Database::select(
            "SELECT DATE(sale_date) AS day,
                    SUM(total_amount) AS revenue,
                    SUM((selling_price_at_sale - purchase_price_at_sale) * quantity_sold - discount) AS gross_profit
             FROM sales
             WHERE branch_id = ? AND sale_date BETWEEN ? AND ?
             GROUP BY DATE(sale_date)
             ORDER BY day DESC",
            [$branchId, $fromDt, $toDt]
        );

        $expensesByGroup = Expense::byGroup($branchId, $fromDt, $toDt);

        view('reports/profit', [
            'layout'     => 'app',
            'pageTitle'  => 'Profit Report',
            'user'       => Auth::user(),
            'from'       => $from,
            'to'         => $to,
            'revenue'    => $revenue,
            'gross'      => $gross,
            'expenses'   => $expenses,
            'net'        => $net,
            'daily'      => $daily,
            'expensesByGroup' => $expensesByGroup,
        ]);
    }

    // ---------- GET /reports/expenses ----------
    public function expenses(): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        $fromDt = "$from 00:00:00";
        $toDt   = "$to 23:59:59";

        $groups = Expense::byGroup($branchId, $fromDt, $toDt);
        $total  = Expense::sumBetween($branchId, $fromDt, $toDt);

        $daily = Database::select(
            "SELECT DATE(expense_date) AS day, COUNT(*) AS entries, SUM(amount) AS total
             FROM expenses
             WHERE branch_id = ? AND expense_date BETWEEN ? AND ?
             GROUP BY DATE(expense_date)
             ORDER BY day DESC",
            [$branchId, $fromDt, $toDt]
        );

        view('reports/expenses', [
            'layout'  => 'app',
            'pageTitle' => 'Expense Report',
            'user'    => Auth::user(),
            'from'    => $from,
            'to'      => $to,
            'groups'  => $groups,
            'total'   => $total,
            'daily'   => $daily,
        ]);
    }

    // ---------- GET /reports/top-products ----------
    public function topProducts(): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        $fromDt = "$from 00:00:00";
        $toDt   = "$to 23:59:59";

        $products = Database::select(
            "SELECT product_name,
                    SUM(quantity_sold) AS qty,
                    SUM(total_amount) AS revenue,
                    SUM((selling_price_at_sale - purchase_price_at_sale) * quantity_sold) AS profit,
                    COUNT(*) AS times_sold
             FROM sales
             WHERE branch_id = ? AND sale_date BETWEEN ? AND ?
             GROUP BY product_name
             ORDER BY qty DESC
             LIMIT 50",
            [$branchId, $fromDt, $toDt]
        );

        view('reports/top_products', [
            'layout'    => 'app',
            'pageTitle' => 'Top Products',
            'user'      => Auth::user(),
            'from'      => $from,
            'to'        => $to,
            'products'  => $products,
        ]);
    }

    // ---------- GET /reports/staff-costs ----------
    public function staffCosts(): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();
        $month = $_GET['month'] ?? date('F');
        $year  = (int)($_GET['year'] ?? date('Y'));

        // From salary payments
        $salaryTotal = SalaryPayment::getTotalSalary($branchId, $month, $year);
        $poshoTotal  = SalaryPayment::getTotalPosho($branchId, $month, $year);

        // From expense categories STAFF_*
        $fromDate = date('Y-m-01', strtotime("$month 1 $year"));
        $toDate   = date('Y-m-t', strtotime("$month 1 $year"));

        $staffExpenses = Database::select(
            "SELECT category, SUM(amount) AS total, COUNT(*) AS cnt
             FROM expenses
             WHERE branch_id = ? AND category LIKE 'STAFF%' AND expense_date BETWEEN ? AND ?
             GROUP BY category ORDER BY total DESC",
            [$branchId, "$fromDate 00:00:00", "$toDate 23:59:59"]
        );

        view('reports/staff_costs', [
            'layout'       => 'app',
            'pageTitle'    => 'Staff Costs',
            'user'         => Auth::user(),
            'month'        => $month,
            'year'         => $year,
            'salaryTotal'  => $salaryTotal,
            'poshoTotal'   => $poshoTotal,
            'staffExpenses'=> $staffExpenses,
        ]);
    }

    // ---------- GET /reports/pl ----------
    public function profitAndLoss(): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        $fromDt = "$from 00:00:00";
        $toDt   = "$to 23:59:59";

        // Revenue
        $revenue = Sale::sumBetween($branchId, $fromDt, $toDt);

        // COGS (purchase price × qty sold)
        $cogs = (float) Database::scalar(
            "SELECT COALESCE(SUM(purchase_price_at_sale * quantity_sold), 0)
             FROM sales WHERE branch_id = ? AND sale_date BETWEEN ? AND ?",
            [$branchId, $fromDt, $toDt]
        );

        // Gross profit
        $grossProfit = $revenue - $cogs;

        // Expenses by group
        $expensesByGroup = Expense::byGroup($branchId, $fromDt, $toDt);
        $totalExpenses = 0;
        foreach ($expensesByGroup as $g) $totalExpenses += $g['total'];

        // Net profit
        $netProfit = $grossProfit - $totalExpenses;

        view('reports/pl', [
            'layout'  => 'app',
            'pageTitle' => 'Profit & Loss',
            'user'    => Auth::user(),
            'from'    => $from,
            'to'      => $to,
            'revenue' => $revenue,
            'cogs'    => $cogs,
            'grossProfit' => $grossProfit,
            'expensesByGroup' => $expensesByGroup,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
        ]);
    }

    // ---------- GET /reports/export (CSV) ----------
    public function export(): void
    {
        // Suppress any error output that could pollute the CSV
        ini_set('display_errors', '0');
        error_reporting(0);

        if (!Auth::check()) redirect('/login');

        // Discard any buffered output (whitespace, warnings, etc.)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $branchId = auth_branch();
        $type = $_GET['type'] ?? 'sales';
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        $fromDt = "$from 00:00:00";
        $toDt   = "$to 23:59:59";

        // Build CSV in memory first
        $csvContent = $this->buildCsv($type, $branchId, $fromDt, $toDt);

        if ($csvContent === null) {
            http_response_code(400);
            echo "Unknown export type: $type";
            exit;
        }

        // Prepend UTF-8 BOM for Excel
        $csvContent = "\xEF\xBB\xBF" . $csvContent;

        $filename = "kodafirca-{$type}-{$from}-to-{$to}.csv";

        // Send download headers
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csvContent));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $csvContent;
        exit;
    }

    private function buildCsv(string $type, int $branchId, string $fromDt, string $toDt): ?string
    {
        $fp = fopen('php://temp', 'w+');
        if (!$fp) return null;

        if ($type === 'sales') {
            fputcsv($fp, ['Sale ID', 'Date', 'Product', 'Qty', 'Unit Price', 'Discount', 'Total', 'Payment', 'Cashier', 'Customer']);
            $rows = Database::select(
                "SELECT s.sale_id, s.sale_date, s.product_name, s.quantity_sold,
                        s.selling_price_at_sale, s.discount, s.total_amount,
                        s.payment_method, e.full_name AS cashier_name, s.customer_name
                 FROM sales s
                 LEFT JOIN employees e ON e.employee_id = s.employee_id
                 WHERE s.branch_id = ? AND s.sale_date BETWEEN ? AND ?
                 ORDER BY s.sale_date DESC",
                [$branchId, $fromDt, $toDt]
            );
            foreach ($rows as $r) {
                fputcsv($fp, [
                    $r['sale_id'], $r['sale_date'], $r['product_name'],
                    $r['quantity_sold'], $r['selling_price_at_sale'],
                    $r['discount'], $r['total_amount'], $r['payment_method'],
                    $r['cashier_name'] ?? '', $r['customer_name'] ?? '',
                ]);
            }
        } elseif ($type === 'expenses') {
            fputcsv($fp, ['Expense ID', 'Date', 'Category', 'Description', 'Amount', 'EFD Receipt', 'EFD Number', 'Recorded By']);
            $rows = Database::select(
                "SELECT ex.expense_id, ex.expense_date, ex.category, ex.description,
                        ex.amount, ex.has_efd_receipt, ex.efd_receipt_number,
                        e.full_name AS employee_name
                 FROM expenses ex
                 LEFT JOIN employees e ON e.employee_id = ex.employee_id
                 WHERE ex.branch_id = ? AND ex.expense_date BETWEEN ? AND ?
                 ORDER BY ex.expense_date DESC",
                [$branchId, $fromDt, $toDt]
            );
            foreach ($rows as $r) {
                fputcsv($fp, [
                    $r['expense_id'], $r['expense_date'], $r['category'],
                    $r['description'], $r['amount'],
                    ((int)($r['has_efd_receipt'] ?? 0) === 1) ? 'YES' : 'NO',
                    $r['efd_receipt_number'] ?? '',
                    $r['employee_name'] ?? '',
                ]);
            }
        } elseif ($type === 'products') {
            fputcsv($fp, ['Product ID', 'Name', 'Category', 'Unit', 'Stock', 'Min Stock', 'Purchase Price', 'Selling Price', 'Stock Value']);
            $rows = Database::select(
                "SELECT product_id, name, category, unit_of_measure, quantity,
                        minimum_stock, purchase_price, selling_price
                 FROM products WHERE branch_id = ? ORDER BY name ASC",
                [$branchId]
            );
            foreach ($rows as $r) {
                fputcsv($fp, [
                    $r['product_id'], $r['name'], $r['category'] ?? '',
                    $r['unit_of_measure'], $r['quantity'], $r['minimum_stock'],
                    $r['purchase_price'], $r['selling_price'],
                    ((int)$r['quantity'] * (float)$r['selling_price']),
                ]);
            }
        } else {
            fclose($fp);
            return null;
        }

        rewind($fp);
        $content = stream_get_contents($fp);
        fclose($fp);
        return $content;
    }
}
