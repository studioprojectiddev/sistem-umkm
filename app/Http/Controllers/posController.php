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
        // 🔹 Ambil semua produk
        $products = Product::with([
            'category',
            'variations.options.attribute'
        ])->select(
            'id', 'name', 'sku', 'price', 'cost_price', 'discount_price',
            'is_promo', 'promo_price', 'promo_start', 'promo_end',
            'thumbnail', 'unit'
        )->get();

        // 🔹 Ambil total stok masuk ke toko (warehouse type = store)
        $warehouseStock = DB::table('warehouse_transfers')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_transfers.to_warehouse_id')
            ->where('warehouses.type', 'store')
            ->where('warehouse_transfers.status', 'received')
            ->select(
                'warehouse_transfers.product_id',
                'warehouse_transfers.variation_id',
                DB::raw('SUM(warehouse_transfers.quantity) as total_in')
            )
            ->groupBy('warehouse_transfers.product_id', 'warehouse_transfers.variation_id')
            ->get();

        // 🔹 Ambil total barang yang terjual dari transaksi
        $sales = DB::table('transaction_items')
            ->select(
                'product_id',
                'variation_id',
                DB::raw('SUM(quantity) as total_sold')
            )
            ->groupBy('product_id', 'variation_id')
            ->get();

        // 🔹 Ubah hasil stok masuk dan stok keluar ke bentuk map agar mudah dipakai
        $stockMap = [];
        foreach ($warehouseStock as $s) {
            $key = $s->product_id . '-' . ($s->variation_id ?? 0);
            $stockMap[$key]['in'] = $s->total_in;
        }

        foreach ($sales as $s) {
            $key = $s->product_id . '-' . ($s->variation_id ?? 0);
            $stockMap[$key]['sold'] = $s->total_sold;
        }

        // 🔹 Tambahkan informasi stok aktual & harga ke setiap produk
        $products->each(function ($product) use ($stockMap) {
            // Harga final (cek promo)
            $product->final_price = ($product->is_promo && $product->promo_start <= now() && $product->promo_end >= now())
                ? $product->promo_price
                : $product->price;

            // Produk tanpa variasi
            $key = $product->id . '-0';
            $total_in = $stockMap[$key]['in'] ?? 0;
            $total_sold = $stockMap[$key]['sold'] ?? 0;
            $product->stock = max(0, $total_in - $total_sold);

            // Produk dengan variasi
            $product->variation_json = $product->variations->map(function ($v, $index) use ($stockMap, $product) {
                $key = $product->id . '-' . $v->id;
                $total_in = $stockMap[$key]['in'] ?? 0;
                $total_sold = $stockMap[$key]['sold'] ?? 0;
                $v_stock = max(0, $total_in - $total_sold);

                return [
                    "no"           => $index + 1,
                    "variation_id" => $v->id,
                    "name"         => $v->name,
                    "price"        => $v->price,
                    "stock"        => $v_stock,
                    "sold"         => $total_sold,
                    "weight"       => $v->weight ?? 0,
                    "options"      => $v->options->map(function ($opt) {
                        return [
                            "attribute" => $opt->attribute->name,
                            "value"     => $opt->value,
                        ];
                    })->toArray()
                ];
            })->toArray();

            // Hitung total terjual produk (tanpa variasi + variasi)
            $product->total_sold = ($stockMap[$key]['sold'] ?? 0)
                + collect($product->variation_json)->sum('sold');
        });

        // 🔹 Data dikirim ke JavaScript
        $productsForJs = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'thumbnail' => $product->thumbnail,
                'category' => $product->category->name ?? '-',
                'sku' => $product->sku ?? $product->barcode ?? '-',
                'price' => $product->price,
                'final_price' => $product->final_price,
                'stock' => $product->stock,
                'unit' => $product->unit ?? 'pcs',
                'variations' => $product->variation_json,
            ];
        })->toArray();

        $cart = session()->get('cart', []);

        return view('umkm.pos', compact('products', 'cart', 'productsForJs'));
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

        $qty = max(1, (int) $request->qty); // pastikan qty minimal 1

        // 🔹 Hitung stok produk dari warehouse_transfers yang dikirim ke toko
        $availableStock = DB::table('warehouse_transfers')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_transfers.to_warehouse_id')
            ->where('warehouses.type', 'store')
            ->where('warehouse_transfers.product_id', $id)
            ->whereNull('warehouse_transfers.variation_id')
            ->where('warehouse_transfers.status', 'received')
            ->sum('warehouse_transfers.quantity');

        // 🔹 Kurangi dengan total yang sudah ada di keranjang
        $cartQty = collect($cart)
            ->where('type', 'product')
            ->where('id', $id)
            ->sum('quantity');

        if ($availableStock - $cartQty < $qty) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stok di toko tidak mencukupi! (tersisa: ' . ($availableStock - $cartQty) . ')'
            ]);
        }

        // 🔹 Tentukan harga promo bila berlaku
        $today = now();
        $price = $product->price;
        if ($product->is_promo && $product->promo_price && $product->promo_start <= $today && $product->promo_end >= $today) {
            $price = $product->promo_price;
        }

        // 🔹 Tambahkan ke keranjang
        $key = 'product_' . $product->id;
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $qty;
        } else {
            $cart[$key] = [
                "id"        => $product->id,
                "type"      => "product",
                "name"      => ($product->sku ?? $product->id) . ' / ' . $product->name,
                "variation" => null,
                "quantity"  => $qty,
                "price"     => $price,
                "thumbnail" => $product->thumbnail,
                "discount"  => 0,
                "subtotal"  => $price * $qty,
                "unit"      => $product->unit ?? 'pcs'
            ];
        }

        $cart[$key]['subtotal'] = ($cart[$key]['price'] * $cart[$key]['quantity']) - $cart[$key]['discount'];
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
            'payment_method' => 'nullable|string|in:cash,transfer,qris,ewallet',
            'uang_diterima'  => 'nullable|numeric|min:0',
            'customer_name'  => 'nullable|string|max:255',
            'due_date'       => 'nullable|date'
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

            $uangDiterima = $request->input('uang_diterima', 0);
            $kembalian = max(0, $uangDiterima - $total);

            // Cek apakah pembayaran kurang (utang)
            $isUtang = $uangDiterima < $total;

            // Simpan transaksi utama
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
                'payment_status'   => $isUtang ? 'unpaid' : 'paid',
                'payment_method'   => $request->payment_method ?? 'cash',
                'status'           => 'completed',
                'uang_diterima'    => $uangDiterima,
                'kembalian'        => $kembalian,
                'customer_name'    => $isUtang ? ($request->customer_name ?? 'Pelanggan Tidak Dikenal') : null,
                'due_date'         => $isUtang ? ($request->due_date ?? now()->addDays(7)) : null, // default 7 hari jika tidak diisi
            ]);

            // Ambil gudang toko (store)
            $storeWarehouse = Warehouse::where('type', 'store')->first();
            if (!$storeWarehouse) {
                throw new \Exception("Warehouse bertipe 'store' (toko) tidak ditemukan. Pastikan ada data gudang tipe store.");
            }
            $storeWarehouseId = $storeWarehouse->id;

            // Loop item dalam cart => validasi & kurangi stok di warehouse_products
            foreach ($cart as $item) {
                $productId   = null;
                $variationId = null;
                $qty = (int) ($item['quantity'] ?? 0);
                if ($qty <= 0) {
                    throw new \Exception("Quantity tidak valid untuk item: " . json_encode($item));
                }

                if ($item['type'] === 'variation') {
                    $variation = ProductVariation::with('product')->find($item['id']);
                    if (!$variation) {
                        throw new \Exception("Varian tidak ditemukan (ID: {$item['id']})");
                    }
                    $productId   = $variation->product_id;
                    $variationId = $variation->id;
                } else {
                    $productId = $item['id'];
                }

                // Simpan detail item transaksi
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'idpenginput'    => auth()->id(),
                    'product_id'     => $productId,
                    'variation_id'   => $variationId,
                    'quantity'       => $qty,
                    'price'          => $item['price'],
                    'discount'       => $item['discount'] ?? 0,
                    'subtotal'       => $item['subtotal'],
                    'unit'           => $item['unit'] ?? 'pcs'
                ]);

                // Ambil stok dari gudang toko
                $wpQuery = DB::table('warehouse_products')
                    ->where('warehouse_id', $storeWarehouseId)
                    ->where('product_id', $productId);

                if ($variationId) {
                    $wpQuery->where('variation_id', $variationId);
                } else {
                    $wpQuery->whereNull('variation_id');
                }

                $wp = $wpQuery->first();

                if (!$wp) {
                    throw new \Exception("Stok produk belum terdaftar di gudang toko (product_id: {$productId}, variation_id: " . ($variationId ?? 'null') . ").");
                }

                if ((int)$wp->stock < $qty) {
                    throw new \Exception("Stok tidak mencukupi untuk produk ID: {$productId}" . ($variationId ? " (variasi {$variationId})" : "") . ". Tersedia: {$wp->stock}, diminta: {$qty}");
                }

                // Kurangi stok di warehouse_products
                DB::table('warehouse_products')
                    ->where('id', $wp->id)
                    ->decrement('stock', $qty);

                // Update ringkasan penjualan harian
                $summary = ProductSalesSummary::firstOrNew([
                    'product_id'   => $productId,
                    'variation_id' => $variationId,
                    'date'         => now()->toDateString()
                ]);
                $summary->idpenginput   = auth()->id();
                $summary->total_qty     = ($summary->total_qty ?? 0) + $qty;
                $summary->total_sales   = ($summary->total_sales ?? 0) + $item['subtotal'];
                $summary->save();
            }

            DB::commit();
            session()->forget('cart');

            return response()->json([
                'status'          => 'success',
                'message'         => $isUtang ? 'Transaksi berhasil (utang tercatat).' : 'Transaksi berhasil!',
                'transaction_id'  => $transaction->id,
                'total'           => $total,
                'uang_diterima'   => $uangDiterima,
                'kembalian'       => $kembalian,
                'payment_status'  => $transaction->payment_status,
                'customer_name'   => $transaction->customer_name,
                'due_date'        => $transaction->due_date,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
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
        $variation = ProductVariation::with(['product', 'options.attribute'])->findOrFail($id);
        $cart = session()->get('cart', []);

        $qty = max(1, (int) $request->qty); // ✅ Pastikan qty dikirim & minimal 1

        // 🔹 Hitung stok variasi dari transfer ke toko (warehouse type = store)
        $availableStock = DB::table('warehouse_transfers')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_transfers.to_warehouse_id')
            ->where('warehouses.type', 'store')
            ->where('warehouse_transfers.variation_id', $id)
            ->where('warehouse_transfers.status', 'received')
            ->sum('warehouse_transfers.quantity');

        // 🔹 Kurangi dengan yang sudah ada di keranjang
        $cartQty = collect($cart)
            ->where('type', 'variation')
            ->where('id', $id)
            ->sum('quantity');

        if ($availableStock - $cartQty < $qty) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stok varian ini tidak mencukupi! (tersisa: ' . ($availableStock - $cartQty) . ')'
            ]);
        }

        // 🔹 Buat label variasi
        $optionLabels = $variation->options->map(fn($opt) => $opt->value)->implode(' / ');
        $weightText   = $variation->weight ? ' [ ' . number_format($variation->weight, 0, ',', '.') . ' gr ]' : '';
        $variationLabel = trim($optionLabels . $weightText);

        // 🔹 Gunakan harga variasi atau fallback ke harga produk
        $price = $variation->price ?? $variation->product->price;

        $key = "variation_{$variation->id}";

        // 🔹 Tambahkan atau update quantity
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $qty;
        } else {
            $cart[$key] = [
                "id"        => $variation->id,
                "type"      => "variation",
                "name"      => $variation->product->name ?? 'Produk',
                "variation" => $variationLabel,
                "quantity"  => $qty,
                "price"     => $price,
                "thumbnail" => $variation->image ?? ($variation->product->thumbnail ?? null),
                "discount"  => 0,
                "subtotal"  => $price * $qty,
                "unit"      => $variation->product->unit ?? 'pcs'
            ];
        }

        // 🔹 Hitung ulang subtotal
        $cart[$key]['subtotal'] = ($cart[$key]['price'] * $cart[$key]['quantity']) - $cart[$key]['discount'];

        session()->put('cart', $cart);

        return response()->json(['status' => 'success', 'cart' => $cart]);
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
        // ✅ Hitung total produk utama
        $totalProduk = Product::count();

        // ✅ Hitung total stok (produk utama + seluruh variasi)
        $totalStok = Product::sum('stock') + ProductVariation::sum('stock');

        // ✅ Hitung jumlah transaksi stok hari ini
        $today = Carbon::today();
        $totalTransaksi = StockTransaction::whereDate('created_at', $today)->count();

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