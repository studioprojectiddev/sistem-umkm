<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ProductSalesSummary;
use App\Models\ProductVariation;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use App\Models\WarehouseTransfer;
use Carbon\Carbon;

class PosController extends Controller
{
    /**
     * Menampilkan halaman POS
     */
    public function index()
    {
        // 🔹 Ambil outlet aktif
        $warehouseId = session('active_warehouse_id');

        // 🔹 Flag untuk view (popup pilih outlet)
        $needOutlet = false;
        if (!$warehouseId) {
            $needOutlet = true;
        }

        /**
         * ======================================================
         * CART (WAJIB PER OUTLET)
         * ======================================================
         */
        $cart = $warehouseId
            ? session()->get("cart.$warehouseId", [])
            : [];

        /**
         * ======================================================
         * PRODUK & VARIASI (HANYA JIKA OUTLET DIPILIH)
         * ======================================================
         */
        $products = collect();
        $productsForJs = [];

        if ($warehouseId) {

            $products = Product::with([
                    'category',
                    'variations.options.attribute'
                ])
                ->leftJoin('warehouse_products as wp', function ($q) use ($warehouseId) {
                    $q->on('wp.product_id', '=', 'products.id')
                    ->where('wp.warehouse_id', $warehouseId)
                    ->whereNull('wp.variation_id');
                })
                ->select(
                    'products.*',
                    DB::raw('COALESCE(wp.stock, 0) as stockProduct'),
                    'wp.min_stock'
                )
                ->distinct()
                ->get();

            // 🔹 Inject harga final & stok variasi per outlet
            $products->each(function ($product) use ($warehouseId) {

                // Harga final (promo aware)
                $product->final_price =
                    ($product->is_promo &&
                    $product->promo_start <= now() &&
                    $product->promo_end >= now())
                    ? $product->promo_price
                    : $product->price;

                // Variasi + stok outlet
                $product->variation_json = $product->variations->map(function ($v, $i) use ($warehouseId, $product) {

                    $stock = DB::table('warehouse_products')
                        ->where('warehouse_id', $warehouseId)
                        ->where('product_id', $product->id)
                        ->where('variation_id', $v->id)
                        ->value('stock') ?? 0;

                    return [
                        'no'           => $i + 1,
                        'variation_id' => $v->id,
                        'name'         => $v->name,
                        'price'        => $v->price,
                        'stock'        => $stock,
                        'weight'       => $v->weight ?? 0,
                        'options'      => $v->options->map(fn ($o) => [
                            'attribute' => $o->attribute->name,
                            'value'     => $o->value,
                        ])->toArray(),
                    ];
                })->toArray();
            });

            // 🔹 Data untuk JS POS
            $productsForJs = $products->map(function ($p) {
                return [
                    'id'           => $p->id,
                    'name'         => $p->name,
                    'thumbnail'    => $p->thumbnail,
                    'category'     => $p->category->name ?? '-',
                    'sku'          => $p->sku ?? '-',
                    'price'        => $p->price,
                    'final_price'  => $p->final_price,
                    'stockProduct' => $p->stockProduct ?? 0,
                    'unit'         => $p->unit ?? 'pcs',
                    'variations'   => $p->variation_json,
                ];
            })->toArray();
        }

        /**
         * ======================================================
         * LIST OUTLET (STORE)
         * ======================================================
         */
        $stores = Warehouse::where('type', 'store')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        /**
         * ======================================================
         * RETURN VIEW
         * ======================================================
         */
        return view('umkm.pos', compact(
            'products',
            'productsForJs',
            'cart',
            'needOutlet',
            'stores'
        ));
    }

    public function setOutlet(Request $request)
    {
        $warehouse = Warehouse::where('id', $request->warehouse_id)
            ->where('type', 'store')
            ->firstOrFail();

        session(['active_warehouse_id' => $warehouse->id]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Ambil isi cart realtime
     */
    public function getCart()
    {
        $warehouseId = session('active_warehouse_id');
        $cart = session()->get("cart.$warehouseId", []);

        return response()->json([
            'status' => 'success',
            'cart'   => $cart
        ]);
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

        $qty = max(1, (int) ($request->qty ?? $request->quantity ?? 1));

        $stock = DB::table('warehouse_products')
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $product->id)
            ->whereNull('variation_id')
            ->value('stock') ?? 0;

        $cartQty = collect($cart)
            ->where('type', 'product')
            ->where('id', $product->id)
            ->sum('quantity');

        $available = $stock - $cartQty;

        if ($available < $qty) {
            return response()->json([
                'status' => 'error',
                'message' => "Stok outlet tidak mencukupi! (tersisa: {$available})"
            ]);
        }

        $today = now();
        $price = (
            $product->is_promo &&
            $product->promo_price &&
            $product->promo_start <= $today &&
            $product->promo_end >= $today
        ) ? $product->promo_price : $product->price;

        $key = 'product_' . $product->id;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $qty;
        } else {
            $cart[$key] = [
                'id'        => $product->id,
                'type'      => 'product',
                'name'      => ($product->sku ?? '-') . ' / ' . $product->name,
                'variation' => null,
                'quantity'  => $qty,
                'price'     => $price,
                'discount'  => 0,
                'subtotal'  => 0,
                'unit'      => $product->unit ?? 'pcs',
            ];
        }

        $cart[$key]['subtotal'] =
            ($cart[$key]['price'] * $cart[$key]['quantity']) - ($cart[$key]['discount'] ?? 0);

        session()->put("cart.$warehouseId", $cart);

        return response()->json([
            'status' => 'success',
            'cart'   => $cart
        ]);
    }

    /**
     * Update cart (qty, discount, dll)
     */
    public function updateCart(Request $request, $id)
    {
        $warehouseId = session('active_warehouse_id');
        $cart = session()->get("cart.$warehouseId", []);

        if (!isset($cart[$id])) {
            return response()->json(['status' => 'error']);
        }

        $qty = max(1, (int) $request->quantity);
        $discount = max(0, (int) ($request->discount ?? 0));

        $cart[$id]['quantity'] = $qty;
        $cart[$id]['discount'] = $discount;
        $cart[$id]['subtotal'] =
            ($cart[$id]['price'] * $qty) - $discount;

        session()->put("cart.$warehouseId", $cart);

        return response()->json(['status' => 'success', 'cart' => $cart]);
    }

    /**
     * Hapus produk dari cart
     */
    public function removeFromCart($id)
    {
        $warehouseId = session('active_warehouse_id');
        $cart = session()->get("cart.$warehouseId", []);

        unset($cart[$id]);

        session()->put("cart.$warehouseId", $cart);

        return response()->json(['status' => 'success', 'cart' => $cart]);
    }

    /**
     * Kosongkan cart
     */
    public function clearCart()
    {
        $warehouseId = session('active_warehouse_id');
        session()->forget("cart.$warehouseId");

        return response()->json([
            'status' => 'success',
            'cart' => []
        ]);
    }

    /**
     * Checkout transaksi
     */
    public function checkout(Request $request)
    {
        $warehouseId = session('active_warehouse_id');

        if (!$warehouseId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Outlet belum dipilih'
            ]);
        }

        $cart = session()->get("cart.$warehouseId", []);

        if (empty($cart)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart masih kosong!'
            ]);
        }

        DB::beginTransaction();
        try {
            $subtotal = collect($cart)->sum('subtotal');
            $total = $subtotal;

            $uangDiterima = (int) $request->uang_diterima;
            $kembalian = max(0, $uangDiterima - $total);
            $isUtang = $uangDiterima < $total;

            // ✅ TRANSAKSI DENGAN OUTLET
            $transaction = Transaction::create([
                'invoice_number'   => 'INV' . time(),
                'transaction_type' => 'sale',
                'idpenginput'      => auth()->id(),
                'user_id'          => auth()->id(),
                'warehouse_id'     => $warehouseId, // ⬅️ PENTING
                'subtotal'         => $subtotal,
                'total'            => $total,
                'payment_status'   => $isUtang ? 'unpaid' : 'paid',
                'payment_method'   => $request->payment_method ?? 'cash',
                'uang_diterima'    => $uangDiterima,
                'kembalian'        => $kembalian,
                'customer_name'    => $isUtang ? $request->customer_name : null,
                'due_date'         => $isUtang ? $request->due_date : null,
                'status'           => 'completed',
            ]);

            foreach ($cart as $item) {

                $qty = (int) ($item['quantity'] ?? 0);
                if ($qty <= 0) {
                    throw new \Exception('Quantity tidak valid');
                }
            
                $productId   = null;
                $variationId = null;
            
                if ($item['type'] === 'variation') {
                    $variationId = $item['id'];
                    $variation   = ProductVariation::findOrFail($variationId);
                    $productId   = $variation->product_id;
                } else {
                    $productId = $item['id'];
                }
            
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $productId,
                    'variation_id'   => $variationId, // ✅ aman (nullable)
                    'quantity'       => $qty,
                    'price'          => $item['price'],
                    'discount'       => $item['discount'] ?? 0,
                    'subtotal'       => $item['subtotal'],
                    'unit'           => $item['unit'] ?? 'pcs',
                    'idpenginput'    => auth()->id(),
                ]);
            
                // 🔻 potong stok outlet aktif
                $wpQuery = DB::table('warehouse_products')
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId);
            
                $variationId
                    ? $wpQuery->where('variation_id', $variationId)
                    : $wpQuery->whereNull('variation_id');
            
                $wp = $wpQuery->first();
            
                if (!$wp || $wp->stock < $qty) {
                    throw new \Exception('Stok tidak mencukupi saat checkout');
                }
            
                DB::table('warehouse_products')
                    ->where('id', $wp->id)
                    ->decrement('stock', $qty);
            }            

            DB::commit();

            // ✅ HANYA HAPUS CART OUTLET INI
            session()->forget("cart.$warehouseId");

            return response()->json([
                'status' => 'success',
                'transaction_id' => $transaction->id,
                'total' => $total,
                'kembalian' => $kembalian
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
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
        $warehouseId = session('active_warehouse_id');

        // 🔒 Pastikan outlet aktif
        if (!$warehouseId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Outlet belum dipilih'
            ]);
        }

        $variation = ProductVariation::with(['product', 'options.attribute'])->findOrFail($id);

        // 🔹 Ambil cart PER OUTLET (INI KUNCI)
        $cart = session()->get("cart.$warehouseId", []);

        $qty = max(1, (int) ($request->qty ?? 1));

        // 🔹 Ambil stok variasi DARI OUTLET AKTIF
        $stock = DB::table('warehouse_products')
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $variation->product_id)
            ->where('variation_id', $variation->id)
            ->value('stock') ?? 0;

        // 🔹 Hitung qty varian yg sudah ada di cart outlet ini
        $cartQty = collect($cart)
            ->where('type', 'variation')
            ->where('id', $variation->id)
            ->sum('quantity');

        $available = $stock - $cartQty;

        if ($available < $qty) {
            return response()->json([
                'status' => 'error',
                'message' => "Stok varian tidak mencukupi! (tersisa: {$available})"
            ]);
        }

        // 🔹 Label variasi
        $optionLabels = $variation->options->map(fn($opt) => $opt->value)->implode(' / ');
        $weightText   = $variation->weight ? ' [ ' . number_format($variation->weight) . ' gr ]' : '';
        $variationLabel = trim($optionLabels . $weightText);

        $price = $variation->price ?? $variation->product->price;

        $key = "variation_{$variation->id}";

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $qty;
        } else {
            $cart[$key] = [
                'id'        => $variation->id,
                'type'      => 'variation',
                'name'      => ($variation->product->sku ?? '-') . ' / ' . $variation->product->name,
                'variation' => $variationLabel,
                'quantity'  => $qty,
                'price'     => $price,
                'subtotal'  => 0,
                'unit'      => $variation->product->unit ?? 'pcs',
            ];
        }

        $cart[$key]['subtotal'] = $cart[$key]['price'] * $cart[$key]['quantity'];

        // 🔥 SIMPAN KE CART OUTLET
        session()->put("cart.$warehouseId", $cart);

        return response()->json([
            'status' => 'success',
            'cart'   => $cart
        ]);
    }

    public function updateDiscount(Request $request)
    {
        $validated = $request->validate([
            'product_id'   => 'required|exists:products,id',
            'discount_price' => 'nullable|numeric|min:0',
            'is_promo'     => 'nullable|boolean',
            'promo_price'  => 'nullable|numeric|min:0',
            'promo_start'  => 'nullable|date',
            'promo_end'    => 'nullable|date|after_or_equal:promo_start',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Simpan harga diskon
        $product->discount_price = $validated['discount_price'] ?? null;

        // Simpan promo
        $product->is_promo    = $request->has('is_promo') ? 1 : 0;
        $product->promo_price = $validated['promo_price'] ?? null;
        $product->promo_start = $validated['promo_start'] ?? null;
        $product->promo_end   = $validated['promo_end'] ?? null;

        $product->save();

        return redirect()->back()->with('success', 'Harga dan promo berhasil diperbarui.');
    }

    public function addStock(Request $request)
    {
        $validated = $request->validate([
            'id'    => 'required|integer',
            'type'  => 'required|string|in:product,variation',
            'stock' => 'required|integer|min:1'
        ]);

        $item = $validated['type'] === 'product'
            ? Product::find($validated['id'])
            : ProductVariation::find($validated['id']);

        if (!$item) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        // Update stok
        $item->increment('stock', $validated['stock']);

        // Catat transaksi stok
        StockTransaction::create([
            'item_type'        => $validated['type'],  // ✅ konsisten pakai string
            'item_id'          => $item->id,
            'transaction_type' => 'in',
            'quantity'         => $validated['stock'],
            'note'             => 'Penambahan stok manual',
            'user_id'          => auth()->id()
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Stok berhasil ditambahkan',
            'new_stock' => $item->stock
        ]);
    }

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'product_id'       => 'required|string', // format: product-12 / variation-5
            'transaction_type' => 'required|in:in,out,adjust',
            'quantity'         => 'required|integer|min:1',
            'supplier'         => 'nullable|string|max:100',
            'note'             => 'nullable|string|max:255'
        ]);

        [$type, $id] = explode('-', $validated['product_id']);
        if (!in_array($type, ['product','variation'])) {
            return response()->json(['status'=>'error','message'=>'Jenis item tidak valid'],422);
        }

        $item = $type === 'product'
            ? Product::find($id)
            : ProductVariation::find($id);

        if (!$item) {
            return response()->json(['status'=>'error','message'=>'Data tidak ditemukan'],404);
        }

        $qty      = (int) $validated['quantity'];
        $newStock = (int) $item->stock;

        // === Hitung stok baru ===
        switch ($validated['transaction_type']) {
            case 'in':
                $newStock += $qty;
                break;
            case 'out':
                if ($qty > $newStock) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Jumlah keluar melebihi stok tersedia'
                    ],422);
                }
                $newStock -= $qty;
                break;
            case 'adjust':
                $newStock = $qty;
                break;
        }

        // === Update stok item (produk atau variasi) ===
        $item->update(['stock' => $newStock]);

        // ✅ Jangan update stok produk induk meskipun item adalah variasi
        // karena produk dan variasi punya stok masing-masing

        // === Catat transaksi stok ===
        StockTransaction::create([
            'item_type'        => $type,
            'item_id'          => $item->id,
            'transaction_type' => $validated['transaction_type'],
            'quantity'         => $qty,
            'supplier'         => $validated['supplier'],
            'note'             => $validated['note'],
            'user_id'          => auth()->id()
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Transaksi berhasil disimpan',
            'new_stock' => $newStock
        ]);
    }

    public function listTransactions(Request $request)
    {
        // Ambil filter tanggal dari query string
        $start = $request->query('start'); // format: Y-m-d
        $end   = $request->query('end');   // format: Y-m-d

        $query = StockTransaction::query();

        // Filter jika start & end tersedia
        if($start && $end){
            $query->whereDate('created_at', '>=', $start)
                ->whereDate('created_at', '<=', $end);
        }

        // Ambil data terbaru (limit 50 terakhir, bisa ditambah pagination)
        $data = $query->latest()
                    ->take(50)
                    ->get()
                    ->map(function ($t) {
                        if ($t->item_type === 'product') {
                            $product = Product::find($t->item_id);
                            $productName = $product->name ?? '-';
                            $variationName = null;
                            $weight = null;
                        } else {
                            $variation = ProductVariation::find($t->item_id);
                            $productName = optional($variation->product)->name ?? '-';
                            $variationName = $variation->name ?? '-';
                            $weight = $variation->weight ? number_format($variation->weight, 0) : null;
                        }

                        return [
                            'created_at'     => $t->created_at->format('Y-m-d H:i'),
                            'product_name'   => $productName,
                            'variation_name' => $variationName,
                            'weight'         => $weight ? $weight . ' g' : null,
                            'type'           => strtoupper($t->transaction_type),
                            'quantity'       => $t->quantity,
                            'supplier'       => $t->supplier,
                            'note'           => $t->note,
                            'user'           => optional($t->user)->name ?? 'System'
                        ];
                    });

        return response()->json($data);
    }

    public function summary()
    {
        // 1️⃣ Total produk
        $totalProduk = Product::count();

        // 2️⃣ Total stok produk utama
        $stokProdukUtama = 0;

        // 🔹 Kalau products punya kolom stock
        if (Schema::hasColumn('products', 'stock')) {
            $stokProdukUtama = Product::sum('stock');
        }
        // 🔹 Kalau tidak, ambil dari warehouse_transfers (produk tanpa variasi)
        else if (Schema::hasTable('warehouse_transfers')) {
            $stokProdukUtama = DB::table('warehouse_transfers')
                ->whereNull('variation_id')
                ->where('status', 'received')
                ->sum('quantity');
        }

        // 3️⃣ Total stok variasi (kalau tabelnya ada)
        $stokVariasi = 0;
        if (Schema::hasTable('product_variations') && Schema::hasColumn('product_variations', 'stock')) {
            $stokVariasi = ProductVariation::sum('stock');
        }

        // 4️⃣ Total stok gabungan
        $totalStok = $stokProdukUtama + $stokVariasi;

        // 5️⃣ Total transaksi hari ini
        $totalTransaksi = 0;
        if (Schema::hasTable('stock_transactions')) {
            $today = Carbon::today();
            $totalTransaksi = StockTransaction::whereDate('created_at', $today)->count();
        }

        return response()->json([
            'totalProduk'     => $totalProduk,
            'totalStok'       => $totalStok,
            'totalTransaksi'  => $totalTransaksi,
        ]);
    }

    public function productReport(Request $request)
    {
        // Parse tanggal (default: awal bulan -> sekarang)
        $start = Carbon::parse($request->query('start', Carbon::now()->startOfMonth()->toDateString()))->startOfDay();
        $end   = Carbon::parse($request->query('end',   Carbon::now()->toDateString()))->endOfDay();

        // Total pergerakan stok (stock transactions) & total penjualan (transaction_items)
        $totalStockMovement = StockTransaction::whereBetween('created_at', [$start, $end])->count();
        $totalSales = TransactionItem::whereBetween('created_at', [$start, $end])->sum('quantity');

        $details = collect();

        // Ambil semua produk (eager load variasi + opsi)
        $products = Product::with('variations.options.attribute')->get();

        // Untuk menentukan best seller nanti
        $totalsForRanking = collect();

        foreach ($products as $p) {
            // --- Produk utama (tanpa variasi) ---
            $soldProducts = TransactionItem::whereBetween('created_at', [$start, $end])
                ->where('product_id', $p->id)
                ->whereNull('variation_id')
                ->sum('quantity');

            $revenueProducts = TransactionItem::whereBetween('created_at', [$start, $end])
                ->where('product_id', $p->id)
                ->whereNull('variation_id')
                ->sum(DB::raw('price * quantity'));

            // stock transactions untuk produk (masuk/keluar/adjust) dalam periode
            $masuk = StockTransaction::whereBetween('created_at', [$start, $end])
                ->where('item_type', 'product')
                ->where('item_id', $p->id)
                ->where('transaction_type', 'in')
                ->sum('quantity');

            $keluar = StockTransaction::whereBetween('created_at', [$start, $end])
                ->where('item_type', 'product')
                ->where('item_id', $p->id)
                ->where('transaction_type', 'out')
                ->sum('quantity');

            $adjust = StockTransaction::whereBetween('created_at', [$start, $end])
                ->where('item_type', 'product')
                ->where('item_id', $p->id)
                ->where('transaction_type', 'adjust')
                ->sum('quantity');

            // stock start = stock_end - masuk + keluar  (agar persisten)
            $stockEnd = (int) $p->stock;
            $stockStart = $stockEnd - (int)$masuk + (int)$keluar;

            $details->push([
                'type'        => 'Produk',
                'name'        => $p->name,
                'sku'         => $p->sku,
                'sold'        => (int) $soldProducts,
                'stock_start' => max(0, (int) $stockStart),
                'stock_end'   => $stockEnd,
                'masuk'       => (int) $masuk,
                'keluar'      => (int) $keluar,
                'adjust'      => (int) $adjust,
                'revenue'     => (float) $revenueProducts,
            ]);

            $totalsForRanking->push([
                'type' => 'product',
                'id'   => $p->id,
                'name' => $p->name,
                'total'=> (int) $soldProducts,
            ]);

            // --- Variasi produk (jika ada) ---
            foreach ($p->variations as $v) {
                $soldVar = TransactionItem::whereBetween('created_at', [$start, $end])
                    ->where('variation_id', $v->id)
                    ->sum('quantity');

                $revenueVar = TransactionItem::whereBetween('created_at', [$start, $end])
                    ->where('variation_id', $v->id)
                    ->sum(DB::raw('price * quantity'));

                $masukV = StockTransaction::whereBetween('created_at', [$start, $end])
                    ->where('item_type', 'variation')
                    ->where('item_id', $v->id)
                    ->where('transaction_type', 'in')
                    ->sum('quantity');

                $keluarV = StockTransaction::whereBetween('created_at', [$start, $end])
                    ->where('item_type', 'variation')
                    ->where('item_id', $v->id)
                    ->where('transaction_type', 'out')
                    ->sum('quantity');

                $adjustV = StockTransaction::whereBetween('created_at', [$start, $end])
                    ->where('item_type', 'variation')
                    ->where('item_id', $v->id)
                    ->where('transaction_type', 'adjust')
                    ->sum('quantity');

                $stockEndV = (int) $v->stock;
                $stockStartV = $stockEndV - (int)$masukV + (int)$keluarV;

                // format options string (e.g. "Tipe Harga: losan / Ukuran: 144")
                $optionsArr = $v->options->map(function($opt){
                    return ($opt->attribute->name ?? '') . ': ' . ($opt->value ?? '');
                })->toArray();

                $details->push([
                    'type'        => 'Variasi',
                    'name'        => ($v->product->name ?? '-') . ' - ' . $v->name,
                    'sku'         => $v->sku ?? null,
                    'options'     => $optionsArr,
                    'weight'      => $v->weight ?? null,
                    'sold'        => (int) $soldVar,
                    'stock_start' => max(0, (int) $stockStartV),
                    'stock_end'   => $stockEndV,
                    'masuk'       => (int) $masukV,
                    'keluar'      => (int) $keluarV,
                    'adjust'      => (int) $adjustV,
                    'revenue'     => (float) $revenueVar,
                ]);

                $totalsForRanking->push([
                    'type' => 'variation',
                    'id'   => $v->id,
                    'name' => ($v->product->name ?? '-') . ' - ' . $v->name,
                    'total'=> (int) $soldVar,
                ]);
            }
        }

        // Best seller (gabungan product + variation) berdasarkan total terjual
        $best = $totalsForRanking->sortByDesc('total')->first();
        $bestName = $best ? $best['name'] . ' (' . $best['type'] . ')' : '-';

        // Chart data: penjualan per tanggal (gabungan)
        $chart = TransactionItem::selectRaw('DATE(created_at) as tanggal, SUM(quantity) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->map(function($r){
                return ['tanggal' => $r->tanggal, 'total' => (int) $r->total];
            });

        return response()->json([
            'bestProduct'        => $bestName,
            'totalSales'         => (int) $totalSales,
            'totalStockMovement' => (int) $totalStockMovement,
            'details'            => $details->values(), // koleksi produk+variasi
            'chart'              => $chart
        ]);
    }

    public function receipt($id)
    {
        $transaction = Transaction::with('items.product', 'items.variation')->findOrFail($id);
        return view('umkm.receipt', compact('transaction'));
    }

}