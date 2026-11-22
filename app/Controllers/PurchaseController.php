<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Validator;
use App\Models\Product;
use App\Models\Purchase;

final class PurchaseController
{
    public function index(): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();

        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');
        $q = trim((string)($_GET['q'] ?? ''));

        $purchases = Purchase::search($branchId, $from, $to, $q);
        $total = 0.0; $count = 0;
        foreach ($purchases as $p) {
            if ($p['status'] === 'ACTIVE') { $total += (float)$p['total_cost']; $count++; }
        }

        view('purchases/index', [
            'layout'    => 'app',
            'pageTitle' => 'Purchases',
            'user'      => Auth::user(),
            'purchases' => $purchases,
            'from' => $from, 'to' => $to, 'q' => $q,
            'total' => $total, 'count' => $count,
            'canManage' => in_array(auth_role(), ['ADMIN', 'MANAGER'], true),
        ]);
    }

    public function create(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');
        $product = $this->ownProduct($id);

        $supplier = Purchase::lastSupplierFor($id) ?: (string)($product['supplier'] ?? '');
        view('purchases/form', [
            'layout'    => 'app',
            'pageTitle' => 'Add Stock',
            'user'      => Auth::user(),
            'product'   => $product,
            'suppliers' => Purchase::suppliers(auth_branch()),
            'defaultSupplier' => $supplier,
        ]);
    }

    public function store(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');
        $this->ownProduct($id);

        $data = [
            'quantity'       => trim((string)($_POST['quantity'] ?? '')),
            'purchase_price' => trim((string)($_POST['purchase_price'] ?? '')),
            'selling_price'  => trim((string)($_POST['selling_price'] ?? '')),
            'supplier_name'  => trim((string)($_POST['supplier_name'] ?? '')),
            'purchase_date'  => trim((string)($_POST['purchase_date'] ?? '')),
            'receipt_number' => trim((string)($_POST['receipt_number'] ?? '')),
            'notes'          => trim((string)($_POST['notes'] ?? '')),
        ];

        $v = new Validator($data);
        $v->required('quantity', 'Quantity')->numeric('quantity', 'Quantity')
          ->required('purchase_price', 'Purchase price')->numeric('purchase_price', 'Purchase price')
          ->required('selling_price', 'Selling price')->numeric('selling_price', 'Selling price')
          ->required('supplier_name', 'Supplier')->max('supplier_name', 150, 'Supplier')
          ->required('purchase_date', 'Date')->date('purchase_date', 'Date')
          ->max('receipt_number', 60, 'Receipt number')->max('notes', 255, 'Notes');
        $errors = $v->fails() ? $v->errors() : [];

        $qty   = (int)$data['quantity'];
        $buy   = round((float)$data['purchase_price'], 2);
        $sell  = round((float)$data['selling_price'], 2);
        if (!$errors) {
            if (!ctype_digit($data['quantity']) || $qty < 1 || $qty > 1000000) $errors[] = 'Quantity must be a whole number of at least 1.';
            if ($buy <= 0)  $errors[] = 'Purchase price must be greater than zero.';
            if ($sell <= 0) $errors[] = 'Selling price must be greater than zero.';
            if ($data['purchase_date'] > date('Y-m-d')) $errors[] = 'Date cannot be in the future.';
        }
        if ($errors) {
            foreach ($errors as $msg) Flash::error($msg);
            $_SESSION['_old'] = $data;
            redirect('/products/' . $id . '/purchase');
        }

        try {
            Database::begin();
            $product = Database::first("SELECT * FROM products WHERE product_id = ? AND branch_id = ? FOR UPDATE", [$id, auth_branch()]);
            if (!$product) throw new \RuntimeException('Product not found.');

            Database::execute(
                "INSERT INTO purchases (branch_id, product_id, product_name, unit_of_measure, supplier_name, quantity,
                    purchase_price, selling_price, total_cost, prev_purchase_price, prev_selling_price,
                    receipt_number, notes, purchase_date, created_by, created_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [auth_branch(), $id, $product['name'], $product['unit_of_measure'], $data['supplier_name'], $qty,
                 $buy, $sell, round($qty * $buy, 2), $product['purchase_price'], $product['selling_price'],
                 $data['receipt_number'] ?: null, $data['notes'] ?: null, $data['purchase_date'], auth_id(), now()]
            );
            Database::execute(
                "UPDATE products SET quantity = quantity + ?, purchase_price = ?, selling_price = ?, supplier = ?, last_updated = ? WHERE product_id = ?",
                [$qty, $buy, $sell, $data['supplier_name'], now(), $id]
            );
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollback();
            Flash::error('Save failed: ' . $e->getMessage());
            $_SESSION['_old'] = $data;
            redirect('/products/' . $id . '/purchase');
        }

        unset($_SESSION['_old']);
        $newQty = (int)$product['quantity'] + $qty;
        Flash::success("Saved: +{$qty} {$product['unit_of_measure']} of {$product['name']} (stock now {$newQty}).");
        if ($buy > $sell) Flash::warning('Selling price is lower than the purchase price. You will sell at a loss.');
        redirect('/products');
    }

    public function void(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');

        $reason = trim((string)($_POST['reason'] ?? ''));
        if (mb_strlen($reason) < 3) { Flash::error('Please give a reason for voiding.'); redirect('/purchases'); }
        $reason = mb_substr($reason, 0, 255);

        try {
            Database::begin();
            $p = Database::first("SELECT * FROM purchases WHERE purchase_id = ? AND branch_id = ? FOR UPDATE", [$id, auth_branch()]);
            if (!$p) throw new \RuntimeException('Purchase not found.');
            if ($p['status'] !== 'ACTIVE') throw new \RuntimeException('This purchase is already voided.');

            if ($p['product_id'] !== null) {
                $prod = Database::first("SELECT * FROM products WHERE product_id = ? FOR UPDATE", [(int)$p['product_id']]);
                if ($prod) {
                    if ((int)$prod['quantity'] < (int)$p['quantity']) {
                        throw new \RuntimeException('Cannot void: only ' . (int)$prod['quantity'] . ' left in stock, but this purchase added ' . (int)$p['quantity'] . '. Part of it was already sold.');
                    }
                    $buy  = (float)$prod['purchase_price'] === (float)$p['purchase_price'] ? $p['prev_purchase_price'] : $prod['purchase_price'];
                    $sell = (float)$prod['selling_price']  === (float)$p['selling_price']  ? $p['prev_selling_price']  : $prod['selling_price'];
                    Database::execute(
                        "UPDATE products SET quantity = quantity - ?, purchase_price = ?, selling_price = ?, last_updated = ? WHERE product_id = ?",
                        [(int)$p['quantity'], $buy, $sell, now(), (int)$p['product_id']]
                    );
                }
            }
            Database::execute(
                "UPDATE purchases SET status = 'VOIDED', void_reason = ?, voided_at = ?, voided_by = ? WHERE purchase_id = ?",
                [$reason, now(), auth_id(), $id]
            );
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollback();
            Flash::error($e->getMessage());
            redirect('/purchases');
        }
        Flash::success('Purchase voided and stock reduced.');
        redirect('/purchases');
    }

    private function ownProduct(int $id): array
    {
        $product = Product::find($id);
        if (!$product || (int)$product['branch_id'] !== auth_branch()) {
            Flash::error('Product not found.');
            redirect('/products');
        }
        return $product;
    }
}
