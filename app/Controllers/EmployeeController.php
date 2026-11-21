<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Validator;
use App\Models\User;

final class EmployeeController
{
    public function index(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');

        $branchId = auth_branch();
        $showInactive = !empty($_GET['inactive']);

        $employees = User::listByBranch($branchId, !$showInactive);

        // Counts
        $activeCount = count(User::listByBranch($branchId, true));

        view('employees/index', [
            'layout'    => 'app',
            'pageTitle' => 'Staff',
            'user'      => Auth::user(),
            'employees' => $employees,
            'activeCount' => $activeCount,
            'showInactive' => $showInactive,
            'roles' => User::ROLES,
            'topbarAction' => auth_role() === 'ADMIN'
                ? '<a href="' . url('/employees/create') . '" class="topbar-action">'
                    . '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>'
                    . 'New Employee</a>'
                : '',
        ]);
    }

    public function create(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        view('employees/form', [
            'layout'    => 'app',
            'pageTitle' => 'New Employee',
            'user'      => Auth::user(),
            'employee'  => null,
            'roles'     => User::ROLES,
        ]);
    }

    public function store(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $data = [
            'full_name'       => trim($_POST['full_name'] ?? ''),
            'phone_number'    => trim($_POST['phone_number'] ?? ''),
            'email'           => trim($_POST['email'] ?? ''),
            'password'        => (string)($_POST['password'] ?? ''),
            'role'            => $_POST['role'] ?? 'CASHIER',
            'employment_type' => $_POST['employment_type'] ?? 'PERMANENT',
            'monthly_salary'  => (float)($_POST['monthly_salary'] ?? 0),
            'daily_rate'      => (float)($_POST['daily_rate'] ?? 0),
            'hire_date'       => trim($_POST['hire_date'] ?? date('Y-m-d')),
            'nssf_number'     => trim($_POST['nssf_number'] ?? ''),
            'notes'           => trim($_POST['notes'] ?? ''),
        ];

        $v = new Validator($data);
        $v->required('full_name', 'Full name')
          ->required('phone_number', 'Phone number')
          ->min('phone_number', 9, 'Phone number')
          ->email('email', 'Email')
          ->min('password', 6, 'Password');

        if (!isset(User::ROLES[$data['role']])) {
            Flash::error('Invalid role.');
            redirect('/employees/create');
        }

        if ($v->fails()) {
            foreach ($v->errors() as $msg) Flash::error($msg);
            $_SESSION['_old'] = $data;
            redirect('/employees/create');
        }

        if (User::findByPhone($data['phone_number'])) {
            Flash::error('That phone number is already registered.');
            $_SESSION['_old'] = $data;
            redirect('/employees/create');
        }

        $data['branch_id'] = auth_branch();
        User::create($data);

        Flash::success('Employee added: ' . $data['full_name']);
        redirect('/employees');
    }

    public function edit(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $employee = User::find($id);
        if (!$employee || (int)$employee['branch_id'] !== auth_branch()) {
            Flash::error('Employee not found.');
            redirect('/employees');
        }

        view('employees/form', [
            'layout'    => 'app',
            'pageTitle' => 'Edit Employee',
            'user'      => Auth::user(),
            'employee'  => $employee,
            'roles'     => User::ROLES,
        ]);
    }

    public function update(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $employee = User::find($id);
        if (!$employee || (int)$employee['branch_id'] !== auth_branch()) {
            Flash::error('Employee not found.');
            redirect('/employees');
        }

        // Prevent changing own role away from ADMIN (safety)
        if ($id === (int)auth_id() && ($_POST['role'] ?? '') !== 'ADMIN') {
            Flash::error('You cannot change your own role away from Admin.');
            redirect('/employees/' . $id . '/edit');
        }

        $data = [
            'full_name'       => trim($_POST['full_name'] ?? ''),
            'phone_number'    => trim($_POST['phone_number'] ?? ''),
            'email'           => trim($_POST['email'] ?? ''),
            'password'        => (string)($_POST['password'] ?? ''),
            'role'            => $_POST['role'] ?? $employee['role'],
            'employment_type' => $_POST['employment_type'] ?? $employee['employment_type'],
            'monthly_salary'  => (float)($_POST['monthly_salary'] ?? 0),
            'daily_rate'      => (float)($_POST['daily_rate'] ?? 0),
            'hire_date'       => trim($_POST['hire_date'] ?? $employee['hire_date']),
            'nssf_number'     => trim($_POST['nssf_number'] ?? ''),
            'notes'           => trim($_POST['notes'] ?? ''),
            'is_active'       => isset($_POST['is_active']) ? 1 : 0,
        ];

        $v = new Validator($data);
        $v->required('full_name', 'Full name')
          ->required('phone_number', 'Phone number')
          ->email('email', 'Email');

        if (!empty($data['password'])) {
            $v->min('password', 6, 'Password');
        }

        if (!isset(User::ROLES[$data['role']])) {
            Flash::error('Invalid role.');
            redirect('/employees/' . $id . '/edit');
        }

        if ($v->fails()) {
            foreach ($v->errors() as $msg) Flash::error($msg);
            $_SESSION['_old'] = $data;
            redirect('/employees/' . $id . '/edit');
        }

        // Phone uniqueness check (excluding self)
        $existing = User::findByPhone($data['phone_number']);
        if ($existing && (int)$existing['employee_id'] !== $id) {
            Flash::error('That phone number is used by another employee.');
            redirect('/employees/' . $id . '/edit');
        }

        User::update($id, $data);
        Flash::success('Employee updated.');
        redirect('/employees');
    }

    public function toggle(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        if ($id === (int)auth_id()) {
            Flash::error('You cannot deactivate yourself.');
            redirect('/employees');
        }

        $employee = User::find($id);
        if (!$employee || (int)$employee['branch_id'] !== auth_branch()) {
            Flash::error('Employee not found.');
            redirect('/employees');
        }

        $newState = (int)$employee['is_active'] === 1 ? 0 : 1;
        User::toggleActive($id, (bool)$newState);

        Flash::success(($newState ? 'Activated: ' : 'Deactivated: ') . $employee['full_name']);
        redirect('/employees');
    }

    public function resetPassword(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        if ($id === (int)auth_id()) {
            Flash::error('Use Settings to change your own password.');
            redirect('/employees');
        }

        $employee = User::find($id);
        if (!$employee || (int)$employee['branch_id'] !== auth_branch()) {
            Flash::error('Employee not found.');
            redirect('/employees');
        }

        $newPass = trim($_POST['new_password'] ?? '');
        if (strlen($newPass) < 6) {
            Flash::error('New password must be at least 6 characters.');
            redirect('/employees');
        }

        User::resetPassword($id, $newPass);
        Flash::success('Password reset for ' . $employee['full_name']);
        redirect('/employees');
    }
}
