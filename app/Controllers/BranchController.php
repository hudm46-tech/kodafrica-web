<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;

final class BranchController
{
    // GET /select-branch - picker for admin
    public function select(): void
    {
        if (!Auth::check()) redirect('/login');
        if (($_SESSION['role'] ?? '') !== 'ADMIN') redirect('/dashboard');

        $branches = Branch::listAll(false);

        // Per-branch stats
        foreach ($branches as &$b) {
            $b['today_sales'] = Sale::sumBetween(
                (int)$b['branch_id'],
                date('Y-m-d 00:00:00'),
                now()
            );
            $b['product_count'] = Product::countByBranch((int)$b['branch_id']);
        }
        unset($b);

        view('branches/select', [
            'layout'    => 'auth',
            'pageTitle' => 'Choose Branch',
            'user'      => Auth::user(),
            'branches'  => $branches,
        ]);
    }

    // POST /select-branch/{id}
    public function choose(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        if (($_SESSION['role'] ?? '') !== 'ADMIN') redirect('/dashboard');

        $branch = Branch::find($id);
        if (!$branch || (int)$branch['is_active'] !== 1) {
            Flash::error('That branch is unavailable.');
            redirect('/select-branch');
        }

        Session::put('branch_id', $id);
        Flash::success('Welcome to ' . $branch['branch_name']);
        redirect('/dashboard');
    }

    // GET /branches - management list
    public function index(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $branches = Branch::listAll(false);
        $currentBranch = (int)($_SESSION['branch_id'] ?? 0);

        view('branches/index', [
            'layout'    => 'app',
            'pageTitle' => 'Branches',
            'user'      => Auth::user(),
            'branches'  => $branches,
            'currentBranch' => $currentBranch,
            'topbarAction' => '<a href="' . url('/branches/create') . '" class="topbar-action">'
                . '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>'
                . 'Add Branch</a>',
        ]);
    }

    // GET /branches/create
    public function create(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        view('branches/form', [
            'layout'    => 'app',
            'pageTitle' => 'Add Branch',
            'user'      => Auth::user(),
            'branch'    => null,
        ]);
    }

    // POST /branches
    public function store(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $data = [
            'branch_name'         => trim($_POST['branch_name'] ?? ''),
            'location'            => trim($_POST['location'] ?? ''),
            'phone_number'        => trim($_POST['phone_number'] ?? ''),
            'tin_number'          => trim($_POST['tin_number'] ?? ''),
            'license_number'      => trim($_POST['license_number'] ?? ''),
            'license_expiry_date' => trim($_POST['license_expiry_date'] ?? ''),
        ];

        $v = new Validator($data);
        $v->required('branch_name', 'Branch name')
          ->required('location', 'Location')
          ->required('phone_number', 'Phone')
          ->required('license_number', 'License number')
          ->date('license_expiry_date', 'License expiry');
        if ($v->fails()) {
            foreach ($v->errors() as $msg) Flash::error($msg);
            $_SESSION['_old'] = $data;
            redirect('/branches/create');
        }

        try {
            $branchId = Branch::create($data);
            Product::seedDefaults((int)$branchId);

            Flash::success('Branch created: ' . $data['branch_name']);
            redirect('/select-branch');
        } catch (\Throwable $e) {
            Flash::error('Create failed: ' . $e->getMessage());
            $_SESSION['_old'] = $data;
            redirect('/branches/create');
        }
    }

    // GET /branches/{id}/edit
    public function edit(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $branch = Branch::find($id);
        if (!$branch) {
            Flash::error('Branch not found.');
            redirect('/branches');
        }

        view('branches/form', [
            'layout'    => 'app',
            'pageTitle' => 'Edit Branch',
            'user'      => Auth::user(),
            'branch'    => $branch,
        ]);
    }

    // POST /branches/{id}
    public function update(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $branch = Branch::find($id);
        if (!$branch) {
            Flash::error('Branch not found.');
            redirect('/branches');
        }

        $data = [
            'branch_name'         => trim($_POST['branch_name'] ?? ''),
            'location'            => trim($_POST['location'] ?? ''),
            'phone_number'        => trim($_POST['phone_number'] ?? ''),
            'tin_number'          => trim($_POST['tin_number'] ?? ''),
            'license_number'      => trim($_POST['license_number'] ?? ''),
            'license_expiry_date' => trim($_POST['license_expiry_date'] ?? ''),
        ];

        $v = new Validator($data);
        $v->required('branch_name', 'Branch name')
          ->required('location', 'Location')
          ->required('phone_number', 'Phone')
          ->required('license_number', 'License number')
          ->date('license_expiry_date', 'License expiry');
        if ($v->fails()) {
            foreach ($v->errors() as $msg) Flash::error($msg);
            $_SESSION['_old'] = $data;
            redirect('/branches/' . $id . '/edit');
        }

        Branch::update($id, $data);
        Flash::success('Branch updated.');
        redirect('/branches');
    }

    // POST /branches/{id}/toggle
    public function toggle(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $branch = Branch::find($id);
        if (!$branch) {
            Flash::error('Branch not found.');
            redirect('/branches');
        }

        $new = (int)$branch['is_active'] === 1 ? 0 : 1;
        Branch::toggle($id, (bool)$new);

        Flash::success(($new ? 'Activated: ' : 'Deactivated: ') . $branch['branch_name']);
        redirect('/branches');
    }
}
