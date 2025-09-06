{{-- resources/views/layouts/scripts.blade.php --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById('sidebar');
        const allDropdown = document.querySelectorAll('#sidebar .side-dropdown');
        const toggleSidebar = document.querySelector('nav .toggle-sidebar');
        const allSideDivider = document.querySelectorAll('#sidebar .divider');

        // SIDEBAR DROPDOWN
        allDropdown.forEach(item => {
            const a = item.parentElement.querySelector('a:first-child');
            a.addEventListener('click', function (e) {
            e.preventDefault();

            if (!this.classList.contains('active')) {
                allDropdown.forEach(i => {
                const link = i.parentElement.querySelector('a:first-child');
                link.classList.remove('active');
                i.classList.remove('show');
                });
            }

            this.classList.toggle('active');
            item.classList.toggle('show');
            });
        });

        // SIDEBAR COLLAPSE
        if (sidebar.classList.contains('hide')) {
            allSideDivider.forEach(item => item.textContent = '-');
            allDropdown.forEach(item => {
            const a = item.parentElement.querySelector('a:first-child');
            a.classList.remove('active');
            item.classList.remove('show');
            });
        } else {
            allSideDivider.forEach(item => item.textContent = item.dataset.text);
        }

        toggleSidebar?.addEventListener('click', () => {
            sidebar.classList.toggle('hide');

            if (sidebar.classList.contains('hide')) {
            allSideDivider.forEach(item => item.textContent = '-');
            allDropdown.forEach(item => {
                const a = item.parentElement.querySelector('a:first-child');
                a.classList.remove('active');
                item.classList.remove('show');
            });
            } else {
            allSideDivider.forEach(item => item.textContent = item.dataset.text);
            }
        });

        sidebar?.addEventListener('mouseleave', () => {
            if (sidebar.classList.contains('hide')) {
            allSideDivider.forEach(item => item.textContent = '-');
            allDropdown.forEach(item => {
                const a = item.parentElement.querySelector('a:first-child');
                a.classList.remove('active');
                item.classList.remove('show');
            });
            }
        });

        sidebar?.addEventListener('mouseenter', () => {
            if (sidebar.classList.contains('hide')) {
            allSideDivider.forEach(item => item.textContent = item.dataset.text);
            }
        });

        // PROFILE DROPDOWN
        const profile = document.querySelector('nav .profile');
        const imgProfile = profile?.querySelector('img');
        const dropdownProfile = profile?.querySelector('.profile-link');

        imgProfile?.addEventListener('click', () => {
            dropdownProfile.classList.toggle('show');
        });

        // MENU DROPDOWN (DOT MENU)
        const allMenu = document.querySelectorAll('main .content-data .head .menu');
        allMenu.forEach(item => {
            const icon = item.querySelector('.icon');
            const menuLink = item.querySelector('.menu-link');
            icon?.addEventListener('click', () => {
            menuLink.classList.toggle('show');
            });
        });

        window.addEventListener('click', (e) => {
            if (e.target !== imgProfile && e.target !== dropdownProfile) {
            dropdownProfile?.classList.remove('show');
            }

            allMenu.forEach(item => {
            const icon = item.querySelector('.icon');
            const menuLink = item.querySelector('.menu-link');
            if (e.target !== icon && e.target !== menuLink) {
                menuLink?.classList.remove('show');
            }
            });
        });

        // PROGRESSBAR
        document.querySelectorAll('main .card .progress').forEach(item => {
            item.style.setProperty('--value', item.dataset.value);
        });

        // APEXCHART INIT
        const chartElement = document.querySelector("#chart");
        if (chartElement) {
            const options = {
            series: [{
                name: 'series1',
                data: [31, 40, 28, 51, 42, 109, 100]
            }, {
                name: 'series2',
                data: [11, 32, 45, 32, 34, 52, 41]
            }],
            chart: {
                height: 350,
                type: 'area'
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            xaxis: {
                type: 'datetime',
                categories: [
                "2018-09-19T00:00:00.000Z",
                "2018-09-19T01:30:00.000Z",
                "2018-09-19T02:30:00.000Z",
                "2018-09-19T03:30:00.000Z",
                "2018-09-19T04:30:00.000Z",
                "2018-09-19T05:30:00.000Z",
                "2018-09-19T06:30:00.000Z"
                ]
            },
            tooltip: {
                x: { format: 'dd/MM/yy HH:mm' }
            }
            };

            const chart = new ApexCharts(chartElement, options);
            chart.render();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        // create modal
        const modal = document.getElementById('createModal');
        const openBtn = document.getElementById('openModalBtn');
        const closeBtn = document.getElementById('closeModalBtn');

        openBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });

        closeBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        // Optional: Close modal when clicking outside content
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
            modal.classList.add('hidden');
            }
        });

        const editModal = document.getElementById('editModal');
        const closeEditBtn = document.getElementById('closeeditModalBtn');
        const editForm = document.getElementById('editForm');
        const editRecipients = document.getElementById('editRecipients');
        const editPriceCash = document.getElementById('editPriceCash');
        const editDate = document.getElementById('edittanggal_penerimaan');

        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                const recipients = button.getAttribute('data-recipients');
                const price_cash = button.getAttribute('data-price_cash');
                const petty_cashDate = button.getAttribute('data-tanggal_penerimaan');

                // Set form action
                editForm.action = `/petty-cash/${id}`; // ganti dengan route yang sesuai

                // Isi nilai ke form
                editRecipients.value = recipients;
                editPriceCash.value = price_cash;
                editDate.value = petty_cashDate;

                // Tampilkan modal
                editModal.classList.remove('hidden');
            });
        });

        closeEditBtn.addEventListener('click', () => {
            editModal.classList.add('hidden');
        });

        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) {
                editModal.classList.add('hidden');
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const jumlahInput = document.getElementById('jumlah');
        const hargaInput = document.getElementById('harga_satuan');
        const biayaLainInput = document.getElementById('biaya_lain');
        const nilaiTotalInput = document.getElementById('nilai_total');
        const nilaiTotalDisplay = document.getElementById('nilai_total_display');
        const pettyCashInput = document.getElementById('petty_cash');
        const submitBtn = document.querySelector('.submit-btn');

        const warningTextId = 'cashWarning';

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(angka);
        }

        function showWarning() {
            if (!document.getElementById(warningTextId)) {
                const warning = document.createElement('small');
                warning.id = warningTextId;
                warning.className = 'text-danger';
                warning.textContent = '⚠️ Nilai total melebihi saldo petty cash';
                nilaiTotalDisplay.parentElement.appendChild(warning);
            }
        }

        function hideWarning() {
            const warning = document.getElementById(warningTextId);
            if (warning) warning.remove();
        }

        function hitungTotal() {
            const jumlah = parseFloat(jumlahInput.value) || 0;
            const harga = parseFloat(hargaInput.value) || 0;
            const biayaLain = parseFloat(biayaLainInput.value) || 0;
            const total = (jumlah * harga) + biayaLain;

            const pettyCash = parseFloat(pettyCashInput.value) || 0;

            nilaiTotalInput.value = total;
            nilaiTotalDisplay.value = formatRupiah(total);

            if (total > pettyCash) {
                submitBtn.disabled = true;
                nilaiTotalDisplay.classList.add('is-invalid');
                showWarning();
            } else {
                submitBtn.disabled = false;
                nilaiTotalDisplay.classList.remove('is-invalid');
                hideWarning();
            }
        }

        jumlahInput.addEventListener('input', hitungTotal);
        hargaInput.addEventListener('input', hitungTotal);
        biayaLainInput.addEventListener('input', hitungTotal);

        hitungTotal(); // inisialisasi saat page load
    });

    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = document.getElementById('editModal');
        const editForm = document.getElementById('editForm');

        editButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                let id              = this.dataset.id;
                let name            = this.dataset.name;
                let slug            = this.dataset.slug;
                let code            = this.dataset.code;
                let parent          = this.dataset.parent;
                let description     = this.dataset.description;
                let sort            = this.dataset.sort;
                let status          = this.dataset.status;
                let icon            = this.dataset.icon;
                let banner          = this.dataset.banner;
                let meta_title      = this.dataset.meta_title;
                let meta_keywords   = this.dataset.meta_keywords;
                let meta_description= this.dataset.meta_description;

                // isi ke form
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_slug').value = slug;
                document.getElementById('edit_code').value = code;
                document.getElementById('edit_parent_id').value = parent;
                document.getElementById('edit_description').value = description;
                document.getElementById('edit_sort_order').value = sort;
                document.getElementById('edit_is_active').value = status;

                document.getElementById('edit_meta_title').value = meta_title;
                document.getElementById('edit_meta_keywords').value = meta_keywords;
                document.getElementById('edit_meta_description').value = meta_description;

                // tampilkan icon lama
                if (icon) {
                    document.getElementById('current_icon').src = "{{ asset('') }}" + icon;
                    document.getElementById('current_icon').style.display = 'block';
                } else {
                    document.getElementById('current_icon').style.display = 'none';
                }

                // tampilkan banner lama
                if (banner) {
                    document.getElementById('current_banner').src = "{{ asset('') }}" + banner;
                    document.getElementById('current_banner').style.display = 'block';
                } else {
                    document.getElementById('current_banner').style.display = 'none';
                }

                // update action form (route update)
                editForm.action = "/umkm/category/" + id;

                // tampilkan modal
                editModal.classList.remove('hidden');
            });
        });

        // close modal
        document.getElementById('closeEditModalBtn').onclick = function () {
            editModal.classList.add('hidden');
        }
        document.getElementById('closeEditModalBtn2').onclick = function () {
            editModal.classList.add('hidden');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const pettyCashField = document.getElementById('petty_cash');
        
        // Fungsi untuk menghapus simbol 'Rp' dan titik dari input
        function formatPettyCash() {
            let pettyCashValue = pettyCashField.value;

            // Hapus simbol 'Rp' dan titik pemisah ribuan
            pettyCashValue = pettyCashValue.replace(/[^\d]/g, '');  // Menghapus simbol non-digit (termasuk 'Rp' dan titik)

            // Set kembali nilai yang sudah diformat
            pettyCashField.value = pettyCashValue;
        }

        // Sebelum submit, pastikan nilai yang dikirim adalah angka
        const form = document.querySelector('form');
        form.addEventListener('submit', function (e) {
            formatPettyCash();  // Menghapus simbol 'Rp' dan titik
        });
    });

    closeBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
        modal.querySelector('form').reset();
    });

    document.addEventListener("DOMContentLoaded", function () {
        const priceInput = document.getElementById("price_cash");

        // Pastikan kita mendengarkan event input
        priceInput.addEventListener("input", function (e) {
            let value = e.target.value.replace(/[^,\d]/g, ""); // Hanya ambil angka dan koma

            // Jika value kosong, tidak perlu memformat
            if (!value) {
                e.target.value = "";
                return;
            }

            // Format rupiah dengan titik setiap 3 angka
            e.target.value = formatRupiah(value, "Rp. ");
        });

        // Fungsi untuk memformat angka ke format Rupiah
        function formatRupiah(angka, prefix) {
            let number_string = angka.replace(/[^,\d]/g, "").toString(),
                split = number_string.split(","),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            // Menambahkan titik sebagai pemisah ribuan
            if (ribuan) {
                let separator = sisa ? "." : "";
                rupiah += separator + ribuan.join(".");
            }

            // Menambahkan koma jika ada bagian desimal
            rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
            return prefix + rupiah;
        }

        // Ketika form disubmit, kita hanya kirim angka saja
        priceInput.closest("form").addEventListener("submit", function () {
            const angka = priceInput.value.replace(/[^0-9]/g, "");  // Hapus "Rp." dan titik
            priceInput.value = angka;  // Kirimkan hanya angka saja
        });
    });

</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.querySelectorAll('.swal-confirm').forEach(button => {
        button.addEventListener('click', function () {
            const form = this.closest('form');

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data akan dihapus permanen, apakah anda yakin?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>

<script>
    document.querySelectorAll('.swal-approve').forEach(button => {
        button.addEventListener('click', function () {
            const form = this.closest('form');

            Swal.fire({
                title: 'Approval?',
                text: "Apakah anda yakin menyetujui petty cash ini?.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, setuju!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>

<script>
    function showContent(contentId) {
        // Sembunyikan semua konten
        const sections = document.querySelectorAll('.content-section');
        sections.forEach(section => section.style.display = 'none');

        // Sembunyikan semua link dan hilangkan kelas 'active'
        const links = document.querySelectorAll('.submenu-link');
        links.forEach(link => link.classList.remove('active'));

        // Tampilkan konten yang relevan
        document.getElementById(contentId).style.display = 'block';

        // Tambahkan kelas active pada link yang dipilih
        const activeLink = document.querySelector(`[onclick="showContent('${contentId}')"]`);
        activeLink.classList.add('active');
    }

    // Secara default tampilkan Pengawasan Stok Bahan Baku
    window.onload = function() {
        showContent('stok-bahan-baku');
    };
</script>

<script>
        function showContentStock(contentStockId) {
            console.log(`showContentStock called with: ${contentStockId}`);

            // Sembunyikan semua .card-body
            const sectionStock = document.querySelectorAll('.card-body');
            sectionStock.forEach(section => {
                console.log(`Hiding: ${section.id}`);
                section.style.display = 'none';
            });

            // Hapus kelas active dari semua link submenu
            const linkStock = document.querySelectorAll('.submenustock-link');
            linkStock.forEach(link => {
                console.log(`Removing active from: ${link}`);
                link.classList.remove('active');
            });

            // Tampilkan konten yang dipilih
            const activeSection = document.getElementById(contentStockId);
            if (activeSection) {
                console.log(`Showing: ${activeSection.id}`);
                activeSection.style.display = 'block';
            } else {
                console.log(`Error: No section found with ID: ${contentStockId}`);
            }

            // Tambahkan kelas active pada link yang dipilih
            const activeLinkStock = document.querySelector(`[onclick="showContentStock('${contentStockId}')"]`);
            if (activeLinkStock) {
                console.log(`Adding active to: ${activeLinkStock}`);
                activeLinkStock.classList.add('active');
            } else {
                console.log(`Error: No link found for onclick action: showContentStock('${contentStockId}')`);
            }
        }

        // Default buka tab 'stock-in' saat halaman pertama kali dimuat
        // window.onload = function() {
        //     showContentStock('stok-in');
        // };
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');

        const urlParams = new URLSearchParams(window.location.search);
        let currentPage = parseInt(urlParams.get('page')) || 1;

        prevBtn?.addEventListener('click', () => {
            if (currentPage > 1) {
                window.location.search = `?page=${currentPage - 1}`;
            }
        });

        nextBtn?.addEventListener('click', () => {
            window.location.search = `?page=${currentPage + 1}`;
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalStockOut = document.getElementById('createModalStockOut');
        const openStockOutBtn = document.getElementById('openModalStockOutBtn');
        const closeStockOutBtn = document.getElementById('closeModalStockOutBtn');

        openStockOutBtn.addEventListener('click', () => {
            console.log('Open modal clicked');
            modalStockOut.classList.remove('hidden'); // Modal muncul
        });

        closeStockOutBtn.addEventListener('click', () => {
            console.log('Close modal clicked');
            modalStockOut.classList.add('hidden'); // Modal hilang
        });

        modalStockOut.addEventListener('click', (e) => {
            if (e.target === modalStockOut) {
                console.log('Modal clicked outside');
                modalStockOut.classList.add('hidden'); // Modal hilang jika klik di luar
            }
        });
    });

    const modalRiwayatStockOut = document.getElementById('createRiwayatStockOut');
    const closeRiwayatStockOutBtn = document.getElementById('closeRiwayatStockOutBtn');
    const tableBody = document.getElementById('riwayatTableBody');
    const pageInfo = document.getElementById('riwayatPageInfo');
    const prevBtn = document.getElementById('prevRiwayatBtn');
    const nextBtn = document.getElementById('nextRiwayatBtn');
    const editFormContainer = document.getElementById('editFormContainer');

    let currentMaterialId = null;
    let currentPage = 1;
    let lastPage = 1;

    async function loadRiwayatStockOut(materialId, page = 1) {
        tableBody.innerHTML = '<tr><td colspan="4">Loading...</td></tr>';
        try {
            const response = await fetch(`/riwayat-stock-out/${materialId}?page=${page}`);
            const data = await response.json();

            tableBody.innerHTML = '';
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            data.data.forEach(item => {
                const row = `<tr>
                    <td>${item.datestock_out}</td>
                    <td>${item.name}</td>
                    <td>${item.quantity}</td>
                    <td>
                        <button class="btn-edit-riwayat btn btn-sm btn-warning"
                            data-id="${item.id}"
                            data-date="${item.datestock_out}"
                            data-quantity="${item.quantity}">
                            <i class="bx bx-edit"></i> Edit
                        </button>

                        <form class="delete-form" action="/delete-stock-out/${item.id}" method="POST">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="button" class="delete-btn" data-id="${item.id}">
                                <i class='bx bxs-message-square-x'></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>`;
                tableBody.insertAdjacentHTML('beforeend', row);
            });

            currentPage = data.current_page;
            lastPage = data.last_page;
            pageInfo.textContent = `Page ${currentPage} of ${lastPage}`;
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === lastPage;

        } catch (error) {
            console.error('Gagal memuat data:', error);
            tableBody.innerHTML = '<tr><td colspan="4">Gagal memuat data</td></tr>';
        }
    }

    document.querySelectorAll('.riwayat-stockout-btn').forEach(button => {
        button.addEventListener('click', () => {
            currentMaterialId = button.dataset.materialId;
            modalRiwayatStockOut.classList.remove('hidden');
            loadRiwayatStockOut(currentMaterialId);
        });
    });

    closeRiwayatStockOutBtn.addEventListener('click', () => {
        modalRiwayatStockOut.classList.add('hidden');
    });

    modalRiwayatStockOut.addEventListener('click', (e) => {
        if (e.target === modalRiwayatStockOut) {
            modalRiwayatStockOut.classList.add('hidden');
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) loadRiwayatStockOut(currentMaterialId, currentPage - 1);
    });

    nextBtn.addEventListener('click', () => {
        if (currentPage < lastPage) loadRiwayatStockOut(currentMaterialId, currentPage + 1);
    });

    // Tombol Edit
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-edit-riwayat') || e.target.closest('.btn-edit-riwayat')) {
            const btn = e.target.closest('.btn-edit-riwayat');
            document.getElementById('editId').value = btn.dataset.id;
            document.getElementById('editDate').value = btn.dataset.date;
            document.getElementById('editQuantity').value = btn.dataset.quantity;

            document.getElementById('riwayatTableContainer').classList.add('hidden');
            editFormContainer.classList.remove('hidden');
        }
    });

    // Tombol Batal Edit
    document.getElementById('cancelEditBtn').addEventListener('click', () => {
        editFormContainer.classList.add('hidden');
        document.getElementById('riwayatTableContainer').classList.remove('hidden');
    });

    // Submit Edit
    document.getElementById('editRiwayatForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const id = document.getElementById('editId').value;
        const date = document.getElementById('editDate').value;
        const quantity = document.getElementById('editQuantity').value;

        try {
            const response = await fetch(`/update-stock-out/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ datestock_out: date, quantity })
            });

            if (response.ok) {
                loadRiwayatStockOut(currentMaterialId, currentPage);
                editFormContainer.classList.add('hidden');
                document.getElementById('riwayatTableContainer').classList.remove('hidden');
            } else {
                console.error('Gagal mengupdate data');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });

    // Tombol Delete
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.delete-btn');
            const form = btn.closest('form');

            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });

    // Tombol Delete
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-product-btn') || e.target.closest('.delete-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.delete-product-btn');
            const form = btn.closest('form');

            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalWarehouse = document.getElementById('createModalWarehouse');
        const openWarehouseOutBtn = document.getElementById('openModalWarehouse');
        const closeWarehouseBtn = document.getElementById('closeModalWarehouseBtn');

        openWarehouseOutBtn.addEventListener('click', () => {
            console.log('Open modal clicked');
            modalWarehouse.classList.remove('hidden'); // Modal muncul
        });

        closeWarehouseBtn.addEventListener('click', () => {
            console.log('Close modal clicked');
            modalWarehouse.classList.add('hidden'); // Modal hilang
        });

        modalWarehouse.addEventListener('click', (e) => {
            if (e.target === modalWarehouse) {
                console.log('Modal clicked outside');
                modalWarehouse.classList.add('hidden'); // Modal hilang jika klik di luar
            }
        });
    });

    const editModalWarehouse = document.getElementById('editModalWarehouse');
    const closeWarehouseEditBtn = document.getElementById('closeeditModalWarehouseBtn');
    const editWarehouseForm = document.getElementById('editWarehouseForm');
    const editname = document.getElementById('editname');
    const editcode = document.getElementById('editcode');
    const editlocation = document.getElementById('editlocation');
    const editdescription = document.getElementById('editdescription');
    
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const code = button.getAttribute('data-code');
            const location = button.getAttribute('data-location');
            const description = button.getAttribute('data-description');
            
            // Set form action
            editWarehouseForm.action = `/warehouse/${id}`; // ganti dengan route yang sesuai

            closeWarehouseEditBtn.addEventListener('click', () => {
                console.log('Close modal clicked');
                editModalWarehouse.classList.add('hidden'); // Modal hilang
            });

            // Isi nilai ke form
            editname.value = name;
            editcode.value = code;
            editlocation.value = location;
            editdescription.value = description;
            // Tampilkan modal
            editModalWarehouse.classList.remove('hidden');
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalMaterial = document.getElementById('createModalMaterial');
        const openMaterialOutBtn = document.getElementById('openModalMaterial');
        const closeMaterialBtn = document.getElementById('closeModalMaterialBtn');

        openMaterialOutBtn.addEventListener('click', () => {
            console.log('Open modal clicked');
            modalMaterial.classList.remove('hidden'); // Modal muncul
        });

        closeMaterialBtn.addEventListener('click', () => {
            console.log('Close modal clicked');
            modalMaterial.classList.add('hidden'); // Modal hilang
        });

        modalMaterial.addEventListener('click', (e) => {
            if (e.target === modalMaterial) {
                console.log('Modal clicked outside');
                modalMaterial.classList.add('hidden'); // Modal hilang jika klik di luar
            }
        });
    });

    const editModalMaterial = document.getElementById('editModalMaterial');
    const closeMaterialEditBtn = document.getElementById('closeeditModalMaterialBtn');
    const editMaterialForm = document.getElementById('editMaterialForm');
    const editMaterial_name = document.getElementById('editname');
    const editUnit = document.getElementById('editunit');
    const editMin_stock = document.getElementById('editmin_stock');
    const editCode_material = document.getElementById('editcode');
    const editDescription_material = document.getElementById('editdescription');
    
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const unit = button.getAttribute('data-unit');
            const code = button.getAttribute('data-code');
            const min_stock = button.getAttribute('data-min_stock');
            const description = button.getAttribute('data-description');
            
            // Set form action
            editMaterialForm.action = `/raw-materials/${id}`; // ganti dengan route yang sesuai

            // Isi nilai ke form
            editMaterial_name.value = name;
            editunit.value = unit;
            editMin_stock.value = min_stock;
            editCode_material.value = code;
            editDescription_material.value = description;

            closeMaterialEditBtn.addEventListener('click', () => {
                console.log('Close modal clicked');
                editModalMaterial.classList.add('hidden'); // Modal hilang
            });

            // Tampilkan modal
            editModalMaterial.classList.remove('hidden');
        });
    });
</script>
<script>
    document.getElementById('name').addEventListener('input', function() {
        let slug = this.value.toLowerCase()
            .replace(/ /g,'-')
            .replace(/[^\w-]+/g,'');
        document.getElementById('slug').value = slug;
    });
    document.getElementById('edit_name').addEventListener('input', function() {
        let slug = this.value.toLowerCase()
            .replace(/ /g,'-')
            .replace(/[^\w-]+/g,'');
        document.getElementById('edit_slug').value = slug;
    });
</script>
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        showConfirmButton: false,
        timer: 2000
    })
</script>
@endif
@if ($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        html: `{!! implode('<br>', $errors->all()) !!}`,
    })
</script>
@endif

<script>
    document.querySelectorAll('.tab-link').forEach(button => {
        button.addEventListener('click', () => {
            // Hilangkan active di semua tombol
            document.querySelectorAll('.tab-link').forEach(btn => btn.classList.remove('active'));
            // Sembunyikan semua konten
            document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('active'));
            
            // Tambahkan active ke tombol yang diklik
            button.classList.add('active');
            // Tampilkan konten sesuai target
            document.getElementById(button.dataset.target).classList.add('active');
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // create modal
        const modal = document.getElementById('createModalProduct');
        const openBtn = document.getElementById('openModalProductBtn');
        const closeBtn = document.getElementById('closeModalProductBtn');

        openBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });

        closeBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        // Optional: Close modal when clicking outside content
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
            modal.classList.add('hidden');
            }
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const modal = document.getElementById("createVariationModal");
        const openBtn = document.getElementById("openVariationModal");
        const closeBtns = [document.getElementById("closeModalVariationBtn"), document.getElementById("closeModalVariationBtn2")];
        const addRowBtn = document.getElementById("addVariationRow");
        const container = document.getElementById("variationAttributes");

        // Buka modal
        openBtn.addEventListener("click", () => {
            modal.classList.remove("hidden");
        });

        // Tutup modal
        closeBtns.forEach(btn => {
            if (btn) {
                btn.addEventListener("click", () => {
                    modal.classList.add("hidden");
                });
            }
        });

        // Tambah row variasi
        addRowBtn.addEventListener("click", () => {
            const newRow = document.createElement("div");
            newRow.classList.add("variation-row");
            newRow.innerHTML = `
                <select name="attributes[]" class="attribute-select" required>
                    <option value="">-- Pilih Atribut --</option>
                    ${window.variationAttributes.map(attr => `<option value="${attr.id}">${attr.name}</option>`).join("")}
                </select>

                <select name="options[]" class="option-select" required>
                    <option value="">-- Pilih Opsi --</option>
                </select>

                <button type="button" class="btn-remove-row">X</button>
            `;
            container.appendChild(newRow);

            // Event hapus row
            newRow.querySelector(".btn-remove-row").addEventListener("click", () => {
                newRow.remove();
            });
        });

        // Event saat atribut dipilih
        document.addEventListener("change", function (e) {
            if (e.target.classList.contains("attribute-select")) {
                let attributeId = e.target.value;
                let optionSelect = e.target.closest(".variation-row").querySelector(".option-select");

                if (attributeId) {
                    fetch(`/umkm/variation-options/${attributeId}`)
                        .then(res => res.json())
                        .then(data => {
                            optionSelect.innerHTML = '<option value="">-- Pilih Opsi --</option>';
                            data.forEach(opt => {
                                optionSelect.innerHTML += `<option value="${opt.id}">${opt.value}</option>`;
                            });
                        });
                } else {
                    optionSelect.innerHTML = '<option value="">-- Pilih Opsi --</option>';
                }
            }
        });

        // Hapus row default
        document.querySelectorAll(".btn-remove-row").forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.target.closest(".variation-row").remove();
            });
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const promoSelect = document.getElementById("is_promo");
        const promoFields = document.getElementById("promo_fields");

        function togglePromoFields() {
            if (promoSelect.value === "1") {
                promoFields.classList.remove("hidden");
            } else {
                promoFields.classList.add("hidden");
                // Kosongkan nilai saat tidak promo
                document.getElementById("promo_price").value = "";
                document.getElementById("promo_start").value = "";
                document.getElementById("promo_end").value = "";
            }
        }

        promoSelect.addEventListener("change", togglePromoFields);

        // jalankan saat pertama kali load
        togglePromoFields();
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.edit-btn');
    const editModal   = document.getElementById('editModalProduct');
    const closeModalProduct  = document.getElementById('closeEditModal');
    const editForm    = document.getElementById('editFormProduct');
    const overlay     = document.getElementById('overlay'); // pastikan id overlay ada di HTML
    const editPromoSelect = document.getElementById('edit_is_promo');
    const editPromoFields = document.getElementById('edit_promo_fields');

    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            // ambil data dari atribut data-*
            const {
                id, name, sku, barcode, category_id, price, discount_price, cost_price, 
                stock, min_stock, unit, product_type, expiry_date, batch_number, description, 
                is_active, is_featured, is_promo, promo_price, promo_start, promo_end, 
                meta_title, meta_keywords, meta_description, thumbnail, images: rawImages
            } = this.dataset;

            let images = [];
            try {
                if (rawImages && rawImages.trim().startsWith("[")) {
                    images = JSON.parse(rawImages);
                } else if (rawImages && rawImages.includes(",")) {
                    images = rawImages.split(",").map(img => img.trim());
                } else if (rawImages && rawImages.trim() !== "") {
                    images = [rawImages.trim()];
                }
            } catch (e) {
                console.error("Invalid images dataset:", rawImages);
            }

            // isi form edit
            document.getElementById('edit_id').value               = id;
            document.getElementById('edit_name').value             = name;
            document.getElementById('edit_sku').value              = sku;
            document.getElementById('edit_barcode').value          = barcode;
            document.getElementById('edit_category_id').value      = category_id;
            document.getElementById('edit_price').value            = price;
            document.getElementById('edit_discount_price').value   = discount_price;
            document.getElementById('edit_cost_price').value       = cost_price;
            document.getElementById('edit_stock').value            = stock;
            document.getElementById('edit_min_stock').value        = min_stock;
            document.getElementById('edit_unit').value             = unit;
            document.getElementById('edit_product_type').value     = product_type;
            document.getElementById('edit_expiry_date').value      = expiry_date;
            document.getElementById('edit_batch_number').value     = batch_number;
            document.getElementById('edit_description').value      = description;
            document.getElementById('edit_is_active').value        = is_active;
            document.getElementById('edit_is_featured').value      = is_featured;
            document.getElementById('edit_is_promo').value         = is_promo;
            document.getElementById('edit_promo_price').value      = promo_price;
            document.getElementById('edit_promo_start').value      = promo_start;
            document.getElementById('edit_promo_end').value        = promo_end;
            document.getElementById('edit_meta_title').value       = meta_title;
            document.getElementById('edit_meta_keywords').value    = meta_keywords;
            document.getElementById('edit_meta_description').value = meta_description;

            // tampilkan thumbnail lama
            const thumb = document.getElementById('current_thumbnail');
            if (thumbnail) {
                thumb.src = "{{ asset('') }}" + thumbnail;
                thumb.style.display = 'block';
            } else {
                thumb.style.display = 'none';
            }

            // tampilkan gambar tambahan lama (asumsikan single image)
            const img = document.getElementById('current_images');
            if (images.length > 0) {
                img.innerHTML = `<img src="{{ asset('') }}${images[0]}" style="max-width:100px; margin-top:5px;">`;
                img.style.display = 'block';
            } else {
                img.innerHTML = '';
                img.style.display = 'none';
            }

            // tampilkan/hide promo fields saat modal dibuka
            function togglePromoFields() {
                if (editPromoSelect.value == "1") {
                    editPromoFields.classList.remove('hidden');
                } else {
                    editPromoFields.classList.add('hidden');
                    document.getElementById('edit_promo_price').value = "";
                    document.getElementById('edit_promo_start').value = "";
                    document.getElementById('edit_promo_end').value = "";
                }
            }
            togglePromoFields();

            editFormProduct.action = "/umkm/product/" + id;
            // tampilkan modal
            editModal.classList.remove('hidden');
            if (overlay) overlay.classList.remove('hidden');
        });
    });

    // jalankan toggle promo jika user ubah select
    if (editPromoSelect) {
        editPromoSelect.addEventListener('change', function () {
            if (editPromoSelect.value == "1") {
                editPromoFields.classList.remove('hidden');
            } else {
                editPromoFields.classList.add('hidden');
                document.getElementById('edit_promo_price').value = "";
                document.getElementById('edit_promo_start').value = "";
                document.getElementById('edit_promo_end').value = "";
            }
        });
    }

    // tombol close (semua .close-btn)
    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.getAttribute('data-close');
            const modal  = document.querySelector(target);
            if (modal) modal.classList.add('hidden');
            if (overlay) overlay.classList.add('hidden');
        });
    });

    // klik overlay untuk close
    if (overlay) {
        overlay.addEventListener('click', function () {
            editModal.classList.add('hidden');
            overlay.classList.add('hidden');
        });
    }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const optionWrapper = document.getElementById("optionWrapper");
    const addOptionRowBtn = document.getElementById("addOptionRow");

    // Tambah opsi baru
    addOptionRowBtn.addEventListener("click", function () {
        const newRow = document.createElement("div");
        newRow.classList.add("variation-row");

        newRow.innerHTML = `
            <input type="text" name="options[]" placeholder="Masukkan opsi (contoh: Merah)" required>
            <button type="button" class="btn-remove-row">X</button>
        `;

        optionWrapper.appendChild(newRow);

        // Tambah event listener untuk tombol hapus
        newRow.querySelector(".btn-remove-row").addEventListener("click", function () {
            newRow.remove();
        });
    });

    // Hapus row default pertama kalau ditekan tombol X
    document.querySelectorAll(".btn-remove-row").forEach(function (btn) {
        btn.addEventListener("click", function () {
            btn.parentElement.remove();
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.variation-edit-btn');
    const editModal   = document.getElementById('variationEditModal');
    const editForm    = document.getElementById('variationEditForm');
    const container   = document.getElementById("editVariationAttributes");
    const addRowBtn   = document.getElementById("addEditVariationRow");

    // fallback kalau tidak didefinisikan
    const ATTRS = window.variationAttributes || [];
    const OPTS_MAP = window.variationOptions || {}; // object: { "1": [{id, value}, ...], "2": [...] }

    function getOptionsArray(attrId) {
        if (!attrId) return [];
        // normalize key ke string (JSON object keys biasanya string)
        const key = String(attrId);
        return OPTS_MAP[key] || []; // if undefined => []
    }

    // Fungsi untuk render row atribut + opsi
    function renderRow(selectedAttribute = "", selectedOption = "") {
        const newRow = document.createElement("div");
        newRow.classList.add("variation-row", "mb-2", "flex", "gap-2", "items-center");

        newRow.innerHTML = `
            <select name="attributes[]" class="attribute-select form-select" required>
                <option value="">-- Pilih Atribut --</option>
                ${ATTRS.map(attr => 
                    `<option value="${attr.id}" ${attr.id == selectedAttribute ? "selected" : ""}>${attr.name}</option>`
                ).join("")}
            </select>

            <select name="options[]" class="option-select form-select" required>
                <option value="">-- Pilih Opsi --</option>
            </select>

            <button type="button" class="btn-remove-row">X</button>
        `;

        container.appendChild(newRow);

        const attributeSelect = newRow.querySelector(".attribute-select");
        const optionSelect    = newRow.querySelector(".option-select");

        function populateOptions(attrId) {
            optionSelect.innerHTML = `<option value="">-- Pilih Opsi --</option>`;
            const arr = getOptionsArray(attrId);
            if (arr.length) {
                arr.forEach(opt => {
                    const label = opt.value ?? opt.name ?? opt.label ?? opt.id;
                    const isSelected = (String(opt.id) === String(selectedOption)) ? "selected" : "";
                    optionSelect.innerHTML += `<option value="${opt.id}" ${isSelected}>${label}</option>`;
                });
            }
        }

        // pertama kali render (pakai selectedAttribute)
        populateOptions(selectedAttribute);

        // jika user ganti attribute
        attributeSelect.addEventListener("change", function () {
            // hilangkan selectedOption saat user ubah attribute
            populateOptions(this.value);
        });

        // hapus row
        newRow.querySelector(".btn-remove-row").addEventListener("click", () => {
            newRow.remove();
        });
    }

    // tombol tambah row (cek keberadaan)
    if (addRowBtn) {
        addRowBtn.addEventListener("click", () => {
            renderRow();
        });
    }

    // buka modal edit
    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const id      = this.dataset.id;
            const product = this.dataset.product;
            const price   = this.dataset.price;
            const stock   = this.dataset.stock;
            const sku     = this.dataset.sku;     // ✅ ambil sku dari data attribute
            const weight  = this.dataset.weight;  // ✅ ambil weight dari data attribute

            // parse data-options (safe)
            let options = [];
            try {
                options = JSON.parse(this.dataset.options || "[]");
                if (!Array.isArray(options)) options = [];
            } catch (e) {
                console.error("Gagal parse options:", e, this.dataset.options);
                options = [];
            }

            // isi form
            document.getElementById('variation_id').value      = id;
            document.getElementById('variation_product').value = product ?? '';
            document.getElementById('variation_price').value   = price ?? '';
            document.getElementById('variation_stock').value   = stock ?? '';
            document.getElementById('variation_sku').value     = sku ?? '';   // ✅ set SKU
            document.getElementById('variation_weight').value  = weight ?? ''; // ✅ set weight

            // reset container
            if (container) container.innerHTML = "";

            // render opsi lama atau minimal 1 row
            if (options.length > 0) {
                options.forEach(opt => {
                    renderRow(opt.attribute_id ?? opt.attribute ?? "", opt.option_id ?? opt.option ?? "");
                });
            } else {
                renderRow();
            }

            // update action (sesuaikan route)
            editForm.action = "/umkm/variasi/update/" + id;

            // tampilkan modal
            if (editModal) editModal.classList.remove('hidden');
        });
    });

    // tutup modal (cek keberadaan tombol)
    const closeBtn1 = document.getElementById('closeVariationEditModalBtn');
    const closeBtn2 = document.getElementById('closeVariationEditModalBtn2');
    if (closeBtn1) closeBtn1.onclick = () => editModal.classList.add('hidden');
    if (closeBtn2) closeBtn2.onclick = () => editModal.classList.add('hidden');
});

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-variasi-delete') || e.target.closest('.delete-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.btn-variasi-delete');
            const form = btn.closest('form');

            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });
</script>