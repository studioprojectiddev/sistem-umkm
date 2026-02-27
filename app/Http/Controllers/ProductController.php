<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;
use App\Models\ProductSalesSummary;
use App\Models\Category;
use App\Models\ProductVariation;
use App\Models\ProductVariationOption;
use App\Models\VariationAttribute;
use App\Models\VariationOption; 
use App\Models\Transaction;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Models\WarehouseTransfer;
use App\Models\TransactionItem;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $id = null)
    {
        // ==========================
        // 🔹 Query daftar produk
        // ==========================
        $query = Product::with('category')
            ->where('user_id', auth()->id());

        // Filter berdasarkan nama produk
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter berdasarkan kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter berdasarkan status aktif/tidak aktif produk
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $products = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();


        // ==========================
        // 🔹 Query daftar variasi
        // ==========================
        $variations = ProductVariation::with([
                'product:id,name',
                'options.attribute'
            ])
            ->whereHas('product', function ($q) {
                $q->where('user_id', auth()->id());
            });

        // Filter by Nama Produk saja
        if ($request->filled('product_name')) {
            $search = $request->product_name;
            $variations->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter by Attribute
        if ($request->filled('attribute')) {
            $attrId = $request->attribute;
            $variations->whereHas('options', function ($q) use ($attrId) {
                $q->where('attribute_id', $attrId);
            });
        }

        // Filter berdasarkan status aktif/tidak aktif variasi
        if ($request->filled('variation_active')) {
            $variations->where('is_active', $request->variation_active);
        }

        $variations = $variations->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'variations_page')
            ->withQueryString();


        // ==========================
        // 🔹 Data tambahan
        // ==========================
        $variationAttributes = VariationAttribute::with('options')->get();
        $variationOptions = VariationOption::all();

        // 🔹 Kalau ada ID (mode edit), ambil produk + variasinya
        $product = null;
        if ($id) {
            $product = Product::with([
                'variations.options.attribute'
            ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        }

        // ==========================
        // 🔹 Return ke view
        // ==========================
        return view('umkm.products.index', compact(
            'products',
            'categories',
            'variations',
            'variationAttributes',
            'variationOptions',
            'product'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'slug'           => 'nullable|string|unique:products,slug',
            'sku'            => 'nullable|string|unique:products,sku',
            'barcode'        => 'nullable|string|unique:products,barcode',
            'description'    => 'nullable|string',
            'category_id'    => 'nullable|exists:categories,id',
            'price'          => 'required|numeric',
            'discount_price' => 'nullable|numeric',
            'cost_price'     => 'nullable|numeric',
            'unit'           => 'nullable|string|max:50',
            'product_type'   => 'required|in:goods,service',
            'expiry_date'    => 'nullable|date',
            'batch_number'   => 'nullable|string',
            'thumbnail'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active'      => 'nullable|boolean',
            'is_featured'    => 'nullable|boolean',
            'is_promo'       => 'nullable|boolean',
            'promo_price'    => 'nullable|numeric',
            'promo_start'    => 'nullable|date',
            'promo_end'      => 'nullable|date',
            'meta_title'     => 'nullable|string|max:255',
            'meta_keywords'  => 'nullable|string',
            'meta_description' => 'nullable|string',
        ]);

        // Generate slug jika kosong
        $slug = $request->slug ?: Str::slug($request->name) . '-' . Str::random(5);

        // Generate barcode otomatis jika kosong
        $barcodeValue = $request->barcode ?: 'BAR' . time() . rand(100, 999);

        // Handle thumbnail
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $filename = time() . '_' . $request->file('thumbnail')->getClientOriginalName();
            $request->file('thumbnail')->move(public_path('assets/images/product'), $filename);
            $thumbnailPath = 'assets/images/product/' . $filename;
        }

        // Handle multiple images
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $filename = time() . '_' . $img->getClientOriginalName();
                $img->move(public_path('assets/images/product'), $filename);
                $images[] = 'assets/images/product/' . $filename;
            }
        }

        // Generate barcode image
        $barcodeDir = public_path('assets/images/product/barcode');
        if (!file_exists($barcodeDir)) {
            mkdir($barcodeDir, 0755, true);
        }
        $barcodeFile = $barcodeDir . '/' . $barcodeValue . '.png';
        $generator = new BarcodeGeneratorPNG();
        file_put_contents($barcodeFile, $generator->getBarcode($barcodeValue, $generator::TYPE_CODE_128));

        // Simpan produk
        Product::create([
            'idpenginput'     => auth()->id(),
            'name'            => $request->name,
            'slug'            => $slug,
            'sku'             => $request->sku,
            'barcode'         => $barcodeValue,
            'description'     => $request->description,
            'user_id'         => auth()->id(),
            'category_id'     => $request->category_id,
            'price'           => $request->price,
            'discount_price'  => $request->discount_price,
            'cost_price'      => $request->cost_price,
            'unit'            => $request->unit ?? 'pcs',
            'product_type'    => $request->product_type ?? 'goods',
            'expiry_date'     => $request->expiry_date,
            'batch_number'    => $request->batch_number,
            'thumbnail'       => $thumbnailPath,
            'images'          => !empty($images) ? json_encode($images) : null,
            'attributes'      => $request->attributes ? json_encode($request->attributes) : null,
            'is_active'       => $request->is_active ?? true,
            'is_featured'     => $request->is_featured ?? false,
            'is_promo'        => $request->is_promo ?? false,
            'promo_price'     => $request->promo_price,
            'promo_start'     => $request->promo_start,
            'promo_end'       => $request->promo_end,
            'meta_title'      => $request->meta_title,
            'meta_keywords'   => $request->meta_keywords,
            'meta_description'=> $request->meta_description,
            'ai_insights'     => null,
        ]);

        return redirect()->route('umkm.product')->with('success', 'Produk berhasil ditambahkan beserta barcode.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'slug'           => 'nullable|string|unique:products,slug,'.$id,
            'sku'            => 'nullable|string|unique:products,sku,'.$id,
            'barcode'        => 'nullable|string|unique:products,barcode,'.$id,
            'description'    => 'nullable|string',
            'category_id'    => 'nullable|exists:categories,id',
            'price'          => 'required|numeric',
            'discount_price' => 'nullable|numeric',
            'cost_price'     => 'nullable|numeric',
            'unit'           => 'nullable|string|max:50',
            'product_type'   => 'required|in:goods,service',
            'expiry_date'    => 'nullable|date',
            'batch_number'   => 'nullable|string',
            'thumbnail'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active'      => 'nullable|boolean',
            'is_featured'    => 'nullable|boolean',
            'is_promo'       => 'nullable|boolean',
            'promo_price'    => 'nullable|numeric',
            'promo_start'    => 'nullable|date',
            'promo_end'      => 'nullable|date',
            'meta_title'     => 'nullable|string|max:255',
            'meta_keywords'  => 'nullable|string',
            'meta_description' => 'nullable|string',
        ]);

        $product = Product::findOrFail($id);

        // Generate slug jika kosong
        $slug = $request->slug ?: Str::slug($request->name) . '-' . Str::random(5);

        // Generate barcode jika kosong
        $barcodeValue = $request->barcode ?: $product->barcode ?: 'BAR' . time() . rand(100, 999);

        // Handle thumbnail
        if ($request->hasFile('thumbnail')) {
            $filename = time() . '_' . $request->file('thumbnail')->getClientOriginalName();
            $request->file('thumbnail')->move(public_path('assets/images/product'), $filename);
            // hapus thumbnail lama
            if ($product->thumbnail && file_exists(public_path($product->thumbnail))) {
                unlink(public_path($product->thumbnail));
            }
            $product->thumbnail = 'assets/images/product/' . $filename;
        }

        // Handle gambar tambahan
        if ($request->hasFile('image')) {
            $filename = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('assets/images/product'), $filename);
            // hapus gambar lama jika ada
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }
            $product->image = 'assets/images/product/' . $filename;
        }

        // Update data lainnya
        $product->name           = $request->name;
        $product->slug           = $slug;
        $product->sku            = $request->sku;
        $product->barcode        = $barcodeValue;
        $product->description    = $request->description;
        $product->category_id    = $request->category_id;
        $product->price          = $request->price;
        $product->discount_price = $request->discount_price;
        $product->cost_price     = $request->cost_price;
        $product->unit           = $request->unit ?? 'pcs';
        $product->product_type   = $request->product_type;
        $product->expiry_date    = $request->expiry_date;
        $product->batch_number   = $request->batch_number;
        $product->is_active      = $request->is_active ?? true;
        $product->is_featured    = $request->is_featured ?? false;
        $product->is_promo       = $request->is_promo ?? false;

        // Promo handling
        if ($product->is_promo) {
            $product->promo_price = $request->promo_price;
            $product->promo_start = $request->promo_start;
            $product->promo_end   = $request->promo_end;
        } else {
            $product->promo_price = null;
            $product->promo_start = null;
            $product->promo_end   = null;
        }

        $product->meta_title      = $request->meta_title;
        $product->meta_keywords   = $request->meta_keywords;
        $product->meta_description= $request->meta_description;

        $product->save();

        return redirect()->route('umkm.product')->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('umkm.product')->with('success', 'Produk berhasil dihapus');
    }

    public function category(Request $request)
    {
        $query = Category::with('parent')
        ->where('idpenginput', auth()->id())
        ->orderBy('sort_order', 'asc');

        // Filter nama kategori
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter parent
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $categories = $query->paginate(10);

        // Untuk select parent filter
        $parents = Category::whereNull('parent_id')->get();

        return view('umkm.products.category', compact('categories', 'parents'));
    }

    public function categoryStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'code' => 'nullable|string|max:50|unique:categories,code',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string',
            'meta_description' => 'nullable|string',
        ]);

        // Default null
        $iconPath = null;
        $bannerPath = null;

        // Simpan icon
        if ($request->hasFile('icon')) {
            $iconFile = $request->file('icon');
            $iconName = time() . '_icon_' . $iconFile->getClientOriginalName();
            $iconFile->move(public_path('assets/images/categories'), $iconName);
            $iconPath = 'assets/images/categories/' . $iconName;
        }

        // Simpan banner
        if ($request->hasFile('banner')) {
            $bannerFile = $request->file('banner');
            $bannerName = time() . '_banner_' . $bannerFile->getClientOriginalName();
            $bannerFile->move(public_path('assets/images/categories'), $bannerName);
            $bannerPath = 'assets/images/categories/' . $bannerName;
        }

        // Generate slug otomatis kalau kosong
        $slug = $request->slug ?: \Str::slug($request->name);

        // Simpan kategori
        Category::create([
            'idpenginput' => auth()->id(),
            'name' => $request->name,
            'slug' => $slug,
            'code' => $request->code,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'icon' => $iconPath,
            'banner' => $bannerPath,
            'is_active' => $request->is_active ?? 1,
            'sort_order' => $request->sort_order ?? 0,
            'meta_title' => $request->meta_title,
            'meta_keywords' => $request->meta_keywords,
            'meta_description' => $request->meta_description,
        ]);

        return redirect()->route('umkm.category')->with('success', 'Kategori berhasil ditambahkan');
    }

    public function categoryUpdate(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'code' => 'nullable|string|max:50|unique:categories,code,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string',
            'meta_description' => 'nullable|string',
        ]);

        // Default pakai file lama
        $iconPath = $category->icon;
        $bannerPath = $category->banner;

        // Update icon jika ada file baru
        if ($request->hasFile('icon')) {
            if ($iconPath && file_exists(public_path($iconPath))) {
                unlink(public_path($iconPath));
            }
            $iconFile = $request->file('icon');
            $iconName = time() . '_icon_' . $iconFile->getClientOriginalName();
            $iconFile->move(public_path('assets/images/categories'), $iconName);
            $iconPath = 'assets/images/categories/' . $iconName;
        }

        // Update banner jika ada file baru
        if ($request->hasFile('banner')) {
            if ($bannerPath && file_exists(public_path($bannerPath))) {
                unlink(public_path($bannerPath));
            }
            $bannerFile = $request->file('banner');
            $bannerName = time() . '_banner_' . $bannerFile->getClientOriginalName();
            $bannerFile->move(public_path('assets/images/categories'), $bannerName);
            $bannerPath = 'assets/images/categories/' . $bannerName;
        }

        // Generate slug otomatis kalau kosong
        $slug = $request->slug ?: \Str::slug($request->name);

        // Update kategori
        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'code' => $request->code,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'icon' => $iconPath,
            'banner' => $bannerPath,
            'is_active' => $request->is_active ?? 1,
            'sort_order' => $request->sort_order ?? 0,
            'meta_title' => $request->meta_title,
            'meta_keywords' => $request->meta_keywords,
            'meta_description' => $request->meta_description,
        ]);

        return redirect()->route('umkm.category')->with('success', 'Kategori berhasil diperbarui');
    }

    public function categoryDestroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('umkm.category')->with('success', 'Kategori berhasil dihapus');
    }

    public function atributStore(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255|unique:variation_attributes,name',
            'options' => 'required|array|min:1',
            'options.*' => 'required|string|max:255'
        ]);

        // Simpan atribut utama
        $attribute = VariationAttribute::create([
            'idpenginput' => auth()->id(),
            'name' => $request->name,
        ]);

        // Loop simpan opsi variasi
        foreach ($request->options as $value) {
            if (!empty($value)) {
                VariationOption::create([
                    'idpenginput' => auth()->id(),
                    'attribute_id' => $attribute->id,
                    'value'        => $value,
                ]);
            }
        }

        return redirect()
            ->route('umkm.product')
            ->with('success', 'Atribut variasi berhasil ditambahkan');
    }

    public function getByAttribute($attributeId)
    {
        $options = VariationOption::where('attribute_id', $attributeId)->get();

        return response()->json($options);
    }

    public function variasiStore(Request $request)
    {
        // Validasi produk wajib ada
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variations' => 'required|array|min:1',
        ]);

        foreach ($request->variations as $index => $variationData) {
            // Validasi tiap variasi
            $validated = validator($variationData, [
                'attributes' => 'required|array|min:1',
                'options'    => 'required|array|min:1',
                'price'      => 'required|numeric|min:0',
                'sku'        => 'nullable|string|max:100|unique:product_variations,sku',
                'weight'     => 'required|numeric|min:0',
                'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'is_active'  => 'nullable|boolean',
            ])->validate();

            // Upload gambar (jika ada)
            $imagePath = null;
            if (isset($variationData['image']) && $variationData['image'] instanceof \Illuminate\Http\UploadedFile) {
                $filename = time() . '_' . $variationData['image']->getClientOriginalName();
                $variationData['image']->move(public_path('assets/images/variation'), $filename);
                $imagePath = 'assets/images/variation/' . $filename;
            }

            // Simpan data variasi
            $variation = ProductVariation::create([
                'idpenginput' => auth()->id(),
                'product_id'  => $request->product_id,
                'name'        => 'Variasi ' . ($index + 1),
                'sku'         => $validated['sku'] ?? strtoupper(uniqid('SKU-')),
                'price'       => $validated['price'],
                'weight'      => $validated['weight'],
                'image'       => $imagePath,
                'is_active'   => isset($variationData['is_active']) ? 1 : 0,
            ]);

            // Simpan opsi variasi
            if (!empty($validated['options'])) {
                foreach ((array) $validated['options'] as $optionId) {
                    ProductVariationOption::create([
                        'idpenginput'  => auth()->id(),
                        'variation_id' => $variation->id,
                        'option_id'    => $optionId,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Variasi produk berhasil ditambahkan!');
    }

    public function updateVariation(Request $request, $id)
    {
        $variation = ProductVariation::findOrFail($id);

        $validated = $request->validate([
            'product_id'   => 'required|exists:products,id',
            'attributes'   => 'required|array',
            'options'      => 'required|array',
            'price'        => 'required|numeric|min:0',
            'sku'          => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('product_variations', 'sku')->ignore($variation->id),
            ],
            'weight'       => 'required|numeric|min:0',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'    => 'nullable|boolean',
        ], [
            'sku.unique' => 'SKU sudah dipakai oleh variasi lain, silakan gunakan kode lain.',
        ]);

        // handle gambar
        $imagePath = $variation->image;
        if ($request->hasFile('image')) {
            if ($variation->image && file_exists(public_path($variation->image))) {
                @unlink(public_path($variation->image));
            }
            $filename = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('assets/images/variation'), $filename);
            $imagePath = 'assets/images/variation/' . $filename;
        }

        // pastikan SKU tidak kosong
        $sku = !empty($validated['sku']) ? $validated['sku'] : $variation->sku;

        // update data utama
        $variation->update([
            'product_id' => $validated['product_id'],
            'sku'        => $sku,
            'price'      => $validated['price'],
            'weight'     => $validated['weight'],
            'image'      => $imagePath,
            'is_active'  => $request->has('is_active') ? 1 : 0,
        ]);

        // hapus opsi lama
        ProductVariationOption::where('variation_id', $variation->id)->delete();

        // simpan opsi baru
        if (!empty($validated['options'])) {
            foreach ($validated['options'] as $attributeId => $optionIds) {
                foreach ((array) $optionIds as $optionId) {
                    ProductVariationOption::create([
                        'idpenginput' => auth()->id(),
                        'variation_id' => $variation->id,
                        'option_id'    => $optionId,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Variasi produk berhasil diperbarui!');
    }

    public function destroyVariation(string $id)
    {
        $product = ProductVariation::findOrFail($id);
        $product->delete();

        return redirect()->route('umkm.product')->with('success', 'Produk berhasil dihapus');
    }

    public function detailPage(Request $request)
    {
        // 🔹 Tentukan gudang aktif untuk transaksi (type = store)
        $storeWarehouse = Warehouse::where('type', 'store')
            ->where('idpenginput', auth()->id())
            ->first();

        // Jika belum ada gudang toko
        if (!$storeWarehouse) {
            return back()->with('error', 'Gudang toko belum dikonfigurasi untuk akun ini.');
        }

        $query = Product::with([
            // 🔹 Relasi variasi produk + stok di gudang toko
            'variations' => function ($q) use ($storeWarehouse) {
                $q->select('id', 'product_id', 'name', 'sku', 'price', 'is_active')
                    ->with(['warehouseStock' => function ($q2) use ($storeWarehouse) {
                        $q2->where('warehouse_id', $storeWarehouse->id)
                            ->select(
                                'id',
                                'warehouse_id',
                                'product_id',
                                'variation_id',
                                'stock',
                                'reserved',
                                'min_stock',
                                'rack_position',
                                'is_active'
                            );
                    }]);
            },

            // 🔹 Stok dari warehouse (produk utama)
            'warehouseStocks' => function ($q) use ($storeWarehouse) {
                $q->where('warehouse_id', $storeWarehouse->id)
                    ->select(
                        'id',
                        'warehouse_id',
                        'product_id',
                        'variation_id',
                        'stock',
                        'reserved',
                        'min_stock',
                        'rack_position',
                        'is_active'
                    );
            },

            // 🔹 Relasi kategori (opsional)
            'category:id,name',
        ])
        ->select('id', 'name', 'sku', 'price', 'thumbnail', 'is_active', 'category_id')
        ->when($request->q, function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->q . '%')
            ->orWhere('sku', 'like', '%' . $request->q . '%');
        })
        ->orderBy('name');

        $products = $query->paginate(20);

        $productIds = $products->getCollection()->pluck('id');

        // 🔹 Ambil qty terjual realtime
        $soldQtyMap = DB::table('transaction_items as ti')
            ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
            ->whereIn('ti.product_id', $productIds)
            ->where('t.status', 'completed') // samakan dengan POS kamu
            ->groupBy('ti.product_id')
            ->select(
                'ti.product_id',
                DB::raw('SUM(ti.quantity) as total_qty')
            )
            ->pluck('total_qty', 'product_id');

        // 🔹 Ambil revenue realtime
        $salesMap = DB::table('transaction_items as ti')
            ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
            ->whereIn('ti.product_id', $productIds)
            ->where('t.status', 'completed')
            ->groupBy('ti.product_id')
            ->select(
                'ti.product_id',
                DB::raw('SUM(ti.quantity * ti.price) as total_sales')
            )
            ->pluck('total_sales', 'product_id');

        // 🔹 Transformasi data agar siap tampil di view
        $products->getCollection()->transform(function ($p) use ($soldQtyMap, $salesMap) {
            // Ambil stok produk utama dari warehouse toko
            $stokInduk = optional($p->warehouseStocks->first())->stock ?? 0;

            // Hitung stok total variasi dari warehouse toko
            $stokVarian = $p->variations->sum(function ($v) {
                return optional($v->warehouseStock->first())->stock ?? 0;
            });

            // 🔸 Total stok gabungan (induk + variasi)
            $p->stock_total = $stokInduk + $stokVarian;

            // 🔸 Informasi tambahan
            $p->stock_product = $stokInduk;
            $p->variant_count = $p->variations->count();
            $p->total_qty   = $soldQtyMap[$p->id] ?? 0;
            $p->total_sales = $salesMap[$p->id] ?? 0;

            // Info gudang (lokasi, status, dsb)
            $p->rack_position = optional($p->warehouseStocks->first())->rack_position;
            $p->min_stock     = optional($p->warehouseStocks->first())->min_stock ?? 0;
            $p->is_available  = optional($p->warehouseStocks->first())->is_active ?? true;

            return $p;
        });

        return view('umkm.products.detailPage', compact('products'));
    }

    public function detail($id)
    {
        // 🔹 Ambil produk dan variasi (tanpa kolom stock, karena tidak ada di tabel)
        $product = Product::with([
            'variations.options.attribute' => function($q){
                $q->select('id','name');
            },
            'variations' => function($q){
                $q->select('id','product_id','price');
            }
        ])->findOrFail($id);

        // 🔹 Ambil total transfer (stok masuk ke toko) dari warehouse_transfers
        $transferData = DB::table('warehouse_transfers')
            ->select(
                'product_id',
                'variation_id',
                DB::raw('SUM(quantity) as total_transfer')
            )
            ->where('product_id', $id)
            ->where('status', 'received')
            ->groupBy('product_id', 'variation_id')
            ->get();

        $transferMap = [];
        foreach ($transferData as $t) {
            $key = $t->product_id . '-' . ($t->variation_id ?? 0);
            $transferMap[$key] = (int) $t->total_transfer;
        }

        // 🔹 Ambil total penjualan (keluar) dari transaction_items
        $soldData = DB::table('transaction_items as ti')
        ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
        ->select(
            'ti.product_id',
            'ti.variation_id',
            DB::raw('SUM(ti.quantity) as total_sold')
        )
        ->where('ti.product_id', $id)
        ->where('t.status', 'completed')
        ->groupBy('ti.product_id', 'ti.variation_id')
        ->get();

        $soldMap = [];
        foreach ($soldData as $s) {
            $key = $s->product_id . '-' . ($s->variation_id ?? 0);
            $soldMap[$key] = (int) $s->total_sold;
        }

        // 🔹 Total Revenue (UANG) dari transaksi sukses
        $totalRevenue = DB::table('transaction_items as ti')
            ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
            ->where('ti.product_id', $id)
            ->where('t.status', 'completed') // samakan dengan POS kamu
            ->sum(DB::raw('ti.quantity * ti.price'));

        // 🔹 Susun variasi lengkap dengan perhitungan stok
        $variations = $product->variations->map(function($v) use ($transferMap, $soldMap, $product){

            $key = $product->id . '-' . $v->id;

            $total_transfer = $transferMap[$key] ?? 0;
            $total_sold = $soldMap[$key] ?? 0;
            $stock = max(0, $total_transfer - $total_sold);

            // 👉 Ambil nama attribute + value dari variation_options
            $displayName = $v->options->map(function($opt){
                return optional($opt->attribute)->name . ' - ' . $opt->value;
            })->implode(' | ');

            return [
                'id'         => $v->id,
                'product_id' => $v->product_id,
                'name'       => $displayName ?: 'Tanpa Variasi',
                'price'      => $v->price,
                'stock'      => $stock,
                'sold'       => $total_sold,
            ];
        })->values();

        // 🔹 Hitung juga stok produk induk (tanpa variasi)
        $keyInduk = $product->id . '-0';
        $stokInduk = $transferMap[$keyInduk] ?? 0;
        $soldInduk = $soldMap[$keyInduk] ?? 0;
        $stokAkhirInduk = max(0, $stokInduk - $soldInduk);

        // 🔹 Kembalikan respons JSON
        $totalStockVariasi = collect($variations)->sum('stock');
        $totalSoldVariasi  = collect($variations)->sum('sold');

        $totalQtySold = $soldInduk + $totalSoldVariasi;

        return response()->json([
            'id'              => $product->id,
            'name'            => $product->name,
            'price'           => $product->price ?? 0,
            'stock_product'   => $stokAkhirInduk,
            'stock_total'     => $stokAkhirInduk + $totalStockVariasi,
            'total_qty_sold'  => $totalQtySold,
            'total_revenue'   => (int) $totalRevenue,
            'variants_count'  => $variations->count(),
            'variations'      => $variations,
        ]);
    }

    public function managementstock(Request $request)
    {
        $q        = $request->q;
        $category = $request->category;

        // ==== DATA PRODUK ====
        $products = Product::query()
            ->with(['variations:id,product_id,name,stock,price'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($x) use ($q) {
                    $x->where('name', 'like', "%{$q}%")
                    ->orWhere('sku',  'like', "%{$q}%");
                });
            })
            ->when($category, fn($q) => $q->where('category_id', $category));

        $products = $products->paginate(20);

        // ==== RINGKASAN STOK ====
        // total stok induk dari semua produk
        $productStock = Product::sum('stock');

        // total stok semua varian
        $variantStock = ProductVariation::sum('stock');

        // total entitas (produk non-varian + setiap varian) yang stoknya < 10
        $lowStock = Product::with('variations')->get()->reduce(function ($carry, $p) {
            // produk induk tanpa varian
            $low = ($p->variations->isEmpty() && $p->stock < 10) ? 1 : 0;

            // setiap varian yang stoknya < 10
            $low += $p->variations->filter(fn($v) => $v->stock < 10)->count();

            return $carry + $low;
        }, 0);

        $summary = [
            'product_stock' => $productStock,
            'variant_stock' => $variantStock,
            'low_stock'     => $lowStock,
        ];

        // ==== TRANSFORMASI DATA PRODUK ====
        $products->getCollection()->transform(function ($p) {
            $variantStock      = $p->variations->sum('stock');
            $p->stock_variants = $variantStock;
            $p->stock_product  = $p->stock;                  // stok induk
            $p->stock_total    = $p->stock + $variantStock;  // total gabungan

            // total terjual (produk + seluruh varian)
            $ids = $p->variations->pluck('id')->toArray();
            $p->total_sold = TransactionItem::where(function ($q) use ($p, $ids) {
                    $q->where('product_id', $p->id)
                    ->orWhereIn('variation_id', $ids);
                })
                ->whereHas('transaction', fn($t) => $t->where('status', 'completed'))
                ->sum('quantity');

            return $p;
        });

        return view('umkm.products.managementstock', compact('products', 'summary'));
    }

    public function managementupdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Update data produk utama
            $product = Product::findOrFail($id);
            $product->update([
                'name'  => $request->name,
                'sku'   => $request->sku,
                'price' => $request->price,
                'stock' => $request->stock,
            ]);

            // Update variasi
            if ($request->has('variations')) {
                foreach ($request->variations as $var) {
                    ProductVariation::where('id', $var['id'])->update([
                        'name'  => $var['name'],
                        'price' => $var['price'],
                        'stock' => $var['stock'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produk: ' . $e->getMessage()
            ], 500);
        }
    }

    public function insight()
    {
        try {

            $storeWarehouse = Warehouse::where('type', 'store')
                ->where('idpenginput', auth()->id())
                ->first();

            $products = Product::with([
                'variations.options.attribute',
                'variations.warehouseStock' => function($q) use ($storeWarehouse){
                    $q->where('warehouse_id', $storeWarehouse->id);
                },
                'warehouseStocks' => function($q) use ($storeWarehouse){
                    $q->where('warehouse_id', $storeWarehouse->id)
                    ->whereNull('variation_id');
                }
            ])->get();
            
            // === [3️⃣] Ambil Data Penjualan 30 Hari Terakhir ===
            $sales = DB::table('transaction_items')
            ->join('transactions','transactions.id','=','transaction_items.transaction_id')
            ->where('transactions.status','completed')
            ->where('transactions.transaction_type','sale')
            ->where('transactions.transaction_date','>=',Carbon::now()->subDays(30))
            ->select(
                'transaction_items.product_id',
                'transaction_items.variation_id',
                DB::raw('SUM(transaction_items.quantity) as total_sold'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.price) as total_income')
            )
            ->groupBy('transaction_items.product_id','transaction_items.variation_id')
            ->get();

            // === [4️⃣] Gabungkan Produk & Data Penjualan ===
            $merged = collect();

                foreach($products as $p){

                    // PRODUK INDUK
                    $stokInduk = optional($p->warehouseStocks->first())->stock ?? 0;

                    $saleInduk = $sales->first(function($s) use ($p){
                        return $s->product_id == $p->id && is_null($s->variation_id);
                    });

                    $merged->push((object)[
                        'product_id'=>$p->id,
                        'variation_id'=>null,
                        'final_name'=>$p->name,
                        'final_price'=>$p->price,
                        'final_stock'=>$stokInduk,
                        'total_sold'=>$saleInduk->total_sold ?? 0,
                        'total_income'=>$saleInduk->total_income ?? 0
                    ]);

                    // VARIASI
                    foreach($p->variations as $v){

                        $stokVar = optional($v->warehouseStock->first())->stock ?? 0;

                        $saleVar = $sales->first(function($s) use ($p,$v){
                            return $s->product_id == $p->id
                                && $s->variation_id == $v->id;
                        });

                        // 🔥 NAMA VARIASI (SAMA DETAIL MODAL)
                        $displayName = $v->options->map(function($opt){
                            return optional($opt->attribute)->name.' - '.$opt->value;
                        })->implode(' | ');

                        $merged->push((object)[
                            'product_id'=>$p->id,
                            'variation_id'=>$v->id,
                            'final_name'=>$p->name.' - '.$displayName,
                            'final_price'=>$v->price,
                            'final_stock'=>$stokVar,
                            'total_sold'=>$saleVar->total_sold ?? 0,
                            'total_income'=>$saleVar->total_income ?? 0
                        ]);
                    }
                }

            // === [5️⃣] Analisis Produk ===
            $topProducts = $merged->sortByDesc('total_sold')->take(5)->values();
            $lowSales = $merged->filter(fn($p) => $p->total_sold < 3)->take(5)->values();
            $lowStock = $merged->filter(fn($p) => $p->final_stock <= 5)->values();

            // === [6️⃣] Saran Harga Berdasarkan Tren Penjualan ===
            $priceSuggestions = $merged->filter(fn($p) => $p->total_sold > 10)
                ->map(function ($p) {
                    $suggested = round($p->final_price * 1.05, 0);
                    return [
                        'product' => $p->final_name,
                        'old_price' => $p->final_price,
                        'suggested_price' => $suggested,
                        'reason' => 'Permintaan tinggi, pertimbangkan kenaikan 5%',
                    ];
                })
                ->values();

            // === [7️⃣] Prediksi Penjualan Minggu Depan ===
            $predictions = $merged->map(function ($p) {
                $avgSales = $p->total_sold / 4; // rata-rata per minggu
                $predictedSales = round($avgSales * 1.1, 1); // naik 10%
                $predictedStock = max(0, $p->final_stock - $avgSales);

                return [
                    'product' => $p->final_name,
                    'predicted_sales' => $predictedSales,
                    'predicted_stock' => $predictedStock,
                ];
            });

            // === [8️⃣] Insight Otomatis ===
            $insights = collect();

            if ($topProducts->isNotEmpty()) {
                $top = $topProducts->first();
                $insights->push([
                    'type' => '🔥 Produk Terlaris',
                    'detail' => "{$top->final_name} terjual {$top->total_sold} unit dalam 30 hari terakhir.",
                ]);
            }

            foreach ($lowStock as $p) {
                $insights->push([
                    'type' => '⚠️ Stok Menipis',
                    'detail' => "\"{$p->final_name}\" hanya tersisa {$p->final_stock} unit.",
                ]);
            }

            foreach ($priceSuggestions as $s) {
                $insights->push([
                    'type' => '💰 Saran Harga',
                    'detail' => "{$s['product']}: naikkan harga dari Rp{$s['old_price']} → Rp{$s['suggested_price']} ({$s['reason']}).",
                ]);
            }

            foreach ($predictions as $p) {
                $insights->push([
                    'type' => '📈 Prediksi Penjualan',
                    'detail' => "{$p['product']}: diprediksi terjual {$p['predicted_sales']} unit minggu depan (stok sisa ~{$p['predicted_stock']}).",
                ]);
            }

            // === [9️⃣] Kirim ke View ===
            return view('umkm.products.insight', [
                'message' => null,
                'insights' => $insights,
                'topProducts' => $topProducts,
                'lowSales' => $lowSales,
                'lowStock' => $lowStock,
                'predictions' => $predictions,
            ]);

        } catch (\Exception $e) {
            // === [🔟] Error Handling ===
            return view('umkm.products.insight', [
                'message' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage(),
                'insights' => [],
                'topProducts' => [],
                'lowSales' => [],
                'lowStock' => [],
                'predictions' => [],
            ]);
        }
    }

    public function analytic()
    {
        $storeWarehouse = Warehouse::where('type','store')
            ->where('idpenginput',auth()->id())
            ->first();

        $products = Product::with([
            'variations.options.attribute',
            'variations.warehouseStock'=>function($q) use ($storeWarehouse){
                $q->where('warehouse_id',$storeWarehouse->id);
            },
            'warehouseStocks'=>function($q) use ($storeWarehouse){
                $q->where('warehouse_id',$storeWarehouse->id)
                ->whereNull('variation_id');
            }
        ])->get();

        // 🔹 SALES REALTIME
        $sales = DB::table('transaction_items as ti')
            ->join('transactions as t','t.id','=','ti.transaction_id')
            ->where('t.status','completed')
            ->where('t.transaction_type','sale')
            ->select(
                'ti.product_id',
                'ti.variation_id',
                DB::raw('SUM(ti.quantity) as total_sold'),
                DB::raw('SUM(ti.quantity * ti.price) as total_revenue')
            )
            ->groupBy('ti.product_id','ti.variation_id')
            ->get();

        $merged = collect();
        $lowStockProducts = collect();

        foreach($products as $p){

            // PRODUK INDUK
            $stokInduk = optional($p->warehouseStocks->first())->stock ?? 0;

            if($stokInduk <=5){
                $lowStockProducts->push((object)[
                    'full_name'=>$p->name,
                    'stock'=>$stokInduk
                ]);
            }

            $saleInduk = $sales->first(function($s) use ($p){
                return $s->product_id==$p->id && is_null($s->variation_id);
            });

            $merged->push((object)[
                'product_name'=>$p->name,
                'total_sold'=>$saleInduk->total_sold ?? 0,
                'total_revenue'=>$saleInduk->total_revenue ?? 0
            ]);

            // VARIASI
            foreach($p->variations as $v){

                $stokVar = optional($v->warehouseStock->first())->stock ?? 0;

                $displayName = $v->options->map(function($opt){
                    return optional($opt->attribute)->name.' - '.$opt->value;
                })->implode(' | ');

                if($stokVar <=5){
                    $lowStockProducts->push((object)[
                        'full_name'=>$p->name.' - '.$displayName,
                        'stock'=>$stokVar
                    ]);
                }

                $saleVar = $sales->first(function($s) use ($p,$v){
                    return $s->product_id==$p->id && $s->variation_id==$v->id;
                });

                $merged->push((object)[
                    'product_name'=>$p->name.' - '.$displayName,
                    'total_sold'=>$saleVar->total_sold ?? 0,
                    'total_revenue'=>$saleVar->total_revenue ?? 0
                ]);
            }
        }

        $totalProducts = $merged->count();
        $totalSales    = $merged->sum('total_sold');
        $totalRevenue  = $merged->sum('total_revenue');

        $topSelling = $merged->sortByDesc('total_sold')->take(5)->values();
        $lowSelling = $merged->sortBy('total_sold')->take(5)->values();

        $salesTrend = DB::table('transaction_items as ti')
            ->join('transactions as t','t.id','=','ti.transaction_id')
            ->where('t.status','completed')
            ->where('t.transaction_type','sale')
            ->whereBetween('t.transaction_date',[Carbon::now()->subDays(7),Carbon::now()])
            ->selectRaw('DATE(t.transaction_date) as date,
                SUM(ti.quantity * ti.price) as total_amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('umkm.products.analytic',[
            'totalProducts'=>$totalProducts,
            'totalSales'=>$totalSales,
            'totalRevenue'=>$totalRevenue,
            'lowStockCount'=>$lowStockProducts->count(),
            'lowStockProducts'=>$lowStockProducts,
            'topSelling'=>$topSelling,
            'lowSelling'=>$lowSelling,
            'salesTrend'=>$salesTrend
        ]);
    }

    public function warehouse()
    {
        // === 1️⃣ Ambil semua gudang ===
        $warehouses = DB::table('warehouses')->where('type', 'warehouse')->get();
        $warehouses_store = DB::table('warehouses')->where('type', 'store')->get();
        $warehouses_detail = DB::table('warehouses')->get();

        // === 2️⃣ Produk tanpa variasi ===
        $nonVariationProducts = DB::table('products')
            ->leftJoin('warehouse_products', 'warehouse_products.product_id', '=', 'products.id')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'warehouse_products.warehouse_id')
            ->select(
                'products.id',
                'warehouses.name as warehouse_name',
                'warehouses.type as warehouse_type',
                'products.id as product_id',
                'products.name as product_name',
                'products.name as name',
                'products.sku',
                'products.price',
                'warehouse_products.warehouse_id',
                'warehouse_products.rack_position'
            )
            ->whereNull('warehouse_products.variation_id')
            ->where(function ($q) {
                $q->where('warehouses.type', 'warehouse')
                ->orWhereNull('warehouses.type'); // produk yang belum punya gudang tetap tampil
            })
            ->distinct()  
            ->get();

        // === 3️⃣ Produk dengan variasi ===
        $variationProducts = DB::table('product_variations')
            ->join('products', 'products.id', '=', 'product_variations.product_id')
            ->leftJoin('warehouse_products', 'warehouse_products.variation_id', '=', 'product_variations.id')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'warehouse_products.warehouse_id')
            ->select(
                'warehouse_products.id',
                'warehouses.name as warehouse_name',
                'warehouses.type as warehouse_type',
                'products.id as product_id',
                'products.name as product_name',
                'products.name as name',
                'products.sku',
                'products.price',
                'product_variations.price as variation_name',
                'warehouse_products.warehouse_id',
                'product_variations.id as variation_id',
                'warehouse_products.rack_position'
            )
            ->where(function ($q) {
                $q->where('warehouses.type', 'warehouse')
                ->orWhereNull('warehouses.type'); // produk yang belum punya gudang tetap tampil
            })
            ->distinct()
            ->get();
            
        // === 4️⃣ Hitung stok dari warehouse_stock_logs ===
        foreach ($nonVariationProducts as $p) {
            // stok masuk (add)
            $stockIn = DB::table('warehouse_stock_logs')
                ->where('warehouse_id', $p->warehouse_id)
                ->where('product_id', $p->product_id)
                ->whereNull('variation_id')
                ->where('action_type', 'add')
                ->sum('quantity');

            // stok keluar (reduce)
            $stockOut = DB::table('warehouse_stock_logs')
                ->where('warehouse_id', $p->warehouse_id)
                ->where('product_id', $p->product_id)
                ->whereNull('variation_id')
                ->where('action_type', 'reduce')
                ->sum('quantity');

            // transfer masuk (barang diterima)
            $transferIn = DB::table('warehouse_transfers')
                ->where('to_warehouse_id', $p->warehouse_id)
                ->where('product_id', $p->product_id)
                ->whereNull('variation_id')
                ->where('status', 'received')
                ->sum('quantity');

            // transfer keluar (barang dikirim)
            $transferOut = DB::table('warehouse_transfers')
                ->where('from_warehouse_id', $p->warehouse_id)
                ->where('product_id', $p->product_id)
                ->whereNull('variation_id')
                ->whereIn('status', ['sent', 'received'])
                ->sum('quantity');

            // Hitung akhir
            $p->stock_in = $stockIn + $transferIn;
            $p->stock_out = $stockOut + $transferOut;
            $p->stock_current = max(0, ($p->stock_in - $p->stock_out));

            $latestMinStock = DB::table('warehouse_products')
                ->where('warehouse_id', $p->warehouse_id)
                ->where('product_id', $p->product_id)
                ->whereNull('variation_id')
                ->orderByDesc('id')
                ->value('min_stock');

            $p->min_stock = $latestMinStock ?? 0;
        }

        foreach ($variationProducts as $v) {
            $stockIn = DB::table('warehouse_stock_logs')
                ->where('warehouse_id', $v->warehouse_id)
                ->where('product_id', $v->product_id)
                ->where('variation_id', $v->variation_id)
                ->where('action_type', 'add')
                ->sum('quantity');

            $stockOut = DB::table('warehouse_stock_logs')
                ->where('warehouse_id', $v->warehouse_id)
                ->where('product_id', $v->product_id)
                ->where('variation_id', $v->variation_id)
                ->where('action_type', 'reduce')
                ->sum('quantity');

            $transferIn = DB::table('warehouse_transfers')
                ->where('to_warehouse_id', $v->warehouse_id)
                ->where('variation_id', $v->variation_id)
                ->where('status', 'received')
                ->sum('quantity');

            $transferOut = DB::table('warehouse_transfers')
                ->where('from_warehouse_id', $v->warehouse_id)
                ->where('variation_id', $v->variation_id)
                ->whereIn('status', ['sent', 'received'])
                ->sum('quantity');

            $v->stock_in = $stockIn + $transferIn;
            $v->stock_out = $stockOut + $transferOut;
            $v->stock_current = max(0, ($v->stock_in - $v->stock_out));

            $latestMinStock = DB::table('warehouse_products')
                ->where('warehouse_id', $p->warehouse_id)
                ->where('product_id', $p->product_id)
                ->orderByDesc('id')
                ->value('min_stock');

            $p->min_stock = $latestMinStock ?? 0;
        }

        // === 5️⃣ Gabungkan dropdown produk ===
        $products = collect();
        foreach ($nonVariationProducts as $p) {
            $products->push((object)[
                'id' => $p->product_id,
                'name' => $p->product_name,
                'variation_id' => null,
                'type' => 'non-variation'
            ]);
        }
        foreach ($variationProducts as $v) {
            $products->push((object)[
                'id' => $v->product_id,
                'name' => $v->product_name,
                'variation_id' => $v->variation_id,
                'type' => 'variation'
            ]);
        }

        // === 6️⃣ Data transfer antar gudang ===
        $transfers = DB::table('warehouse_transfers')
        ->leftJoin('warehouses as w_from', 'w_from.id', '=', 'warehouse_transfers.from_warehouse_id')
        ->leftJoin('warehouses as w_to', 'w_to.id', '=', 'warehouse_transfers.to_warehouse_id')
        ->leftJoin('products', 'products.id', '=', 'warehouse_transfers.product_id')
        ->leftJoin('product_variations', 'product_variations.id', '=', 'warehouse_transfers.variation_id')
        ->select(
            'products.id as product_id',
            'warehouse_transfers.id',
            'warehouse_transfers.quantity',
            'warehouse_transfers.status',
            'warehouse_transfers.created_at',
            'product_variations.id as variation_id',
            'w_from.id as from_warehouse_id', 
            'w_to.id as to_warehouse_id',
            'w_from.name as from_warehouse_name',
            'w_to.name as to_warehouse_name',
            DB::raw("COALESCE(products.name, 'Produk Tidak Ditemukan') as product_name"),
            DB::raw("COALESCE(product_variations.price, '0') as variation_name")
        )
        ->orderByDesc('warehouse_transfers.created_at')
        ->get();

        foreach ($transfers as $t) {
            if (isset($t->created_at) && is_string($t->created_at)) {
                $t->created_at = Carbon::parse($t->created_at);
            }
        }

        // === 7️⃣ Return ke view ===
        return view('umkm.products.warehouse', compact(
            'warehouses',
            'products',
            'nonVariationProducts',
            'variationProducts',
            'warehouses_store',
            'warehouses_detail',
            'transfers'
        ));
    }
    
    public function getProductVariations($id)
    {
        $variations = DB::table('product_variations')
            ->where('product_id', $id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'variations' => $variations
        ]);
    }

    public function warehouseStore(Request $request)
    {
        try {
            $data = $request->validate([
                'name'        => 'required|string|max:255',
                'type'        => 'required',
                'code'        => 'required|string|max:100|unique:warehouses,code',
                'city'        => 'nullable|string|max:100',
                'address'     => 'nullable|string',
                'pic_name'    => 'nullable|string|max:255',
                'pic_contact' => 'nullable|string|max:255',
                'phone'       => 'nullable|string|max:50',
            ]);

            $data['idpenginput'] = auth()->id();

            $warehouse = Warehouse::create($data);

            // 🔥 WAJIB: Selalu balas JSON agar fetch() tidak error
            return response()->json([
                'success' => true,
                'message' => 'Data gudang berhasil disimpan!',
                'data'    => $warehouse,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $th->getMessage(),
            ]);
        }
    }

    public function updateWarehouse(Request $request, $id)
    {
        $warehouse = Warehouse::find($id);
        if (!$warehouse) {
            return response()->json(['success' => false, 'message' => 'Gudang tidak ditemukan.']);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $id,
        ]);

        $warehouse->update([
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'city' => $request->city,
            'phone' => $request->phone,
            'pic_name' => $request->pic_name,
            'pic_contact' => $request->pic_contact,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gudang berhasil diperbarui.'
        ]);
    }

    /**
     * 🗑️ Hapus gudang
     */
    public function destroyWarehouse($id)
    {
        $warehouse = Warehouse::find($id);
        if (!$warehouse) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.']);
        }

        $warehouse->delete();

        return response()->json(['success' => true, 'message' => 'Gudang berhasil dihapus.']);
    }

    public function updateStock(Request $request)
    {
        try {
            // 🧩 Jika request JSON
            if ($request->isJson()) {
                $data = $request->json()->all();
                $request->merge($data);
            }

            // 🧾 Validasi data
            $validated = $request->validate([
                'warehouse_id'  => 'required|exists:warehouses,id',
                'product_id'    => 'required|integer|exists:products,id',
                'supplier_name' => 'required|string',
                'variation_id'  => 'nullable|integer|exists:product_variations,id',
                'action_type'   => 'required|in:add,reduce',
                'quantity'      => 'required|integer|min:1',
                'min_stock'     => 'nullable|integer|min:0',
                'rack_position' => 'nullable|string|max:100',
            ]);

            $productId   = $request->product_id;
            $variationId = $request->variation_id ?: null;

            // 🔍 Ambil data stok di warehouse_products
            $record = \App\Models\WarehouseProduct::where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $productId)
                ->where(function ($q) use ($variationId) {
                    if ($variationId) $q->where('variation_id', $variationId);
                    else $q->whereNull('variation_id');
                })
                ->first();

            // 🔢 Hitung stok baru
            if ($record) {
                $newStock = $record->stock;

                if ($request->action_type === 'add') {
                    $newStock += $request->quantity;
                } elseif ($request->action_type === 'reduce') {
                    if ($record->stock < $request->quantity) {
                        return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi untuk dikurangi.']);
                    }
                    $newStock = max(0, $record->stock - $request->quantity);
                }

                // ✏️ Update stok utama
                $record->update([
                    'stock'         => $newStock,
                    'min_stock'     => $request->min_stock ?? $record->min_stock,
                    'rack_position' => $request->rack_position ?? $record->rack_position,
                    'supplier_name' => $request->supplier_name ?? $record->supplier_name,
                ]);

            } else {
                // 🆕 Jika belum ada data stok (harus add)
                if ($request->action_type === 'reduce') {
                    return response()->json(['success' => false, 'message' => 'Tidak dapat mengurangi stok yang belum ada.']);
                }

                $record = \App\Models\WarehouseProduct::create([
                    'warehouse_id'  => $request->warehouse_id,
                    'product_id'    => $productId,
                    'variation_id'  => $variationId,
                    'stock'         => $request->quantity,
                    'min_stock'     => $request->min_stock ?? 0,
                    'rack_position' => $request->rack_position,
                    'supplier_name' => $request->supplier_name,
                    'is_active'     => true,
                ]);
            }

            // 🧾 Simpan log perubahan stok
            DB::table('warehouse_stock_logs')->insert([
                'warehouse_id' => $request->warehouse_id,
                'product_id'   => $productId,
                'variation_id' => $variationId,
                'action_type'  => $request->action_type,
                'quantity'     => $request->quantity,
                'note'         => $request->supplier_name ?? '-',
                'user_id'      => auth()->id() ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil diperbarui dan dicatat dalam log.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|integer',
            'to_warehouse_id'   => 'required|integer|different:from_warehouse_id',
            'product_id'        => 'required|integer',
            'quantity'          => 'required|integer|min:1',
            'variation_id'      => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            /* =====================================================
            * 1️⃣ Ambil stok gudang asal
            * ===================================================== */
            $fromStock = DB::table('warehouse_products')
                ->where('warehouse_id', $validated['from_warehouse_id'])
                ->where('product_id', $validated['product_id'])
                ->when(
                    $validated['variation_id'],
                    fn ($q) => $q->where('variation_id', $validated['variation_id']),
                    fn ($q) => $q->whereNull('variation_id')
                )
                ->value('stock');

            $fromStock = $fromStock ?? 0;

            /* =====================================================
            * 2️⃣ Validasi stok cukup
            * ===================================================== */
            if ($validated['quantity'] > $fromStock) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Stok tidak mencukupi. Stok tersedia: {$fromStock}",
                ], 422);
            }

            /* =====================================================
            * 3️⃣ Simpan data transfer
            * ===================================================== */
            $transfer = WarehouseTransfer::create([
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id'   => $validated['to_warehouse_id'],
                'product_id'        => $validated['product_id'],
                'variation_id'      => $validated['variation_id'] ?? null,
                'quantity'          => $validated['quantity'],
                'status'            => 'received',
            ]);

            /* =====================================================
            * 4️⃣ Kurangi stok gudang asal
            * ===================================================== */
            DB::table('warehouse_products')
                ->where('warehouse_id', $validated['from_warehouse_id'])
                ->where('product_id', $validated['product_id'])
                ->when(
                    $validated['variation_id'],
                    fn ($q) => $q->where('variation_id', $validated['variation_id']),
                    fn ($q) => $q->whereNull('variation_id')
                )
                ->decrement('stock', $validated['quantity']);

            /* =====================================================
            * 5️⃣ Tambah stok gudang tujuan
            * ===================================================== */
            $existsToWarehouse = DB::table('warehouse_products')
                ->where('warehouse_id', $validated['to_warehouse_id'])
                ->where('product_id', $validated['product_id'])
                ->when(
                    $validated['variation_id'],
                    fn ($q) => $q->where('variation_id', $validated['variation_id']),
                    fn ($q) => $q->whereNull('variation_id')
                )
                ->exists();

            if ($existsToWarehouse) {
                // UPDATE stok
                DB::table('warehouse_products')
                    ->where('warehouse_id', $validated['to_warehouse_id'])
                    ->where('product_id', $validated['product_id'])
                    ->when(
                        $validated['variation_id'],
                        fn ($q) => $q->where('variation_id', $validated['variation_id']),
                        fn ($q) => $q->whereNull('variation_id')
                    )
                    ->increment('stock', $validated['quantity']);
            } else {
                // INSERT stok baru
                DB::table('warehouse_products')->insert([
                    'warehouse_id' => $validated['to_warehouse_id'],
                    'product_id'   => $validated['product_id'],
                    'variation_id' => $validated['variation_id'] ?? null,
                    'stock'        => $validated['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transfer stok berhasil disimpan',
                'data'    => $transfer
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateTransfer(Request $request, $id)
    {
        $transfer = WarehouseTransfer::findOrFail($id);
        $transfer->update([
            'from_warehouse_id' => $request->from_warehouse_id,
            'to_warehouse_id'   => $request->to_warehouse_id,
            'product_id'        => $request->product_id,
            'variation_id'      => $request->variation_id,
            'quantity'          => $request->quantity,
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteTransfer($id)
    {
        WarehouseTransfer::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

}
