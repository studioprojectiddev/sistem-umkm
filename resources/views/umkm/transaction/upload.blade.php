@extends('layouts.app')

@section('title', 'Upload Nota (OCR)')

@section('content')
<h1 class="title">Upload Nota (OCR)</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.transaction.upload') }}" class="active">Upload Nota (OCR)</a></li>
</ul>

<style>
    .card-box{
    background:#fff;
    padding:20px;
    border-radius:16px;
    box-shadow:0 6px 20px rgba(0,0,0,0.05);
    margin-top:20px;
}

.form-group{
    display:flex;
    flex-direction:column;
    gap:6px;
}

.form-control{
    padding:10px 12px;
    border-radius:10px;
    border:1px solid #ddd;
}

.btn-primary{
    background:#4e73df;
    color:#fff;
    border:none;
    padding:10px 18px;
    border-radius:10px;
    cursor:pointer;
}
.upload-area {
    border:2px dashed #d1d5db;
    border-radius:12px;
    padding:30px;
    text-align:center;
    cursor:pointer;
    transition:0.3s;
    background:#fafafa;
}

.upload-area:hover {
    border-color:#4e73df;
    background:#f4f7ff;
}

.upload-icon {
    font-size:30px;
    margin-bottom:10px;
}

.upload-content p {
    margin:0;
    font-size:14px;
}

.upload-content span {
    font-size:12px;
    color:#888;
}

.preview-wrapper {
    margin-top:15px;
}

#previewImage {
    max-width:250px;
    border-radius:12px;
    display:none;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

/* OCR TEXT */
.ocr-box {
    margin-top:10px;
}

.ocr-box textarea {
    width:100%;
    border-radius:10px;
    border:1px solid #e5e7eb;
    padding:12px;
    font-size:13px;
    line-height:1.6;
    background:#f9fafb;
    resize:none;
    height:120px;
    overflow:auto;
}
</style>

<div class="card-box">

    <h4>📸 Upload Nota</h4>

    <div class="upload-area" id="uploadArea">
        <input type="file" id="imageInput" accept="image/*" hidden>

        <div class="upload-content">
            <div class="upload-icon">📁</div>
            <p><strong>Klik atau drag gambar ke sini</strong></p>
            <span>Format: JPG, PNG (Max 2MB)</span>
        </div>
    </div>

    <div class="preview-wrapper">
        <img id="previewImage">
    </div>

    <div style="margin-top:15px;">
        <button id="btnProcess" class="btn-primary">
            🔍 Proses OCR
        </button>
    </div>

</div>

{{-- ================= HASIL OCR ================= --}}
<div class="card-box" id="resultBox" style="display:none;">

    <h4>📊 Hasil OCR</h4>

    <div class="form-grid">

        <div class="form-group">
            <label>Tanggal</label>
            <input type="date" id="resultDate" class="form-control">
        </div>

        <div class="form-group">
            <label>Total</label>
            <input type="number" id="resultTotal" class="form-control">
        </div>

    </div>

    <div style="margin-top:15px;">
        <label>Text OCR (debug)</label>
        <div class="ocr-box">
            <textarea id="resultText" readonly></textarea>
        </div>
    </div>

    <div style="margin-top:15px; text-align:right;">
        <button id="btnSave" class="btn-primary">
            💾 Simpan ke Transaksi
        </button>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function(){

    let selectedFile = null;

    // ================= HELPER =================
    function get(id){
        return document.getElementById(id);
    }

    // ================= ELEMENT =================
    const uploadArea = get('uploadArea');
    const inputFile = get('imageInput');
    const previewImage = get('previewImage');
    const btnProcess = get('btnProcess');
    const btnSave = get('btnSave');

    // ================= UPLOAD CLICK =================
    if(uploadArea && inputFile){
        uploadArea.addEventListener('click', () => inputFile.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#4e73df';
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = '#d1d5db';
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();

            const file = e.dataTransfer.files[0];
            inputFile.files = e.dataTransfer.files;

            handlePreview(file);
        });
    }

    // ================= INPUT CHANGE =================
    if(inputFile){
        inputFile.addEventListener('change', function(e){
            const file = e.target.files[0];
            handlePreview(file);
        });
    }

    // ================= PREVIEW =================
    function handlePreview(file){
        if(!file) return;

        selectedFile = file;

        const reader = new FileReader();
        reader.onload = function(ev){
            if(previewImage){
                previewImage.src = ev.target.result;
                previewImage.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }

    // ================= PROCESS OCR =================
    if(btnProcess){
        btnProcess.addEventListener('click', function(){

            if(!selectedFile){
                Swal.fire('Error','Pilih gambar dulu','error');
                return;
            }

            let formData = new FormData();
            formData.append('image', selectedFile);

            Swal.fire({
                title:'Memproses OCR...',
                allowOutsideClick:false,
                didOpen:()=>Swal.showLoading()
            });

            fetch("{{ route('umkm.transaction.ocr') }}", {
                method:'POST',
                headers:{
                    'X-CSRF-TOKEN':'{{ csrf_token() }}'
                },
                body:formData
            })
            .then(res=>res.json())
            .then(res=>{

                if(res.status === 'success'){

                    const resultBox = get('resultBox');
                    const resultDate = get('resultDate');
                    const resultTotal = get('resultTotal');
                    const resultText = get('resultText');

                    if(resultBox) resultBox.style.display = 'block';
                    if(resultDate) resultDate.value = res.date ?? '';
                    if(resultTotal) resultTotal.value = res.total ?? 0;
                    if(resultText) resultText.value = res.text ?? '';

                    Swal.fire('Berhasil','OCR selesai','success');

                }else{
                    Swal.fire('Error',res.message,'error');
                }

            })
            .catch(()=>{
                Swal.fire('Error','Gagal proses OCR','error');
            });

        });
    }

    // ================= SAVE OCR =================
    if(btnSave){
        btnSave.addEventListener('click', function(){

            let totalEl = get('resultTotal');
            let dateEl = get('resultDate');

            if(!totalEl || !dateEl){
                console.log('Element tidak ditemukan ❌');
                return;
            }

            let total = totalEl.value;
            let date = dateEl.value;

            if(!total){
                Swal.fire('Error','Total kosong','error');
                return;
            }

            if(!date){
                Swal.fire('Error','Tanggal kosong','error');
                return;
            }

            Swal.fire({
                title:'Menyimpan...',
                allowOutsideClick:false,
                didOpen:()=>Swal.showLoading()
            });

            let formData = new FormData();
            formData.append('amount', total);
            formData.append('transaction_date', date);
            formData.append('account_id', 1); // nanti bisa dinamis

            fetch("{{ route('umkm.transaction.store_ocr') }}", {
                method:'POST',
                headers:{
                    'X-CSRF-TOKEN':'{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success'){
                    Swal.fire('Berhasil','Transaksi disimpan','success')
                        .then(()=>location.reload());
                }else{
                    Swal.fire('Error',res.message,'error');
                }
            })
            .catch(()=>{
                Swal.fire('Error','Gagal simpan','error');
            });

        });
    }

});
</script>

@endsection