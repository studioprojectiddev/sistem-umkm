<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Warehouse;
use App\Models\Category;

class KasirController extends Controller
{
    /**
     * Halaman utama Kasir Tablet
     */
    public function index()
    {
        $warehouseId = session('active_warehouse_id');

        if (!$warehouseId) {
            return redirect()
                ->route('umkm.pos.index')
                ->with('need_outlet', true);
        }

        // Produk outlet aktif
        $products = Product::with(['variations.options.attribute'])
            ->leftJoin('warehouse_products as wp', function ($q) use ($warehouseId) {
                $q->on('wp.product_id', '=', 'products.id')
                  ->where('wp.warehouse_id', $warehouseId)
                  ->whereNull('wp.variation_id');
            })
            ->select('products.*', DB::raw('COALESCE(wp.stock,0) as stock'))
            ->get();

        // Cart per outlet
        $cart = session()->get("cart.$warehouseId", []);

        $categories = Category::whereNull('parent_id')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

        return view('umkm.kasir.index', compact('products', 'cart', 'categories'));
    }

    /**
     * Tambah produk ke cart
     */
    public function addToCart(Request $request, $id)
    {
        $warehouseId = session('active_warehouse_id');

        if (!$warehouseId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Outlet belum dipilih'
            ]);
        }

        $product = Product::findOrFail($id);
        $cart = session()->get("cart.$warehouseId", []);

        // ✅ qty dari modal / default 1
        $qty = max(1, (int) ($request->qty ?? 1));

        // 🔹 stok produk non-variasi di outlet aktif
        $stock = DB::table('warehouse_products')
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $id)
            ->whereNull('variation_id')
            ->value('stock') ?? 0;

        $key = "product_$id";

        $currentQty = isset($cart[$key])
            ? (int) $cart[$key]['quantity']
            : 0;

        // 🔴 validasi stok
        if (($stock - $currentQty) < $qty) {
            return response()->json([
                'status'  => 'error',
                'message' => "Stok tidak mencukupi (tersisa " . ($stock - $currentQty) . ")"
            ]);
        }

        $price = $product->final_price ?? $product->price;

        // 🔹 tambah / update cart
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $qty;
        } else {
            $cart[$key] = [
                'key'       => $key,
                'id'        => $product->id,
                'type'      => 'product',
                'name'      => $product->name,
                'variation' => null,
                'quantity'  => $qty,
                'price'     => $price,
                'subtotal'  => 0,
                'unit'      => $product->unit ?? 'pcs',
            ];
        }

        // 🔹 hitung subtotal item
        $cart[$key]['subtotal'] =
            $cart[$key]['quantity'] * $cart[$key]['price'];

        session()->put("cart.$warehouseId", $cart);

        // ✅ RESPONSE KHUSUS REALTIME POS
        return response()->json([
            'status' => 'success',
            'item'   => $cart[$key],                 // ⬅️ item yang berubah
            'total'  => collect($cart)->sum('subtotal') // ⬅️ total baru
        ]);
    }

    /**
     * Tambah variasi ke cart
     */
    public function addVariationToCart(Request $request, $id)
    {
        $warehouseId = session('active_warehouse_id');

        if (!$warehouseId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Outlet belum dipilih'
            ]);
        }

        $variation = ProductVariation::with('product')->findOrFail($id);
        $cart = session()->get("cart.$warehouseId", []);

        $qty = max(1, (int) ($request->qty ?? 1));

        // 🔹 stok variasi
        $stock = DB::table('warehouse_products')
            ->where('warehouse_id', $warehouseId)
            ->where('variation_id', $id)
            ->value('stock') ?? 0;

        $key = "variation_$id";

        $currentQty = isset($cart[$key])
            ? (int) $cart[$key]['quantity']
            : 0;

        if (($stock - $currentQty) < $qty) {
            return response()->json([
                'status'  => 'error',
                'message' => "Stok tidak mencukupi (tersisa " . ($stock - $currentQty) . ")"
            ]);
        }

        $price = $variation->price ?? $variation->product->price;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $qty;
        } else {
            $cart[$key] = [
                'key'          => $key,
                'id'           => $variation->product_id,
                'variation_id' => $variation->id,
                'type'         => 'variation',
                'name'         => $variation->product->name,
                'variation'    => $variation->name,
                'quantity'     => $qty,
                'price'        => $price,
                'subtotal'     => 0,
                'unit'         => $variation->product->unit ?? 'pcs',
            ];
        }

        $cart[$key]['subtotal'] =
            $cart[$key]['quantity'] * $cart[$key]['price'];

        session()->put("cart.$warehouseId", $cart);

        return response()->json([
            'status' => 'success',
            'cart'   => $cart
        ]);
    }

    public function updateCartQty(Request $request)
    {
        $warehouseId = session('active_warehouse_id');

        if (!$warehouseId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Outlet belum dipilih'
            ]);
        }

        $key = $request->key;
        $qty = max(1, (int) $request->qty);

        $cart = session()->get("cart.$warehouseId", []);

        if (!isset($cart[$key])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item tidak ditemukan'
            ]);
        }

        $item = $cart[$key];

        // =============================
        // CEK STOK
        // =============================
        if ($item['type'] === 'product') {
            $stock = DB::table('warehouse_products')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $item['id'])
                ->whereNull('variation_id')
                ->value('stock') ?? 0;
        } else {
            $stock = DB::table('warehouse_products')
                ->where('warehouse_id', $warehouseId)
                ->where('variation_id', $item['variation_id'])
                ->value('stock') ?? 0;
        }

        if ($qty > $stock) {
            return response()->json([
                'status'  => 'error',
                'message' => "Stok tidak mencukupi (maks $stock)"
            ]);
        }

        // =============================
        // UPDATE CART
        // =============================
        $cart[$key]['quantity'] = $qty;
        $cart[$key]['subtotal'] = $qty * $cart[$key]['price'];

        session()->put("cart.$warehouseId", $cart);

        return response()->json([
            'status' => 'success',
            'item'   => $cart[$key],
            'total'  => collect($cart)->sum('subtotal')
        ]);
    }

    public function removeItem(Request $request)
    {
        $warehouseId = session('active_warehouse_id');

        if (!$warehouseId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Outlet belum dipilih'
            ]);
        }

        $key = $request->key;

        $cart = session()->get("cart.$warehouseId", []);

        if (!isset($cart[$key])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item tidak ditemukan'
            ]);
        }

        // hapus item
        unset($cart[$key]);

        session()->put("cart.$warehouseId", $cart);

        $total = collect($cart)->sum('subtotal');

        return response()->json([
            'status' => 'success',
            'total'  => $total
        ]);
    }

    /**
     * Checkout
     */
    public function checkout(Request $request)
    {
        $warehouseId = session('active_warehouse_id');

        if (!$warehouseId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Outlet belum dipilih'
            ], 422);
        }

        $cart = session()->get("cart.$warehouseId", []);

        if (empty($cart)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Keranjang kosong'
            ], 422);
        }

        // 🔹 HITUNG ULANG TOTAL DARI CART (AMAN)
        $total = collect($cart)->sum('subtotal');

        /*
        |--------------------------------------------------------------------------
        | MODE 1️⃣ : CASH LAMA (BACKWARD COMPATIBLE)
        |--------------------------------------------------------------------------
        */
        if (!$request->has('payments')) {

            $uang = (int) $request->uang_diterima;

            if ($uang <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Uang diterima tidak valid'
                ], 422);
            }

            if ($uang < $total) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Uang belum cukup'
                ], 422);
            }

            DB::transaction(function () use ($cart, $total, $uang, $warehouseId) {

                $sale = Sale::create([
                    'warehouse_id' => $warehouseId,
                    'total'        => $total,
                    'paid'         => $uang,
                    'change'       => $uang - $total,
                    'payment_type' => 'cash'
                ]);

                foreach ($cart as $item) {
                    SaleItem::create([
                        'sale_id'      => $sale->id,
                        'product_id'   => $item['id'],
                        'variation_id' => $item['variation_id'] ?? null,
                        'qty'          => $item['quantity'],
                        'price'        => $item['price'],
                        'subtotal'     => $item['subtotal']
                    ]);
                }

                SalePayment::create([
                    'sale_id' => $sale->id,
                    'method'  => 'cash',
                    'amount'  => $uang
                ]);

                session()->forget("cart.$warehouseId");
            });

            return response()->json([
                'status'    => 'success',
                'message'   => 'Transaksi berhasil',
                'kembalian' => $uang - $total
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | MODE 2️⃣ : SPLIT PAYMENT (DISEMPURNAKAN)
        |--------------------------------------------------------------------------
        */
        $payments = $request->payments;

        if (!is_array($payments) || count($payments) === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pembayaran tidak valid'
            ], 422);
        }

        $allowedMethods = ['cash', 'qris', 'transfer'];
        $paid = 0;
        $cashAmount = 0;

        foreach ($payments as $p) {

            if (
                !isset($p['method'], $p['amount']) ||
                !in_array($p['method'], $allowedMethods) ||
                (int)$p['amount'] <= 0
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Metode atau jumlah pembayaran tidak valid'
                ], 422);
            }

            $paid += (int)$p['amount'];

            if ($p['method'] === 'cash') {
                $cashAmount += (int)$p['amount'];
            }
        }

        // 🔥 BOLEH LEBIH, TAPI TIDAK BOLEH KURANG
        if ($paid < $total) {
            return response()->json([
                'status' => 'error',
                'message' => 'Total pembayaran kurang'
            ], 422);
        }

        DB::transaction(function () use ($cart, $total, $payments, $paid, $warehouseId) {

            $sale = Sale::create([
                'warehouse_id' => $warehouseId,
                'total'        => $total,
                'paid'         => $paid,
                'change'       => max(0, $paid - $total),
                'payment_type' => 'split'
            ]);

            foreach ($cart as $item) {
                SaleItem::create([
                    'sale_id'      => $sale->id,
                    'product_id'   => $item['id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'qty'          => $item['quantity'],
                    'price'        => $item['price'],
                    'subtotal'     => $item['subtotal']
                ]);
            }

            foreach ($payments as $p) {
                SalePayment::create([
                    'sale_id' => $sale->id,
                    'method'  => $p['method'],
                    'amount'  => $p['amount']
                ]);
            }

            session()->forget("cart.$warehouseId");
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Transaksi berhasil',
            'kembalian' => max(0, $paid - $total)
        ]);
    }

}