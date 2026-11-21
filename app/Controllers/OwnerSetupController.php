<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Validator;
use App\Models\Branch;
use App\Models\User;

final class OwnerSetupController
{
    public function showBranchForm(): void
    {
        if (!Auth::check()) redirect('/login');
        $user = Auth::user();
        if ($user['role'] !== 'ADMIN') { http_response_code(403); die('Forbidden'); }
        if ((int)$user['branch_id'] > 0) redirect('/dashboard');

        view('setup/branch', [
            'layout'    => 'auth',
            'pageTitle' => 'Add Your First Branch',
        ]);
    }

    public function saveBranch(): void
    {
        if (!Auth::check()) redirect('/login');
        $user = Auth::user();
        if ($user['role'] !== 'ADMIN') { http_response_code(403); die('Forbidden'); }

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
            redirect('/setup/branch');
        }

        try {
            Database::begin();
            $branchId = Branch::create($data);
            User::setBranch((int)$user['employee_id'], $branchId);
            $this->seedSampleProducts($branchId);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollback();
            Flash::error('Save failed: ' . $e->getMessage());
            redirect('/setup/branch');
        }

        // Do NOT set session branch - admin will pick from list
        Flash::success('Branch created! Please choose your branch.');
        redirect('/select-branch');
    }

    private function seedSampleProducts(int $branchId): void
    {
        $items = [
            // [name, unit, qty, purchase, selling, category]
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
