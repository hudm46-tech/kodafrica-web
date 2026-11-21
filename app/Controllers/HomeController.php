<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Database;

final class HomeController
{
    public function index(): void
    {
        $employeeCount = (int) Database::scalar("SELECT COUNT(*) FROM employees");

        // Only show real business name if it's been customized by owner
        $raw = (string) (Database::scalar("SELECT business_name FROM app_settings WHERE id=1") ?: '');
        $defaultNames = ['Kodafirca POS', 'Kodafirca Msata Hardware', ''];
        $businessName = in_array(trim($raw), $defaultNames, true) ? 'Point of Sale System' : $raw;

        view('splash', [
            'layout'        => 'splash',
            'pageTitle'     => 'Kodafirca POS',
            'employeeCount' => $employeeCount,
            'businessName'  => $businessName,
        ]);
    }
}
