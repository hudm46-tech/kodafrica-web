<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Validator;
use App\Models\Product;
use App\Models\Sale;

final class PosController
{
    // ---------- GET /pos ----------
    public function index(): void
    {
        if (!Auth::check()) redirect('/login');
        $branchId = auth_branch();
        $user = Auth::user();

        $search = trim($_GET['q'] ?? '');
        $products = $search
            ? Database::select(
                "SELECT * FROM products WHERE branch_id = ? AND quantity > 0 AND name LIKE ? ORDER BY name ASC LIMIT 50",
                [$branchId, '%' . $search . '%']
              )
            : Database::select(
                "SELECT * FROM products WHERE branch_id = ? AND quantity > 0 ORDER BY name ASC LIMIT 50",
                [$branchId]
              );

        $cart = $_SESSION['cart'] ?? [];
        $cartDetail = $this->buildCartDetail($cart, $branchId);

        view('pos/index', [
            'layout'    => 'app',
            'pageTitle' => 'Point of Sale',
            'user'      => $user,
            'products'  => $products,
            'search'    => $search,
            'cart'      => $cart,
            'cartDetail'=> $cartDetail,
        ]);
    }

    // ---------- POST /pos/add ----------
    public function add(): void
    {
        if (!Auth::check()) { http_response_code(401); echo json_encode(['error' => 'auth']); return; }

        $productId = (int)($_POST['product_id'] ?? 0);
        $qty       = max(1, (int)($_POST['qty'] ?? 1));

        $product = Product::find($productId);
        if (!$product || (int)$product['branch_id'] !== auth_branch()) {
            echo json_encode(['error' => 'Product not found']); return;
        }

        $cart = $_SESSION['cart'] ?? [];
        $existing = $cart[$productId] ?? 0;
        $newQty = $existing + $qty;

        // Cap at stock
        if ($newQty > (int)$product['quantity']) {
            $newQty = (int)$product['quantity'];
        }
        if ($newQty <= 0) {
            echo json_encode(['error' => 'Out of stock']); return;
        }

        $cart[$productId] = $newQty;
        $_SESSION['cart'] = $cart;

        echo json_encode([
            'ok'        => true,
            'cart'      => $this->buildCartDetail($cart, auth_branch()),
            'count'     => array_sum($cart),
        ]);
    }

    // ---------- POST /pos/update ----------
    public function update(): void
    {
        if (!Auth::check()) { http_response_code(401); echo json_encode(['error' => 'auth']); return; }

        $productId = (int)($_POST['product_id'] ?? 0);
        $qty       = (int)($_POST['qty'] ?? 0);

        $cart = $_SESSION['cart'] ?? [];
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $product = Product::find($productId);
            if (!$product || (int)$product['branch_id'] !== auth_branch()) {
                echo json_encode(['error' => 'Product not found']); return;
            }
            $qty = min($qty, (int)$product['quantity']);
            if ($qty <= 0) {
                unset($cart[$productId]);
            } else {
                $cart[$productId] = $qty;
            }
        }
        $_SESSION['cart'] = $cart;

        echo json_encode([
            'ok'        => true,
            'cart'      => $this->buildCartDetail($cart, auth_branch()),
            'count'     => array_sum($cart),
        ]);
    }

    // ---------- POST /pos/remove ----------
    public function remove(): void
    {
        if (!Auth::check()) { http_response_code(401); echo json_encode(['error' => 'auth']); return; }
        $productId = (int)($_POST['product_id'] ?? 0);
        $cart = $_SESSION['cart'] ?? [];
        unset($cart[$productId]);
        $_SESSION['cart'] = $cart;
        echo json_encode([
            'ok'        => true,
            'cart'      => $this->buildCartDetail($cart, auth_branch()),
            'count'     => array_sum($cart),
        ]);
    }

    // ---------- POST /pos/clear ----------
    public function clear(): void
    {
        if (!Auth::check()) { http_response_code(401); echo json_encode(['error' => 'auth']); return; }
        $_SESSION['cart'] = [];
        echo json_encode(['ok' => true, 'cart' => [], 'count' => 0]);
    }

    // ---------- POST /pos/checkout ----------
    public function checkout(): void
    {
        if (!Auth::check()) redirect('/login');

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            Flash::error('Cart is empty.');
            redirect('/pos');
        }

        $paymentMethod = $_POST['payment_method'] ?? 'CASH';
        if (!isset(Sale::PAYMENT_METHODS[$paymentMethod])) {
            Flash::error('Invalid payment method.');
            redirect('/pos');
        }

        $customerName  = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $paymentRef    = trim($_POST['payment_reference'] ?? '');
        $discount      = (float)($_POST['discount'] ?? 0);

        // For CREDIT, require customer name
        if ($paymentMethod === 'CREDIT' && $customerName === '') {
            Flash::error('Customer name is required for credit sales.');
            redirect('/pos');
        }

        $branchId = auth_branch();
        $userId   = auth_id();

        try {
            Database::begin();

            $totalItems = 0;
            $totalAmount = 0.0;
            $saleGroupTs = date('Y-m-d H:i:s');

            foreach ($cart as $productId => $qty) {
                $product = Database::first("SELECT * FROM products WHERE product_id = ? AND branch_id = ? FOR UPDATE", [$productId, $branchId]);
                if (!$product) {
                    throw new \RuntimeException("Product #$productId not found.");
                }
                if ((int)$product['quantity'] < $qty) {
                    throw new \RuntimeException("Insufficient stock for {$product['name']}. Available: {$product['quantity']}.");
                }

                $sellingPrice  = (float)$product['selling_price'];
                $purchasePrice = (float)$product['purchase_price'];
                $lineTotal     = $sellingPrice * $qty;
                $lineDiscount  = 0;

                Database::insert(
                    "INSERT INTO sales (branch_id, employee_id, product_id, product_name, quantity_sold,
                                        purchase_price_at_sale, selling_price_at_sale, discount, total_amount,
                                        payment_method, payment_reference, customer_phone, customer_name,
                                        is_paid, receipt_type, sale_date, last_updated)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $branchId, $userId, $productId, $product['name'], $qty,
                        $purchasePrice, $sellingPrice, $lineDiscount, $lineTotal,
                        $paymentMethod, $paymentRef ?: null, $customerPhone ?: null, $customerName ?: null,
                        $paymentMethod === 'CREDIT' ? 0 : 1,
                        'NORMAL',
                        $saleGroupTs,
                        now(),
                    ]
                );

                // Decrement stock
                Database::execute(
                    "UPDATE products SET quantity = quantity - ?, last_updated = ? WHERE product_id = ?",
                    [$qty, now(), $productId]
                );

                $totalItems += $qty;
                $totalAmount += $lineTotal;
            }

            // Apply cart-level discount (proportional)
            if ($discount > 0 && $totalAmount > 0) {
                $discountPerItem = $discount / $totalItems;
                Database::execute(
                    "UPDATE sales SET discount = discount + (quantity_sold * ?), total_amount = total_amount - (quantity_sold * ?)
                     WHERE branch_id = ? AND sale_date = ? AND employee_id = ?",
                    [$discountPerItem, $discountPerItem, $branchId, $saleGroupTs, $userId]
                );
            }

            Database::commit();
            $_SESSION['cart'] = [];
            $_SESSION['last_sale_ts'] = $saleGroupTs;

            Flash::success("Sale completed! $totalItems item" . ($totalItems === 1 ? '' : 's') . ", total " . money($totalAmount));
            redirect('/pos/receipt?ts=' . urlencode($saleGroupTs));

        } catch (\Throwable $e) {
            Database::rollback();
            Flash::error('Checkout failed: ' . $e->getMessage());
            redirect('/pos');
        }
    }

    // ---------- GET /pos/receipt ----------
    public function receipt(): void
    {
        if (!Auth::check()) redirect('/login');
        $ts = $_GET['ts'] ?? ($_SESSION['last_sale_ts'] ?? null);
        if (!$ts) { redirect('/pos'); }

        $branchId = auth_branch();
        $sales = Database::select(
            "SELECT * FROM sales WHERE branch_id = ? AND sale_date = ? ORDER BY sale_id ASC",
            [$branchId, $ts]
        );
        if (empty($sales)) {
            Flash::error('Receipt not found.');
            redirect('/pos');
        }

        $branch = \App\Models\Branch::find($branchId);
        $business = \App\Core\Database::first("SELECT * FROM app_settings WHERE id = 1");
        $cashier  = Auth::user();

        $total = 0;
        $itemCount = 0;
        foreach ($sales as $s) { $total += (float)$s['total_amount']; $itemCount += (int)$s['quantity_sold']; }

        // Payment method & customer from first row
        $firstSale = $sales[0];

        view('pos/receipt', [
            'layout'      => 'receipt',
            'pageTitle'   => 'Receipt',
            'branch'      => $branch,
            'business'    => $business,
            'cashier'     => $cashier,
            'sales'       => $sales,
            'total'       => $total,
            'itemCount'   => $itemCount,
            'paymentMethod' => $firstSale['payment_method'],
            'customerName'  => $firstSale['customer_name'],
            'customerPhone' => $firstSale['customer_phone'],
            'reference'     => $firstSale['payment_reference'],
            'saleTs'        => $ts,
        ]);
    }

    /**
     * Build cart details from session storage { product_id => qty }.
     */
    private function buildCartDetail(array $cart, int $branchId): array
    {
        if (empty($cart)) return [];
        $ids = array_keys($cart);
        $place = implode(',', array_fill(0, count($ids), '?'));
        $products = Database::select(
            "SELECT * FROM products WHERE branch_id = ? AND product_id IN ($place)",
            array_merge([$branchId], $ids)
        );

        $out = [];
        $total = 0.0;
        foreach ($products as $p) {
            $qty = (int)($cart[$p['product_id']] ?? 0);
            if ($qty <= 0) continue;
            $line = (float)$p['selling_price'] * $qty;
            $total += $line;
            $out[] = [
                'product_id'  => (int)$p['product_id'],
                'name'        => $p['name'],
                'unit'        => $p['unit_of_measure'],
                'price'       => (float)$p['selling_price'],
                'qty'         => $qty,
                'stock'       => (int)$p['quantity'],
                'line_total'  => $line,
            ];
        }
        return $out;
    }
}
