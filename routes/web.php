<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\posController;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/register-umkm', [RegisteredUserController::class, 'createUmkm'])->name('register.umkm');
Route::post('/register-umkm', [RegisteredUserController::class, 'storeUmkm']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', fn() => 'Halaman Admin');
});

Route::middleware(['auth','umkm'])->group(function () {
    // Produk UMKM
    Route::get('/umkm/product', [ProductController::class, 'index'])->name('umkm.product');
    Route::get('/umkm/product/{id}', [ProductController::class, 'index'])->name('umkm.product.edit');
    Route::get('/umkm/productdetail', [ProductController::class,'detailPage'])->name('umkm.product.product_detail');
    Route::resource('/umkm/products', ProductController::class);
    Route::post('/umkm/product', [ProductController::class, 'store'])->name('umkm.product.store');
    Route::put('/umkm/product/{id}', [ProductController::class, 'update'])->name('umkm.product.update');
    Route::delete('/umkm/product/{id}', [ProductController::class, 'destroy'])->name('umkm.product.destroy');
    Route::get('/productdetail/{id}', [ProductController::class, 'detail'])->name('umkm.product.detail');
    Route::get('/managementstock', [ProductController::class, 'managementstock'])->name('umkm.product.management_stock');
    Route::put('/managementstock/{id}', [ProductController::class, 'managementupdate'])->name('umkm.product.management_update');
    Route::get('/insight', [ProductController::class, 'insight'])->name('umkm.product.insight');
    Route::get('/analytic', [ProductController::class, 'analytic'])->name('umkm.product.analytic');
    Route::get('/warehouse', [ProductController::class, 'warehouse'])->name('umkm.product.warehouse');
    Route::post('/warehouse', [ProductController::class, 'warehouseStore'])->name('umkm.warehouse.store');
    Route::post('/warehouse/update-stock', [ProductController::class, 'updateStock'])->name('umkm.warehouse.update-stock');
    Route::post('/umkm/warehouse/update/{id}', [ProductController::class, 'updateWarehouse'])->name('umkm.warehouse.update-warehouse');
    Route::delete('/warehouse/delete/{id}', [ProductController::class, 'destroyWarehouse'])->name('umkm.warehouse.delete');
    Route::get('/umkm/product/{id}/variations', [ProductController::class, 'getProductVariations']);
    Route::post('/umkm/warehouse-transfer', [ProductController::class, 'storeTransfer'])->name('umkm.warehouse.transfer');
    Route::put('/umkm/warehouse-transfer/{id}', [ProductController::class, 'updateTransfer']);
    Route::delete('/umkm/warehouse-transfer-delete/{id}', [ProductController::class, 'deleteTransfer']);

    // Kategori UMKM (semua via ProductController)
    Route::get('/umkm/category', [ProductController::class, 'category'])->name('umkm.category');
    Route::post('/umkm/category', [ProductController::class, 'categoryStore'])->name('umkm.categories.store');
    Route::put('/umkm/category/{id}', [ProductController::class, 'categoryUpdate'])->name('umkm.categories.update');
    Route::delete('/umkm/category/{id}', [ProductController::class, 'categoryDestroy'])->name('umkm.categories.destroy');
    
    Route::post('/umkm/atribut', [ProductController::class, 'atributStore'])->name('umkm.atribut.store');
    Route::get('/umkm/variation-options/{attribute}', [ProductController::class, 'getByAttribute'])->name('umkm.variation.options');
    Route::post('/umkm/variasi', [ProductController::class, 'variasiStore'])->name('umkm.variasi.store');
    Route::delete('/umkm/variasi/{id}', [ProductController::class, 'destroyVariation'])->name('umkm.variation.destroy');
    Route::put('/umkm/variasi/update/{id}', [ProductController::class, 'updateVariation'])->name('umkm.variation.update');

    Route::get('/umkm/pos', [PosController::class, 'index'])->name('umkm.pos.index');
    Route::post('/umkm/pos/add/{id}', [PosController::class, 'addToCart'])->name('umkm.pos.add');
    Route::post('/umkm/pos/update/{id}', [PosController::class, 'updateCart'])->name('umkm.pos.update');
    Route::delete('/umkm/pos/remove/{id}', [PosController::class, 'removeFromCart'])->name('umkm.pos.remove');
    Route::post('/umkm/pos/clear', [PosController::class, 'clearCart'])->name('umkm.pos.clear');
    Route::post('/umkm/pos/checkout', [PosController::class, 'checkout'])->name('umkm.pos.checkout');
    Route::get('/umkm/pos/products', [PosController::class, 'getProducts'])->name('umkm.pos.products');
    Route::post('/umkm/pos/add-variation/{id}', [PosController::class, 'addVariationToCart'])->name('umkm.pos.addVariation');
    Route::post('/umkm/pos/discount/update', [PosController::class, 'updateDiscount'])->name('umkm.pos.discount');
    Route::post('/umkm/pos/stock/add', [PosController::class, 'addStock'])->name('umkm.pos.add');
    Route::post('/api/stock/update', [PosController::class, 'storeTransaction'])->name('stock.transaction.store');
    Route::get('/api/stock/transactions', [PosController::class, 'listTransactions'])->name('stock.transaction.list');
    Route::get('/stock/summary', [PosController::class, 'summary']);
    Route::get('/api/report/products', [PosController::class, 'productReport']);
    Route::get('/umkm/pos/receipt/{id}', [PosController::class, 'receipt'])->name('umkm.pos.receipt');

});

Route::middleware(['auth', 'role:umkm'])->group(function () {
    Route::resource('products', ProductController::class);
});


require __DIR__.'/auth.php';
