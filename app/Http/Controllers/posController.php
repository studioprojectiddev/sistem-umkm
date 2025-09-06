<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ProductSalesSummary;

class PosController extends Controller
{
    /**
     * Menampilkan halaman POS
     */
    public function index()
    {
        $products = Product::select('id','name','sku','price','stock','thumbnail','unit')->get();
        $cart = session()->get('cart', []);
        return view('umkm.pos', compact('products','cart'));
    }

    /**
     * Ambil isi cart realtime
     */
    public function getCart()
    {
        $cart = session()->get('cart', []);
        return response()->json(['status' => 'success', 'cart' => $cart]);
    }

    /**
     * Tambah produk ke cart
     */
    public function addToCart(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += 1;
        } else {
            $cart[$id] = [
                "name" => $product->name,
                "quantity" => 1,
                "price" => $product->price,
                "thumbnail" => $product->thumbnail,
                "discount" => 0,
                "subtotal" => $product->price,
                "unit" => $product->unit ?? 'pcs'
            ];
        }

        $cart[$id]['subtotal'] = ($cart[$id]['price'] * $cart[$id]['quantity']) - $cart[$id]['discount'];
        session()->put('cart', $cart);

        return response()->json(['status' => 'success', 'cart' => $cart]);
    }

    /**
     * Update cart (qty, discount, dll)
     */
    public function updateCart(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1',
            'discount' => 'nullable|numeric|min:0'
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $request->quantity ?? $cart[$id]['quantity'];
            $cart[$id]['discount'] = $request->discount ?? $cart[$id]['discount'];
            $cart[$id]['subtotal'] = ($cart[$id]['price'] * $cart[$id]['quantity']) - $cart[$id]['discount'];
            session()->put('cart', $cart);
        }

        return response()->json(['status' => 'success', 'cart' => $cart]);
    }

    /**
     * Hapus produk dari cart
     */
    public function removeFromCart($id)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }
        return response()->json(['status' => 'success', 'cart' => $cart]);
    }

    /**
     * Kosongkan cart
     */
    public function clearCart()
    {
        session()->forget('cart');
        return response()->json(['status' => 'success', 'cart' => []]);
    }

    /**
     * Checkout transaksi
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'nullable|string|in:cash,transfer,qris,ewallet'
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return response()->json(['status' => 'error', 'message' => 'Cart masih kosong!']);
        }

        DB::beginTransaction();
        try {
            $subtotal = collect($cart)->sum('subtotal');
            $discount = 0;
            $tax = 0;
            $shipping_cost = 0;
            $total = $subtotal - $discount + $tax + $shipping_cost;

            // Simpan transaksi
            $transaction = Transaction::create([
                'invoice_number' => 'INV' . time(),
                'transaction_type' => 'sale',
                'idpenginput' => auth()->id(),
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'shipping_cost' => $shipping_cost,
                'total' => $total,
                'payment_status' => 'paid',
                'payment_method' => $request->payment_method ?? 'cash',
                'status' => 'completed',
            ]);

            foreach ($cart as $productId => $item) {
                // Simpan detail item
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'idpenginput' => auth()->id(),
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'],
                    'subtotal' => $item['subtotal'],
                    'unit' => $item['unit'] ?? 'pcs'
                ]);

                // Update stok produk
                $product = Product::find($productId);
                if ($product) {
                    $product->decrement('stock', $item['quantity']);
                }

                // Update summary penjualan
                $summary = ProductSalesSummary::firstOrNew([
                    'product_id' => $productId,
                    'variation_id' => null,
                    'date' => now()->toDateString()
                ]);
                $summary->idpenginput = auth()->id();
                $summary->total_qty = ($summary->total_qty ?? 0) + $item['quantity'];
                $summary->total_sales = ($summary->total_sales ?? 0) + $item['subtotal'];
                $summary->save();
            }

            DB::commit();
            session()->forget('cart');

            return response()->json(['status' => 'success', 'message' => 'Transaksi berhasil!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat checkout.']);
        }
    }
}