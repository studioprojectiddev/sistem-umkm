<section id="sidebar">
    <a href="#" class="brand"> 
        <div class="brand">
            <img src="{{ asset('assets/images/icon_imanuel2.png') }}" 
                alt="Logo" 
                style="width: 100px; height: auto;">
        </div>
    </a>

    <ul class="side-menu">
        <!-- dashboard -->
        <li>
            <a href="{{ route('dashboard') }}" class="active">
                <i class='bx bxs-dashboard icon'></i> Dashboard
            </a>
        </li>

        <li class="divider" data-text="UMKM">UMKM</li>

        <!-- kasir -->
        <li>
            <a href="{{ route('umkm.pos.index') }}">
                <i class='bx  bx-receipt icon'></i>  Kasir (POS)
            </a>
        </li>

        <li>
            <a href="#"><i class='bx  bx-credit-card-front icon'  ></i>  Transaksi <i class='bx bx-chevron-right icon-right'></i></a>
            <ul class="side-dropdown">
                <li><a href="">Pemasukan/Pengeluaran</a></li>
                <li><a href="" >Upload nota (OCR)</a></li>
                <li><a href="" >Bank/e-wallet</a></li>
                <li><a href="" >Histori transaksi</a></li>
            </ul>
        </li>

        <li>
            <a href="#"><i class='bx  bx-buildings icon'  ></i>  Produk & Stok <i class='bx bx-chevron-right icon-right'></i></a>
            <ul class="side-dropdown">
                <li><a href="{{ route('umkm.category') }}" >Kategori Produk</a></li>
                <li><a href="{{ route('umkm.product') }}">Daftar & Variasi Produk</a></li>
                <li><a href="{{ route('umkm.product.warehouse') }}" >Multi-Gudang</a></li>
                <li><a href="{{ route('umkm.product.product_detail') }}">Detail Produk</a></li>
                <li><a href="{{ route('umkm.product.management_stock') }}" >Manajemen Stok</a></li>
                <li><a href="{{ route('umkm.product.insight') }}" >AI Insight</a></li>
                <li><a href="{{ route('umkm.product.analytic') }}" >Analisis Produk</a></li>
            </ul>
        </li>

        <li>
            <a href="#"><i class='bx  bx-user icon'  ></i>  Pelanggan (CRM Mini) <i class='bx bx-chevron-right icon-right'></i></a>
            <ul class="side-dropdown">
                <li><a href="">Data pelanggan</a></li>
                <li><a href="" >Segmentasi</a></li>
                <li><a href="" >Broadcast promo/WA</a></li>
            </ul>
        </li>

        <li>
            <a href="#"><i class='bx  bx-book-alt icon' ></i>   Laporan Keuangan <i class='bx bx-chevron-right icon-right'></i></a>
            <ul class="side-dropdown">
                <li><a href="">Laporan Periodik</a></li>
                <li><a href="" >Laba rugi</a></li>
                <li><a href="" >Cashflow & neraca</a></li>
            </ul>
        </li>

        <li>
            <a href="#"><i class='bx  bx-trending-down icon'  ></i> Insight & Rekomendasi <i class='bx bx-chevron-right icon-right'></i></a>
            <ul class="side-dropdown">
                <li><a href="">Penjualan & pelanggan</a></li>
                <li><a href="" >Prediksi penjualan</a></li>
                <li><a href="" >Efisiensi biaya</a></li>
                <li><a href="" >Rekomendasi promosi</a></li>
            </ul>
        </li>

        <li>
            <a href="#"><i class='bx  bx-store icon'  ></i>  Marketplace & Integrasi <i class='bx bx-chevron-right icon-right'></i></a>
            <ul class="side-dropdown">
                <li><a href="">Shopee sync</a></li>
                <li><a href="" >Tokopedia sync</a></li>
                <li><a href="" >Bank & e-wallet</a></li>
                <li><a href="" >Notifikasi real-time</a></li>
            </ul>
        </li>

        <li>
            <a href="#"><i class='bx  bx-cog icon'  ></i> Pengaturan <i class='bx bx-chevron-right icon-right'></i></a>
            <ul class="side-dropdown">
                <li><a href="">Profil UMKM</a></li>
                <li><a href="" >Kategori Transaksi</a></li>
                <li><a href="" >Hak akses user/staf</a></li>
                <li><a href="" >API & integrasi</a></li>
            </ul>
        </li>

    </ul>

    <div class="ads">
        <div class="wrapper">
            <a href="#" class="btn-upgrade">Chat Me</a>
            <p>Have a problem? <span>DON'T WORRY</span> please contact us via <span>The Button Above</span></p>
        </div>
    </div>
</section>