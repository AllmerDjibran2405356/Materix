@extends('layouts.app')
@section('title', 'Unggah File IFC')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/unggah.css') }}">
<link rel="stylesheet" href="{{ asset('/css/navbar.css') }}">
@endsection

@section('content')

<nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="{{ route('landing') }}">
          <img src="{{ asset('/images/materixlogos.png') }}" alt="Logo" class="navbar-logo">
          <span class="logo-text">aterix</span>
      </a>

  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <div class="ms-auto d-flex align-items-center gap-2">
          {{-- Tombol Masuk dengan style putih --}}
          <a class="btn btn-masuk-custom" href="{{ route('login.form') }}">
            Masuk
          </a>
          
          {{-- Tombol Daftar dengan style outline putih --}}
          <a class="btn btn-daftar-custom" href="{{ route('daftar.form') }}">
            Daftar
          </a>
        </div>
      </div>
    </div>
  </nav>

<div class="unggah-page-wrapper">
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

    <div class="unggah-logo-wrapper">
        <img src="{{ asset('images/materixlogos.png') }}" alt="materix logo">
    </div>

    <h1 class="unggah-title">Unggah desain anda.</h1>

    <div class="unggah-container">

        {{-- KONDISI 1: BELUM ADA FILE (TAMPILKAN FORM UPLOAD) --}}
        @if(!session('uploaded_file'))

            <form id="uploadForm"
                  action="{{ route('Unggah.upload') }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                <div id="drop-zone">
                    <h4 class="unggah-h4">
                        <span class="orange">Tarik dan letakkan gambar atau </span>
                        <span class="white"> telusuri berkas untuk mengunggah.</span>
                    </h4>

                    {{-- Input File (Hidden) --}}
                    <input type="file"
                           id="fileInput"
                           name="file"
                           accept=".ifc,.IFC"
                           style="display:none;">

                    {{-- Label Pemicu Input --}}
                    <label for="fileInput" class="btn btn-light mt-3" style="cursor: pointer;">
                        Unggah berkas.
                    </label>
                    <p class="drop-sub-text">Upload berkas desain IFC anda.</p>
                </div>
            </form>

        {{-- KONDISI 2: FILE SUDAH ADA DI SESSION (TAMPILKAN TOMBOL ANALISIS) --}}
        @else

            <div id="drop-zone"> <h4 class="unggah-h4">
                    <span class="white"> Berkas siap untuk di Analisis.</span>
                </h4>
                <p class="mb-1 text-white">{{ session('uploaded_file') }}</p> {{-- Tambah text-white agar terbaca --}}

                {{-- FORM KHUSUS ANALISIS --}}
                <form action="{{ route('Unggah.analyze') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success mt-3">
                        Analisis
                    </button>
                </form>
            </div>

        @endif

    </div>

    {{-- TOMBOL HAPUS (DIPISAH DILUAR CONTAINER UTAMA) --}}
    @if(session('uploaded_file'))
    <div class="hapus-wrapper mt-3 text-center">
        <form action="{{ route('Unggah.remove') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="hapus-btn btn btn-danger">
                Hapus File / Batal
            </button>
        </form>
    </div>
    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');

    // Hanya jalankan listener jika fileInput ada (artinya sedang di mode upload)
    if(fileInput) {
        fileInput.addEventListener('change', function(e) {
            if(this.files && this.files.length > 0) {
                // Submit form upload secara otomatis saat file dipilih
                document.getElementById('uploadForm').submit();
            }
        });
    }
});
</script>

@endsection
