@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-gray-500 text-sm font-medium">Total Produk</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ \App\Models\Product::where('user_id', auth()->id())->count() }}
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-gray-500 text-sm font-medium">Produk Aktif</h3>
                <p class="mt-2 text-3xl font-bold text-green-600">
                    {{ \App\Models\Product::where('user_id', auth()->id())->where('is_active', true)->count() }}
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-gray-500 text-sm font-medium">Kategori</h3>
                <p class="mt-2 text-3xl font-bold text-blue-600">
                    {{ \App\Models\Category::count() }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Navigasi Cepat</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('products.index') }}" 
                   class="bg-blue-500 hover:bg-blue-600 text-white text-center py-4 px-6 rounded-lg shadow">
                    Kelola Produk
                </a>
                <a href="" 
                   class="bg-green-500 hover:bg-green-600 text-white text-center py-4 px-6 rounded-lg shadow">
                    Kelola Kategori
                </a>
                <a href="#" 
                   class="bg-purple-500 hover:bg-purple-600 text-white text-center py-4 px-6 rounded-lg shadow">
                    Laporan
                </a>
                <a href="#" 
                   class="bg-yellow-500 hover:bg-yellow-600 text-white text-center py-4 px-6 rounded-lg shadow">
                    Pengaturan
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Selamat Datang!</h3>
            <p class="mt-2 text-gray-600 dark:text-gray-300">
                Halo <b>{{ Auth::user()->name }}</b>, selamat datang di dashboard UMKM Anda. 
                Silakan mulai dengan menambahkan produk, kategori, atau melihat laporan penjualan.
            </p>
        </div>
    </div> -->
@endsection