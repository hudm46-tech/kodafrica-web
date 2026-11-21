<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = substr($class, strlen($prefix));
    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) require $file;
});

$cfg = require dirname(__DIR__) . '/config/config.php';
date_default_timezone_set($cfg['app']['tz']);

if ($cfg['app']['debug']) { ini_set('display_errors', '1'); error_reporting(E_ALL); }
else { ini_set('display_errors', '0'); error_reporting(0); }

require dirname(__DIR__) . '/app/Core/Helpers.php';

use App\Core\Session;
use App\Core\Csrf;
use App\Core\Router;

Session::start();
Csrf::check();

// Buffer all output to prevent accidental header pollution
ob_start();

$router = new Router();

$router->get('/',              [\App\Controllers\HomeController::class, 'index']);
$router->get('/login',         [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login',        [\App\Controllers\AuthController::class, 'login']);
$router->get('/register',      [\App\Controllers\AuthController::class, 'showRegister']);
$router->post('/register',     [\App\Controllers\AuthController::class, 'register']);
$router->post('/logout',       [\App\Controllers\AuthController::class, 'logout']);
$router->get('/setup/branch',  [\App\Controllers\OwnerSetupController::class, 'showBranchForm']);
$router->post('/setup/branch', [\App\Controllers\OwnerSetupController::class, 'saveBranch']);
$router->get('/dashboard',     [\App\Controllers\DashboardController::class, 'index']);

// Branch picker + management
$router->get('/select-branch',              [\App\Controllers\BranchController::class, 'select']);
$router->post('/select-branch/{id}',        [\App\Controllers\BranchController::class, 'choose']);
$router->get('/branches',                   [\App\Controllers\BranchController::class, 'index']);
$router->get('/branches/create',            [\App\Controllers\BranchController::class, 'create']);
$router->post('/branches',                  [\App\Controllers\BranchController::class, 'store']);
$router->get('/branches/{id}/edit',         [\App\Controllers\BranchController::class, 'edit']);
$router->post('/branches/{id}',             [\App\Controllers\BranchController::class, 'update']);
$router->post('/branches/{id}/toggle',      [\App\Controllers\BranchController::class, 'toggle']);

// Profile (all logged-in users)
$router->get('/profile',              [\App\Controllers\ProfileController::class, 'show']);
$router->post('/profile/info',        [\App\Controllers\ProfileController::class, 'updateInfo']);
$router->post('/profile/password',    [\App\Controllers\ProfileController::class, 'updatePassword']);

$router->get('/expenses',               [\App\Controllers\ExpenseController::class, 'index']);
$router->get('/expenses/create',        [\App\Controllers\ExpenseController::class, 'create']);
$router->post('/expenses',              [\App\Controllers\ExpenseController::class, 'store']);
$router->get('/expenses/summary',       [\App\Controllers\ExpenseController::class, 'summary']);
$router->get('/expenses/{id}/edit',     [\App\Controllers\ExpenseController::class, 'edit']);
$router->post('/expenses/{id}',         [\App\Controllers\ExpenseController::class, 'update']);
$router->post('/expenses/{id}/delete',  [\App\Controllers\ExpenseController::class, 'destroy']);

$router->get('/products',               [\App\Controllers\ProductController::class, 'index']);
$router->get('/products/create',        [\App\Controllers\ProductController::class, 'create']);
$router->post('/products',              [\App\Controllers\ProductController::class, 'store']);
$router->get('/products/{id}/edit',     [\App\Controllers\ProductController::class, 'edit']);
$router->post('/products/{id}',         [\App\Controllers\ProductController::class, 'update']);
$router->post('/products/{id}/adjust',  [\App\Controllers\ProductController::class, 'adjustStock']);
$router->post('/products/{id}/delete',  [\App\Controllers\ProductController::class, 'destroy']);

$router->get('/pos',                    [\App\Controllers\PosController::class, 'index']);
$router->post('/pos/add',               [\App\Controllers\PosController::class, 'add']);
$router->post('/pos/update',            [\App\Controllers\PosController::class, 'update']);
$router->post('/pos/remove',            [\App\Controllers\PosController::class, 'remove']);
$router->post('/pos/clear',             [\App\Controllers\PosController::class, 'clear']);
$router->post('/pos/checkout',          [\App\Controllers\PosController::class, 'checkout']);
$router->get('/pos/receipt',            [\App\Controllers\PosController::class, 'receipt']);

$router->get('/sales',                  [\App\Controllers\SaleController::class, 'index']);
$router->get('/sales/{ts}',             [\App\Controllers\SaleController::class, 'show']);
$router->post('/sales/mark-paid',       [\App\Controllers\SaleController::class, 'markPaid']);

$router->get('/reports',                [\App\Controllers\ReportController::class, 'index']);
$router->get('/reports/sales',          [\App\Controllers\ReportController::class, 'sales']);
$router->get('/reports/profit',         [\App\Controllers\ReportController::class, 'profit']);
$router->get('/reports/expenses',       [\App\Controllers\ReportController::class, 'expenses']);
$router->get('/reports/top-products',   [\App\Controllers\ReportController::class, 'topProducts']);
$router->get('/reports/staff-costs',    [\App\Controllers\ReportController::class, 'staffCosts']);
$router->get('/reports/pl',             [\App\Controllers\ReportController::class, 'profitAndLoss']);
$router->get('/reports/export',         [\App\Controllers\ReportController::class, 'export']);

$router->get('/employees',                    [\App\Controllers\EmployeeController::class, 'index']);
$router->get('/employees/create',             [\App\Controllers\EmployeeController::class, 'create']);
$router->post('/employees',                   [\App\Controllers\EmployeeController::class, 'store']);
$router->get('/employees/{id}/edit',          [\App\Controllers\EmployeeController::class, 'edit']);
$router->post('/employees/{id}',              [\App\Controllers\EmployeeController::class, 'update']);
$router->post('/employees/{id}/toggle',       [\App\Controllers\EmployeeController::class, 'toggle']);
$router->post('/employees/{id}/reset-pass',   [\App\Controllers\EmployeeController::class, 'resetPassword']);

$router->get('/salaries',                     [\App\Controllers\SalaryController::class, 'index']);
$router->get('/salaries/create',              [\App\Controllers\SalaryController::class, 'create']);
$router->post('/salaries',                    [\App\Controllers\SalaryController::class, 'store']);
$router->get('/salaries/{id}',                [\App\Controllers\SalaryController::class, 'show']);
$router->post('/salaries/{id}/delete',        [\App\Controllers\SalaryController::class, 'destroy']);

$router->get('/settings',                     [\App\Controllers\SettingsController::class, 'index']);
$router->post('/settings/business',           [\App\Controllers\SettingsController::class, 'saveBusiness']);
$router->post('/settings/logo',               [\App\Controllers\SettingsController::class, 'uploadLogo']);
$router->post('/settings/tax',                [\App\Controllers\SettingsController::class, 'saveTax']);
$router->post('/settings/password',           [\App\Controllers\SettingsController::class, 'changePassword']);
$router->get('/settings/backup',              [\App\Controllers\SettingsController::class, 'downloadBackup']);

$router->get('/tax',                          [\App\Controllers\TaxController::class, 'index']);
$router->get('/tax/generate',                 [\App\Controllers\TaxController::class, 'showGenerate']);
$router->post('/tax/generate',                [\App\Controllers\TaxController::class, 'generate']);
$router->get('/tax/{id}',                     [\App\Controllers\TaxController::class, 'show']);
$router->post('/tax/{id}/delete',             [\App\Controllers\TaxController::class, 'destroy']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
