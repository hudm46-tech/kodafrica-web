<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Validator;
use App\Models\Expense;

final class ExpenseController
{
    public function index(): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();
        $user = Auth::user();

        $from  = $_GET['from']  ?? date('Y-m-01');
        $to    = $_GET['to']    ?? date('Y-m-d');
        $group = $_GET['group'] ?? null;
        $efd   = $_GET['efd']   ?? null; // with | without | null

        $expenses = Expense::listByBranch($branchId, $from, $to, $group, $efd);
        $total    = array_sum(array_map(fn($e) => (float)$e['amount'], $expenses));
        $groups   = Expense::byGroup($branchId, $from, $to);
        $count    = count($expenses);

        // EFD totals
        $efdWithTotal    = Expense::sumWithEfd($branchId, $from . ' 00:00:00', $to . ' 23:59:59');
        $efdWithoutTotal = Expense::sumWithoutEfd($branchId, $from . ' 00:00:00', $to . ' 23:59:59');
        $efdWithCount    = Expense::countWithEfd($branchId, $from . ' 00:00:00', $to . ' 23:59:59');
        $efdWithoutCount = Expense::countWithoutEfd($branchId, $from . ' 00:00:00', $to . ' 23:59:59');

        view('expenses/index', [
            'layout'    => 'app',
            'pageTitle' => 'Expenses',
            'user'      => $user,
            'expenses'  => $expenses,
            'total'     => $total,
            'groups'    => $groups,
            'count'     => $count,
            'from'      => $from,
            'to'        => $to,
            'activeGroup' => $group,
            'activeEfd' => $efd,
            'categoryGroups' => Expense::CATEGORIES,
            'efdWithTotal'    => $efdWithTotal,
            'efdWithoutTotal' => $efdWithoutTotal,
            'efdWithCount'    => $efdWithCount,
            'efdWithoutCount' => $efdWithoutCount,
            'topbarAction' => '<a href="' . url('/expenses/create') . '" class="topbar-action">'
                . '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>'
                . 'New Expense</a>',
        ]);
    }

    public function create(): void
    {
        if (!Auth::check()) redirect('/login');
        view('expenses/form', [
            'layout'    => 'app',
            'pageTitle' => 'New Expense',
            'user'      => Auth::user(),
            'expense'   => null,
            'categories' => Expense::CATEGORIES,
        ]);
    }

    public function store(): void
    {
        if (!Auth::check()) redirect('/login');

        $data = [
            'category'     => trim($_POST['category'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'amount'       => (float)($_POST['amount'] ?? 0),
            'expense_date' => trim($_POST['expense_date'] ?? ''),
            'notes'        => trim($_POST['notes'] ?? ''),
            'has_efd_receipt' => isset($_POST['has_efd_receipt']) ? 1 : 0,
            'efd_receipt_number' => trim($_POST['efd_receipt_number'] ?? ''),
        ];

        $v = new Validator($data);
        $v->required('category', 'Category')
          ->required('description', 'Description')
          ->numeric('amount', 'Amount')
          ->required('expense_date', 'Date');

        if ($data['amount'] <= 0) {
            Flash::error('Amount must be greater than zero.');
            $_SESSION['_old'] = $data;
            redirect('/expenses/create');
        }

        if ($v->fails()) {
            foreach ($v->errors() as $msg) Flash::error($msg);
            $_SESSION['_old'] = $data;
            redirect('/expenses/create');
        }

        $receiptPath = $this->handleReceipt(null);
        if ($receiptPath === false) {
            $_SESSION['_old'] = $data;
            redirect('/expenses/create');
        }

        $data['branch_id'] = auth_branch();
        $data['receipt_image_path'] = $receiptPath;
        Expense::create($data);

        Flash::success('Expense recorded: ' . money($data['amount']));
        redirect('/expenses');
    }

    public function edit(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        $expense = Expense::find($id);
        if (!$expense || (int)$expense['branch_id'] !== auth_branch()) {
            Flash::error('Expense not found.');
            redirect('/expenses');
        }
        view('expenses/form', [
            'layout'    => 'app',
            'pageTitle' => 'Edit Expense',
            'user'      => Auth::user(),
            'expense'   => $expense,
            'categories' => Expense::CATEGORIES,
        ]);
    }

    public function update(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        $expense = Expense::find($id);
        if (!$expense || (int)$expense['branch_id'] !== auth_branch()) {
            Flash::error('Expense not found.');
            redirect('/expenses');
        }

        $data = [
            'category'     => trim($_POST['category'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'amount'       => (float)($_POST['amount'] ?? 0),
            'expense_date' => trim($_POST['expense_date'] ?? ''),
            'notes'        => trim($_POST['notes'] ?? ''),
            'has_efd_receipt' => isset($_POST['has_efd_receipt']) ? 1 : 0,
            'efd_receipt_number' => trim($_POST['efd_receipt_number'] ?? ''),
        ];

        $v = new Validator($data);
        $v->required('category', 'Category')
          ->required('description', 'Description')
          ->numeric('amount', 'Amount')
          ->required('expense_date', 'Date');
        if ($v->fails() || $data['amount'] <= 0) {
            foreach ($v->errors() as $msg) Flash::error($msg);
            if ($data['amount'] <= 0) Flash::error('Amount must be greater than zero.');
            $_SESSION['_old'] = $data;
            redirect('/expenses/' . $id . '/edit');
        }

        $newPath = $this->handleReceipt($expense['receipt_image_path'] ?? null);
        if ($newPath === false) {
            redirect('/expenses/' . $id . '/edit');
        }

        Expense::update($id, $data);
        if ($newPath !== null) {
            \App\Core\Database::execute(
                "UPDATE expenses SET receipt_image_path = ? WHERE expense_id = ?",
                [$newPath, $id]
            );
        }

        Flash::success('Expense updated.');
        redirect('/expenses');
    }

    public function destroy(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');
        $expense = Expense::find($id);
        if (!$expense || (int)$expense['branch_id'] !== auth_branch()) {
            Flash::error('Expense not found.');
            redirect('/expenses');
        }
        if (!empty($expense['receipt_image_path'])) {
            $this->deleteFile($expense['receipt_image_path']);
        }
        Expense::delete($id);
        Flash::success('Expense deleted.');
        redirect('/expenses');
    }

    public function summary(): void
    {
        if (!Auth::check()) redirect('/login');
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        $groups = Expense::byGroup(auth_branch(), $from, $to);

        $efdWith    = Expense::sumWithEfd(auth_branch(), $from . ' 00:00:00', $to . ' 23:59:59');
        $efdWithout = Expense::sumWithoutEfd(auth_branch(), $from . ' 00:00:00', $to . ' 23:59:59');

        view('expenses/summary', [
            'layout'    => 'app',
            'pageTitle' => 'Expense Summary',
            'user'      => Auth::user(),
            'groups'    => $groups,
            'from'      => $from,
            'to'        => $to,
            'efdWith'   => $efdWith,
            'efdWithout'=> $efdWithout,
        ]);
    }

    private function handleReceipt(?string $existingPath): string|false|null
    {
        if (isset($_FILES['camera_image']) && $_FILES['camera_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            return $this->processCameraImage($_FILES['camera_image'], $existingPath);
        }
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] !== UPLOAD_ERR_NO_FILE) {
            return $this->processFile($_FILES['receipt'], $existingPath);
        }
        return null;
    }

    private function processCameraImage(array $file, ?string $existingPath): string|false
    {
        if ($file['error'] !== UPLOAD_ERR_OK) { Flash::error('Camera capture failed.'); return false; }
        if ($file['size'] > 10 * 1024 * 1024) { Flash::error('Photo max 10 MB.'); return false; }
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
            Flash::error('Camera must be JPG/PNG/WEBP.'); return false;
        }

        // If Imagick is not available (e.g., InfinityFree), save as image instead
        if (!class_exists('Imagick')) {
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/receipts';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
            $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
            $name = 'exp_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $name)) {
                Flash::error('Save failed.'); return false;
            }
            if ($existingPath) $this->deleteFile($existingPath);
            return 'receipts/' . $name;
        }

        try {
            $pdfPath = $this->imageToPdf($file['tmp_name']);
        } catch (\Throwable $e) {
            Flash::error('Could not convert: ' . $e->getMessage()); return false;
        }
        if ($existingPath) $this->deleteFile($existingPath);
        return $pdfPath;
    }

    private function processFile(array $file, ?string $existingPath): string|false
    {
        if ($file['error'] !== UPLOAD_ERR_OK) { Flash::error('Upload failed.'); return false; }
        if ($file['size'] > 5 * 1024 * 1024) { Flash::error('File max 5 MB.'); return false; }
        $mime = mime_content_type($file['tmp_name']);
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf'];
        if (!isset($allowed[$mime])) { Flash::error('Must be JPG/PNG/WEBP/PDF.'); return false; }
        $ext = $allowed[$mime];
        $name = 'exp_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/receipts';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
        $target = $uploadDir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) { Flash::error('Save failed.'); return false; }
        if ($existingPath) $this->deleteFile($existingPath);
        return 'receipts/' . $name;
    }

    private function imageToPdf(string $tmpImagePath): string
    {
        if (!class_exists('Imagick')) throw new \RuntimeException('Imagick missing.');
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/receipts';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
        $basename = 'exp_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.pdf';
        $target = $uploadDir . '/' . $basename;
        $im = new \Imagick();
        $im->setBackgroundColor('white');
        $im->readImage($tmpImagePath);
        $im->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
        $im->autoOrientImage();
        $im->setImageFormat('jpeg');
        $im->setImageCompressionQuality(85);
        $im->resizeImage(595, 0, \Imagick::FILTER_LANCZOS, 1);
        if ($im->getImageHeight() > 842) $im->resizeImage(0, 842, \Imagick::FILTER_LANCZOS, 1);
        $im->setImageFormat('pdf');
        $im->setImageCompression(\Imagick::COMPRESSION_JPEG);
        $im->setImageCompressionQuality(85);
        $im->writeImage($target);
        $im->clear(); $im->destroy();
        return 'receipts/' . $basename;
    }

    private function deleteFile(string $relativePath): void
    {
        $full = dirname(__DIR__, 2) . '/public/uploads/' . $relativePath;
        if (is_file($full)) @unlink($full);
    }
}
