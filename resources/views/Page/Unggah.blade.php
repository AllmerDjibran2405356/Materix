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
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM UTAMA (UPLOAD / ANALYZE) --}}
    <form id="uploadForm"
          action="{{ session('uploaded_file') ? route('Unggah.analyze') : route('Unggah.upload') }}"
          method="POST" enctype="multipart/form-data">
        @csrf

        <div id="drop-zone">

            @if(!session('uploaded_file'))
                <h4 class="fw-bold">Upload File Desain IFC</h4>
                <p>Klik tombol atau seret file ke sini</p>

                {{-- 1. Input File (Hidden) --}}
                <input type="file"
                       id="fileInput"
                       name="file"
                       accept=".ifc,.IFC"
                       style="display:none;">

                {{-- 2. Label sebagai Tombol (SOLUSI PERMANEN) --}}
                {{-- Menggunakan 'label for' akan otomatis memicu input tanpa JS --}}
                <label for="fileInput" class="btn btn-light mt-3" style="cursor: pointer;">
                    Pilih File
                </label>

            @else
                <h4 class="fw-bold">File Siap untuk Analisis</h4>
                <p class="mb-1">{{ session('uploaded_file') }}</p>

                <button type="submit" class="btn btn-success mt-3">
                    Analisis
                </button>
            @endif
        </div>
    </form>

    {{-- FORM REMOVE DIPISAH --}}
    @if(session('uploaded_file'))
    <form action="{{ route('Unggah.remove') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-danger mt-3 ms-2">
            Hapus File
        </button>
    </form>
    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');

    // Kita TIDAK LAGI butuh listener click untuk tombol trigger
    // Karena <label> sudah menangani klik secara native.

    if(fileInput) {
        fileInput.addEventListener('change', function(e) {
            // Cek apakah user benar-benar memilih file (length > 0)
            // Jika user tekan Cancel, files.length akan 0 dan form tidak akan di-submit
            if(this.files && this.files.length > 0) {
                document.getElementById('uploadForm').submit();
            }
        });
    }
});
</script>

@endsection
