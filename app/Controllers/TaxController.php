<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Models\TaxSetting;
use App\Models\TaxReportHistory;
use App\Models\Sale;
use App\Models\Expense;

final class TaxController
{
    // ---------- GET /tax ----------
    public function index(): void
    {
        if (!Auth::check()) redirect('/login');

        $branchId = auth_branch();
        $settings = TaxSetting::current();
        $history  = TaxReportHistory::listByBranch($branchId);

        view('tax/index', [
            'layout'    => 'app',
            'pageTitle' => 'Tax',
            'user'      => Auth::user(),
            'settings'  => $settings,
            'history'   => $history,
            'isAdmin'   => auth_role() === 'ADMIN',
            'topbarAction' => '<a href="' . url('/tax/generate') . '" class="topbar-action">'
                . '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>'
                . 'Generate Report</a>',
        ]);
    }

    // ---------- GET /tax/generate ----------
    public function showGenerate(): void
    {
        if (!Auth::check()) redirect('/login');

        view('tax/generate', [
            'layout'    => 'app',
            'pageTitle' => 'Generate Tax Report',
            'user'      => Auth::user(),
            'settings'  => TaxSetting::current(),
        ]);
    }

    // ---------- POST /tax/generate ----------
    public function generate(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');

        $branchId  = auth_branch();
        $year      = (int)($_POST['year'] ?? date('Y'));
        $month     = (int)($_POST['month'] ?? 0); // 0 = full year, 1-12 = month

        $settings = TaxSetting::current();
        if (!$settings) {
            Flash::error('Tax settings missing. Configure in Settings first.');
            redirect('/tax');
        }

        // Determine date range
        if ($month > 0) {
            $from = sprintf('%04d-%02d-01 00:00:00', $year, $month);
            $to   = date('Y-m-t 23:59:59', strtotime($from));
            $label = date('F Y', strtotime($from));
        } else {
            $from = sprintf('%04d-01-01 00:00:00', $year);
            $to   = sprintf('%04d-12-31 23:59:59', $year);
            $label = "Year $year";
        }

        // Financial data
        $totalSales = Sale::sumBetween($branchId, $from, $to);
        $cogs = (float) Database::scalar(
            "SELECT COALESCE(SUM(purchase_price_at_sale * quantity_sold), 0)
             FROM sales WHERE branch_id = ? AND sale_date BETWEEN ? AND ?",
            [$branchId, $from, $to]
        );
        $totalExpenses = Expense::sumBetween($branchId, $from, $to);
        $grossProfit = $totalSales - $cogs;
        $netProfit = $grossProfit - $totalExpenses;

        // Turnover for presumptive (annualized if monthly)
        $turnover = $totalSales;
        if ($month > 0) {
            // Estimate annual turnover from this month
            $turnover = $totalSales * 12;
        }

        $entityType = $settings['entity_type'];
        $taxAmount = 0;
        $regimeUsed = '';
        $bracketUsed = null;
        $corpRateUsed = null;
        $presumpRateUsed = null;
        $vatRateUsed = null;
        $vatAmount = 0;
        $vatRequired = false;

        if ($entityType === 'SOLE_PROPRIETOR') {
            // ---- Presumptive Tax ----
            $regimeUsed = 'PRESUMPTIVE';
            $t1 = (float)$settings['presumptive_threshold1'];
            $t2 = (float)$settings['presumptive_threshold2'];
            $t3 = (float)$settings['presumptive_threshold3'];
            $t4 = (float)$settings['presumptive_threshold4'];

            $fee3 = (float)$settings['presumptive_fixed_fee3'];
            $fee4 = (float)$settings['presumptive_fixed_fee4'];

            if ($turnover <= $t1) {
                $taxAmount = 0;
                $bracketUsed = 1;
            } elseif ($turnover <= $t2) {
                $rate = (float)$settings['presumptive_rate2'];
                $taxAmount = $turnover * ($rate / 100);
                $presumpRateUsed = $rate;
                $bracketUsed = 2;
            } elseif ($turnover <= $t3) {
                $rate = (float)$settings['presumptive_rate3'];
                $taxAmount = $fee3 + (($turnover - $t2) * ($rate / 100));
                $presumpRateUsed = $rate;
                $bracketUsed = 3;
            } elseif ($turnover <= $t4) {
                $rate = (float)$settings['presumptive_rate4'];
                $taxAmount = $fee4 + (($turnover - $t3) * ($rate / 100));
                $presumpRateUsed = $rate;
                $bracketUsed = 4;
            } else {
                // Above threshold → corporate tax applies
                $regimeUsed = 'CORPORATE (above presumptive threshold)';
                $rate = (float)$settings['corporate_rate'];
                $taxAmount = $netProfit * ($rate / 100);
                $corpRateUsed = $rate;
            }

            // VAT check
            if ((float)$settings['vat_registration_threshold'] > 0
                && $turnover >= (float)$settings['vat_registration_threshold']) {
                $vatRequired = true;
                $vatRateUsed = (float)$settings['vat_rate'];
                $vatAmount = $totalSales * ($vatRateUsed / (100 + $vatRateUsed)); // extract VAT
            }

        } else {
            // ---- Corporate Tax ----
            $regimeUsed = 'CORPORATE';
            $rate = (float)$settings['corporate_rate'];
            $taxAmount = max(0, $netProfit) * ($rate / 100);
            $corpRateUsed = $rate;

            if ((float)$settings['vat_registration_threshold'] > 0
                && $totalSales >= (float)$settings['vat_registration_threshold']) {
                $vatRequired = true;
                $vatRateUsed = (float)$settings['vat_rate'];
                $vatAmount = $totalSales * ($vatRateUsed / (100 + $vatRateUsed));
            }
        }

        // Save snapshot
        $reportId = TaxReportHistory::create([
            'report_year'     => $year,
            'report_month'    => $month,
            'branch_id'       => $branchId,
            'entity_type'     => $entityType,
            'regime_used'     => $regimeUsed,
            'total_sales'     => $totalSales,
            'total_expenses'  => $totalExpenses,
            'cost_of_goods_sold' => $cogs,
            'gross_profit'    => $grossProfit,
            'net_profit'      => $netProfit,
            'annual_turnover' => $turnover,
            'estimated_tax'   => $taxAmount,
            'vat_amount'      => $vatAmount,
            'vat_required'    => $vatRequired ? 1 : 0,
            'corporate_rate_used'    => $corpRateUsed,
            'presumptive_rate_used'  => $presumpRateUsed,
            'vat_rate_used'          => $vatRateUsed,
            'presumptive_bracket_used'=> $bracketUsed,
            'generated_by'    => Auth::user()['full_name'] ?? 'SYSTEM',
            'notes'           => "$label report",
        ]);

        Flash::success('Tax report generated.');
        redirect('/tax/' . $reportId);
    }

    // ---------- GET /tax/{id} ----------
    public function show(int $id): void
    {
        if (!Auth::check()) redirect('/login');

        $report = TaxReportHistory::find($id);
        if (!$report || ((int)$report['branch_id'] !== auth_branch() && (int)$report['branch_id'] !== 0)) {
            Flash::error('Report not found.');
            redirect('/tax');
        }

        view('tax/report', [
            'layout'    => 'receipt',
            'pageTitle' => 'Tax Report',
            'report'    => $report,
            'business'  => Database::first("SELECT * FROM app_settings WHERE id = 1"),
        ]);
    }

    // ---------- POST /tax/{id}/delete ----------
    public function destroy(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $report = TaxReportHistory::find($id);
        if (!$report || (int)$report['branch_id'] !== auth_branch()) {
            Flash::error('Report not found.');
            redirect('/tax');
        }

        TaxReportHistory::delete($id);
        Flash::success('Tax report deleted.');
        redirect('/tax');
    }
}
