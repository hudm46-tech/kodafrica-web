<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Validator;
use App\Models\Product;

final class ProductController
{
    public function index(): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();

        $search   = trim($_GET['q'] ?? '');
        $category = trim($_GET['category'] ?? '');
        $lowOnly  = !empty($_GET['low']);

        $products = Product::listByBranch($branchId, $search ?: null, $category ?: null, $lowOnly);
        $categories = Product::categories($branchId);

        $stats = [
            'total'    => Product::countByBranch($branchId),
            'low'      => Product::lowStockCount($branchId),
            'value'    => Product::totalStockValue($branchId),
        ];

        view('products/index', [
            'layout'     => 'app',
            'pageTitle'  => 'Stock',
            'user'       => Auth::user(),
            'products'   => $products,
            'categories' => $categories,
            'search'     => $search,
            'category'   => $category,
            'lowOnly'    => $lowOnly,
            'stats'      => $stats,
            'topbarAction' => '<a href="' . url('/products/create') . '" class="topbar-action">'
                . '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>'
                . 'New Product</a>',
        ]);
    }

    public function create(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');
        view('products/form', [
            'layout'    => 'app',
            'pageTitle' => 'New Product',
            'user'      => Auth::user(),
            'product'   => null,
            'categories' => Product::categories(auth_branch()),
        ]);
    }

    public function store(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');

        $data = [
            'name'            => trim($_POST['name'] ?? ''),
            'unit_of_measure' => trim($_POST['unit_of_measure'] ?? 'PCS'),
            'quantity'        => (int)($_POST['quantity'] ?? 0),
            'minimum_stock'   => (int)($_POST['minimum_stock'] ?? 5),
            'purchase_price'  => (float)($_POST['purchase_price'] ?? 0),
            'selling_price'   => (float)($_POST['selling_price'] ?? 0),
            'category'        => trim($_POST['category'] ?? ''),
            'supplier'        => trim($_POST['supplier'] ?? ''),
            'remarks'         => trim($_POST['remarks'] ?? ''),
        ];

        $v = new Validator($data);
        $v->required('name', 'Product name')
          ->numeric('quantity', 'Quantity')
          ->numeric('purchase_price', 'Purchase price')
          ->numeric('selling_price', 'Selling price');

        if ($data['selling_price'] <= 0) Flash::error('Selling price must be greater than 0.');
        if ($data['purchase_price'] < 0) Flash::error('Purchase price cannot be negative.');
        if ($data['quantity'] < 0) Flash::error('Quantity cannot be negative.');

        if ($v->fails() || $data['selling_price'] <= 0) {
            foreach ($v->errors() as $msg) Flash::error($msg);
            $_SESSION['_old'] = $data;
            redirect('/products/create');
        }

        $data['branch_id'] = auth_branch();
        Product::create($data);

        Flash::success('Product added: ' . $data['name']);
        redirect('/products');
    }

    public function edit(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');
        $product = Product::find($id);
        if (!$product || (int)$product['branch_id'] !== auth_branch()) {
            Flash::error('Product not found.');
            redirect('/products');
        }
        view('products/form', [
            'layout'    => 'app',
            'pageTitle' => 'Edit Product',
            'user'      => Auth::user(),
            'product'   => $product,
            'categories' => Product::categories(auth_branch()),
        ]);
    }

    public function update(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');
        $product = Product::find($id);
        if (!$product || (int)$product['branch_id'] !== auth_branch()) {
            Flash::error('Product not found.');
            redirect('/products');
        }

        $data = [
            'name'            => trim($_POST['name'] ?? ''),
            'unit_of_measure' => trim($_POST['unit_of_measure'] ?? 'PCS'),
            'minimum_stock'   => (int)($_POST['minimum_stock'] ?? 5),
            'purchase_price'  => (float)($_POST['purchase_price'] ?? 0),
            'selling_price'   => (float)($_POST['selling_price'] ?? 0),
            'category'        => trim($_POST['category'] ?? ''),
            'supplier'        => trim($_POST['supplier'] ?? ''),
            'remarks'         => trim($_POST['remarks'] ?? ''),
        ];

        $v = new Validator($data);
        $v->required('name', 'Product name')
          ->numeric('purchase_price', 'Purchase price')
          ->numeric('selling_price', 'Selling price');

        if ($data['selling_price'] <= 0) Flash::error('Selling price must be greater than 0.');

        if ($v->fails() || $data['selling_price'] <= 0) {
            foreach ($v->errors() as $msg) Flash::error($msg);
            $_SESSION['_old'] = $data;
            redirect('/products/' . $id . '/edit');
        }

        Product::update($id, $data);

        // Stock adjustment (if changed)
        $newQty = (int)($_POST['quantity'] ?? $product['quantity']);
        if ($newQty !== (int)$product['quantity']) {
            Product::setQuantity($id, $newQty);
        }

        Flash::success('Product updated.');
        redirect('/products');
    }

    public function adjustStock(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER', 'CASHIER');

        $product = Product::find($id);
        if (!$product || (int)$product['branch_id'] !== auth_branch()) {
            Flash::error('Product not found.');
            redirect('/products');
        }

        $qty  = (int)($_POST['qty'] ?? 0);
        $mode = $_POST['mode'] ?? 'add';

        if ($qty <= 0) {
            Flash::error('Quantity must be greater than zero.');
            redirect('/products');
        }
        if (!in_array($mode, ['add', 'remove'], true)) {
            Flash::error('Invalid stock mode.');
            redirect('/products');
        }

        $delta = ($mode === 'remove') ? -$qty : $qty;
        $newQty = (int)$product['quantity'] + $delta;

        if ($newQty < 0) {
            Flash::error('Cannot reduce stock below zero. Current: ' . (int)$product['quantity'] . ' ' . $product['unit_of_measure']);
            redirect('/products');
        }

        Product::adjustQuantity($id, $delta);

        $actionLabel = ($mode === 'add') ? 'Added' : 'Removed';
        $prep = ($mode === 'add') ? 'to' : 'from';
        Flash::success("{$actionLabel} {$qty} {$product['unit_of_measure']} {$prep} {$product['name']}");
        redirect('/products');
    }

    public function destroy(int $id): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN', 'MANAGER');
        $product = Product::find($id);
        if (!$product || (int)$product['branch_id'] !== auth_branch()) {
            Flash::error('Product not found.');
            redirect('/products');
        }
        Product::delete($id);
        Flash::success('Product deleted.');
        redirect('/products');
    }
}
