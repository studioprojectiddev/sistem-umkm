<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ProductSalesSummary;
use App\Models\ProductVariation;

class PosController extends Controller
{
    /**
     * Menampilkan halaman POS
     */
    public function index()
    {
        $products = Product::with([
            'category',
            'variations.options.attribute'
        ])->select(
            'id',
            'name',
            'sku',
            'price',
            'cost_price',
            'discount_price',
            'is_promo',
            'promo_price',
            'promo_start',
            'promo_end',
            'stock',
            'thumbnail',
            'unit'
        )->get();

        // Tambahkan properti tambahan
        $products->each(function ($product) {
            // Final price (pakai promo kalau aktif)
            $product->final_price = ($product->is_promo && $product->promo_start <= now() && $product->promo_end >= now())
                ? $product->promo_price
                : $product->price;

            // Variasi → pakai nomor urut mulai dari 1, tambahkan weight (gram)
            $product->variation_json = $product->variations->values()->map(function ($v, $index) {
                return [
                    "no"     => $index + 1, // nomor urut mulai dari 1
                    "name"   => $v->name,
                    "price"  => $v->price,
                    "stock"  => $v->stock,
                    "weight" => $v->weight ?? 0, // dalam gram, default 0 kalau null
                ];
            })->toArray();
        });

        $cart = session()->get('cart', []);

        return view('umkm.pos', compact('products', 'cart'));
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

        // Tentukan harga
        $today = now();
        $price = $product->price;

        if ($product->is_promo 
            && $product->promo_price 
            && $product->promo_start <= $today 
            && $product->promo_end >= $today) {
            $price = $product->promo_price;
        }

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += 1;
        } else {
            $cart[$id] = [
                "id"       => $product->id,
                "type"     => "product",
                "name"     => $product->name,
                "quantity" => 1,
                "price"    => $price,
                "thumbnail"=> $product->thumbnail,
                "discount" => 0,
                "subtotal" => $price,
                "unit"     => $product->unit ?? 'pcs'
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
                'invoice_number'   => 'INV' . time(),
                'transaction_type' => 'sale',
                'idpenginput'      => auth()->id(),
                'user_id'          => auth()->id(),
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'tax'              => $tax,
                'shipping_cost'    => $shipping_cost,
                'total'            => $total,
                'payment_status'   => 'paid',
                'payment_method'   => $request->payment_method ?? 'cash',
                'status'           => 'completed',
            ]);

            foreach ($cart as $key => $item) {
                $isVariation = $item['type'] === 'variation';
            
                // Ambil product_id untuk varian
                $productId = null;
                $variationId = null;
            
                if ($isVariation) {
                    $variation = \App\Models\ProductVariation::with('product')->find($item['id']);
                    if (!$variation) {
                        throw new \Exception("Varian tidak ditemukan");
                    }
                    $productId = $variation->product_id; // produk induk
                    $variationId = $variation->id;
                } else {
                    $productId = $item['id'];
                }
            
                // Simpan detail item
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'idpenginput'    => auth()->id(),
                    'product_id'     => $productId,
                    'variation_id'   => $variationId,
                    'quantity'       => $item['quantity'],
                    'price'          => $item['price'],
                    'discount'       => $item['discount'],
                    'subtotal'       => $item['subtotal'],
                    'unit'           => $item['unit'] ?? 'pcs'
                ]);
            
                // Kurangi stok
                if ($isVariation) {
                    $variation->decrement('stock', $item['quantity']);
                } else {
                    $product = Product::find($productId);
                    if ($product) {
                        $product->decrement('stock', $item['quantity']);
                    }
                }
            
                // Update summary penjualan
                $summary = ProductSalesSummary::firstOrNew([
                    'product_id'   => $productId,
                    'variation_id' => $variationId,
                    'date'         => now()->toDateString()
                ]);
                $summary->idpenginput   = auth()->id();
                $summary->total_qty     = ($summary->total_qty ?? 0) + $item['quantity'];
                $summary->total_sales   = ($summary->total_sales ?? 0) + $item['subtotal'];
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

    // PosController
    public function getProducts()
    {
        $products = Product::select('id','name','sku','price','stock','thumbnail','unit')->get();
        return response()->json($products);
    }

    public function addVariationToCart(Request $request, $id)
    {
        $variation = ProductVariation::with('product')->findOrFail($id);

        if ($variation->stock <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Stok varian habis!']);
        }

        $cart = session()->get('cart', []);
        $key = "variation_{$variation->id}";

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += 1;
        } else {
            $cart[$key] = [
                "id" => $variation->id,
                "type" => "variation",
                "name" => ($variation->product->name ?? 'Produk') . ' - ' . $variation->name,
                "quantity" => 1,
                "price" => $variation->price ?? $variation->product->price,
                "thumbnail" => $variation->image ?? ($variation->product->thumbnail ?? null),
                "discount" => 0,
                "subtotal" => $variation->price ?? $variation->product->price,
                "unit" => $variation->product->unit ?? 'pcs'
            ];
        }

        $cart[$key]['subtotal'] = ($cart[$key]['price'] * $cart[$key]['quantity']) - $cart[$key]['discount'];

        // 🚫 HAPUS ini:
        // $variation->decrement('stock', 1);

        session()->put('cart', $cart);

        return response()->json(['status' => 'success', 'cart' => $cart]);
    }

}