<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'UMKM Dashboard') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-...your-integrity-hash..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://unpkg.com/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/layout/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jQuery & Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('styles')
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="flex h-screen">

        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-gray-800 shadow-lg flex flex-col transition-all duration-300" id="sidebar">
            <div class="h-16 flex items-center justify-center border-b dark:border-gray-700">
                <h1 class="text-xl font-bold text-blue-600">UMKM Panel</h1>
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('dashboard') }}" 
                   class="block px-4 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-700 {{ request()->routeIs('umkm.dashboard') ? 'bg-blue-500 text-white' : 'text-gray-700 dark:text-gray-200' }}">
                    📊 Dashboard
                </a>

                <!-- Produk -->
                <div>
                    <button class="w-full flex justify-between items-center px-4 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 menu-toggle">
                        🛒 Produk
                        <span>▼</span>
                    </button>
                    <div class="submenu ml-4 mt-1 space-y-1">
                        <a href="{{ route('umkm.category') }}" class="block px-4 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">Daftar Kategori</a>
                        <a href="{{ route('umkm.product') }}" class="block px-4 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">Daftar Produk</a>
                    </div>
                </div>

                <a href="#" class="block px-4 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">
                    📑 Laporan
                </a>

                <a href="#" class="block px-4 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">
                    ⚙️ Pengaturan
                </a>
            </nav>

            <!-- Logout -->
            <div class="p-4 border-t dark:border-gray-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg">
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            
            <!-- Header -->
            <header class="h-16 bg-white dark:bg-gray-800 shadow flex items-center justify-between px-6">
                <button id="toggleSidebar" class="text-gray-700 dark:text-gray-200 text-xl">☰</button>
                <h2 class="font-semibold text-lg text-gray-800 dark:text-gray-200">Dashboard UMKM</h2>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700 dark:text-gray-200">{{ auth()->user()->name ?? 'UMKM User' }}</span>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'U') }}" class="w-8 h-8 rounded-full" />
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Yakin hapus?',
                        text: "Data ini akan dihapus permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, hapus!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    <script>
        $(document).ready(function(){
            // Toggle submenu dengan animasi
            $(".menu-toggle").on("click", function(){
                $(this).next(".submenu").slideToggle(200);
            });

            // Toggle sidebar
            $("#toggleSidebar").on("click", function(){
                $("#sidebar").toggleClass("hidden");
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var modal = document.getElementById('categoryModal');
            var form = document.getElementById('formCategory');
            var method = document.getElementById('formMethod');
            var modalTitle = document.getElementById('categoryModalLabel');
            var iconSelect = document.getElementById('iconSelect');
            var iconInput = document.getElementById('icon');
            var iconPreview = document.getElementById('iconPreview');

            let nameInput = document.getElementById('name');
            let slugInput = document.getElementById('slug');

            // 🔥 fungsi slugify
            function generateSlug(text) {
                return text
                    .toLowerCase()
                    .trim()
                    .replace(/\s+/g, '-')     // ganti spasi dengan "-"
                    .replace(/[^\w\-]+/g,'')  // hapus karakter aneh
                    .replace(/\-\-+/g, '-')   // hapus double "-"
                    .replace(/^-+/, '')       // hapus strip depan
                    .replace(/-+$/, '');      // hapus strip belakang
            }

            // Slug otomatis saat user mengetik
            nameInput.addEventListener('input', function () {
                slugInput.value = generateSlug(nameInput.value);
            });

            modal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;

                form.reset();
                form.action = "{{ route('umkm.categories.store') }}";
                method.value = "POST";
                modalTitle.innerText = "Tambah Kategori";

                // default slug kosong
                slugInput.value = "";

                // default icon
                iconSelect.value = "fa-solid fa-store";
                iconPreview.className = "fa-solid fa-store";
                iconInput.value = "fa-solid fa-store";

                // Jika edit
                if (button.getAttribute('data-id')) {
                    form.action = "/categories/" + button.getAttribute('data-id');
                    method.value = "PUT";
                    modalTitle.innerText = "Edit Kategori";

                    nameInput.value = button.getAttribute('data-name');
                    slugInput.value = button.getAttribute('data-slug'); // ambil slug lama
                    document.getElementById('parent_id').value = button.getAttribute('data-parent') ?? "";
                    document.getElementById('description').value = button.getAttribute('data-description');

                    let iconVal = button.getAttribute('data-icon') || "fa-solid fa-store";
                    iconSelect.value = iconVal;
                    iconInput.value = iconVal;
                    iconPreview.className = iconVal;

                    document.getElementById('is_active').value = button.getAttribute('data-status');
                    document.getElementById('meta_title').value = button.getAttribute('data-meta_title');
                    document.getElementById('meta_keywords').value = button.getAttribute('data-meta_keywords');
                    document.getElementById('meta_description').value = button.getAttribute('data-meta_description');
                } else {
                    // Tambah kategori → slug ikut generate otomatis dari nama
                    slugInput.value = generateSlug(nameInput.value);
                }
            });

            // update icon preview
            iconSelect.addEventListener('change', function () {
                let selectedIcon = this.value;
                iconPreview.className = selectedIcon;
                iconInput.value = selectedIcon;
            });
        });

    </script>
</body>
</html>