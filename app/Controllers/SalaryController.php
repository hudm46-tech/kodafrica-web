<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Validator;
use App\Models\SalaryPayment;
use App\Models\User;
use App\Models\Branch;

final class SalaryController
{
    public function index(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');

        $branchId = auth_branch();
        $month = $_GET['month'] ?? date('F');
        $year  = (int)($_GET['year'] ?? date('Y'));

        if (!in_array($month, SalaryPayment::MONTHS, true)) $month = date('F');

        $payments = SalaryPayment::listByBranch($branchId, $month, $year);
        $totals   = SalaryPayment::totalsByBranch($branchId, $month, $year);

        view('salaries/index', [
            'layout'   => 'app',
            'pageTitle'=> 'Salaries',
            'user'     => Auth::user(),
            'payments' => $payments,
            'totals'   => $totals,
            'month'    => $month,
            'year'     => $year,
            'months'   => SalaryPayment::MONTHS,
            'types'    => SalaryPayment::TYPES,
            'topbarAction' => '<a href="' . url('/salaries/create') . '" class="topbar-action">'
                . '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>'
                . 'New Payment</a>',
        ]);
    }

    public function create(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');

        $branchId = auth_branch();
        $employees = User::listByBranch($branchId, true);

        view('salaries/form', [
            'layout'   => 'app',
            'pageTitle'=> 'New Payment',
            'user'     => Auth::user(),
            'employees'=> $employees,
            'payment'  => null,
            'months'   => SalaryPayment::MONTHS,
            'types'    => SalaryPayment::TYPES,
        ]);
    }

    public function store(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');

        $branchId = auth_branch();

        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $type = $_POST['payment_type'] ?? 'SALARY';
        $month = $_POST['period_month'] ?? date('F');
        $year = (int)($_POST['period_year'] ?? date('Y'));

        $employee = User::find($employeeId);
        if (!$employee || (int)$employee['branch_id'] !== $branchId) {
            Flash::error('Invalid employee.');
            redirect('/salaries/create');
        }
        if (!isset(SalaryPayment::TYPES[$type])) {
            Flash::error('Invalid payment type.');
            redirect('/salaries/create');
        }

        // Calculate amount based on type
        $baseAmount = 0; $daysWorked = 0; $dailyRate = 0;
        $overtimeAmount = 0; $overtimeHours = 0; $overtimeRate = 0;
        $bonus = 0; $deductions = 0;

        if ($type === 'SALARY') {
            $baseAmount = (float)$employee['monthly_salary'];
            // Allow override
            if (!empty($_POST['base_amount'])) $baseAmount = (float)$_POST['base_amount'];
        } elseif ($type === 'POSHO') {
            $daysWorked = (int)($_POST['days_worked'] ?? 0);
            $dailyRate  = (float)($employee['daily_rate'] ?: ($_POST['daily_rate'] ?? 0));
            $baseAmount = $daysWorked * $dailyRate;
        } else {
            // Overtime, Advance, Bonus, NSSF — manual amount
            $baseAmount = (float)($_POST['base_amount'] ?? 0);
        }

        $overtimeHours = (float)($_POST['overtime_hours'] ?? 0);
        $overtimeRate  = (float)($_POST['overtime_rate'] ?? 0);
        $overtimeAmount = $overtimeHours * $overtimeRate;

        $bonus      = (float)($_POST['bonus'] ?? 0);
        $deductions = (float)($_POST['deductions'] ?? 0);

        $amountPaid = $baseAmount + $overtimeAmount + $bonus - $deductions;

        if ($amountPaid <= 0) {
            Flash::error('Final amount must be greater than zero.');
            redirect('/salaries/create');
        }

        SalaryPayment::create([
            'employee_id'     => $employeeId,
            'branch_id'       => $branchId,
            'payment_type'    => $type,
            'period_month'    => $month,
            'period_year'     => $year,
            'days_worked'     => $daysWorked,
            'daily_rate'      => $dailyRate,
            'base_amount'     => $baseAmount,
            'overtime_hours'  => $overtimeHours,
            'overtime_rate'   => $overtimeRate,
            'overtime_amount' => $overtimeAmount,
            'bonus'           => $bonus,
            'deductions'      => $deductions,
            'amount_paid'     => $amountPaid,
            'payment_method'  => $_POST['payment_method'] ?? 'CASH',
            'payment_reference' => trim($_POST['payment_reference'] ?? ''),
            'notes'           => trim($_POST['notes'] ?? ''),
        ]);

        Flash::success('Payment recorded: ' . money($amountPaid) . ' to ' . $employee['full_name']);
        redirect('/salaries?month=' . urlencode($month) . '&year=' . $year);
    }

    public function show(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');

        $payment = SalaryPayment::withDetails($id);
        if (!$payment || (int)$payment['branch_id'] !== auth_branch()) {
            Flash::error('Payment not found.');
            redirect('/salaries');
        }
        $branch = Branch::find($payment['branch_id']);
        $business = Database::first("SELECT * FROM app_settings WHERE id = 1");

        view('salaries/payslip', [
            'layout'   => 'receipt',
            'pageTitle'=> 'Payslip',
            'payment'  => $payment,
            'branch'   => $branch,
            'business' => $business,
        ]);
    }

    public function destroy(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $payment = SalaryPayment::find($id);
        if (!$payment || (int)$payment['branch_id'] !== auth_branch()) {
            Flash::error('Payment not found.');
            redirect('/salaries');
        }
        SalaryPayment::delete($id);
        Flash::success('Payment deleted.');
        redirect('/salaries?month=' . urlencode($payment['period_month']) . '&year=' . $payment['period_year']);
    }
}
