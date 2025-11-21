@extends('layouts.app')
@section('title', 'Unggah File IFC')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/unggah.css') }}">
@endsection

@section('content')
<div class="container mt-5 pt-4">

    {{-- Notifikasi sukses --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Notifikasi error --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form id="uploadForm" action="{{ session('uploaded_file') ? route('Unggah.analyze') : route('Unggah.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div id="drop-zone">

            @if(!session('uploaded_file'))
                <h4 class="fw-bold">Upload File Desain IFC</h4>
                <p>Klik tombol atau seret file ke sini</p>

                <input type="file" id="fileInput" name="file" accept=".ifc,.IFC" style="display:none;">

                <button type="button" id="triggerInput" class="btn btn-light mt-3">
                    Pilih File
                </button>
            @else
                <h4 class="fw-bold">File Siap untuk Analisis</h4>
                <p class="mb-1">{{ session('uploaded_file') }}</p>

                <button type="submit" class="btn btn-success mt-3">Analisis</button>
            @endif

        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const triggerBtn = document.getElementById('triggerInput');

    // Hanya jalankan jika elemen ada (artinya file belum diupload)
    if(triggerBtn && fileInput) {
        triggerBtn.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', (e) => {
            if(e.target.files.length > 0) {
                document.getElementById('uploadForm').submit();
            }
        });
    }
});
</script>
@endsection
