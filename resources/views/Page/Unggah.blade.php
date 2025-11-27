@extends('layouts.app')
@section('title', 'Unggah File IFC')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/unggah.css') }}">
@endsection

@section('content')

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

    <!-- LOGO -->
    <div class="unggah-logo-wrapper">
        <img src="{{ asset('images/materixlogos.png') }}" alt="materix logo">
    </div>

    <!-- TITLE -->
    <h1 class="unggah-title">Unggah desain anda.</h1>

    <div class="unggah-container">

    <!-- FORM -->
    <form id="uploadForm"
          action="{{ session('uploaded_file') ? route('Unggah.analyze') : route('Unggah.upload') }}"
          method="POST" enctype="multipart/form-data">
        @csrf

        <div id="drop-zone">

            @if(!session('uploaded_file'))
                <h4 class="unggah-h4">
                    <span class="orange">Tarik dan letakkan gambar atau </span>
                    <span class="white"> telusuri berkas untuk mengunggah.</span>
                </h4>

                {{-- 1. Input File (Hidden) --}}
                <input type="file"
                       id="fileInput"
                       name="file"
                       accept=".ifc,.IFC"
                       style="display:none;">

                {{-- 2. Label sebagai Tombol (SOLUSI PERMANEN) --}}
                {{-- Menggunakan 'label for' akan otomatis memicu input tanpa JS --}}
                <label for="fileInput" class="btn btn-light mt-3" style="cursor: pointer;">
                    Unggah berkas.
                </label>
                <p class="drop-sub-text">Upload berkas desain IFC anda.</p>

            @else
                <h4 class="unggah-h4">
                    <span class="white"> Berkas siap untuk di Analisis.</span>
                </h4>
                <p class="mb-1">{{ session('uploaded_file') }}</p>

                <button type="submit" class="btn btn-success mt-3">
                    Analisis
                </button>
            @endif
        </div>
    </form>

</div>
{{-- FORM REMOVE DIPISAH --}}
@if(session('uploaded_file'))
<div class="hapus-wrapper">
    <form action="{{ route('Unggah.remove') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="hapus-btn">
            Hapus File
        </button>
    </form>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');

    if(fileInput) {
        fileInput.addEventListener('change', function(e) {
            if(this.files && this.files.length > 0) {
                document.getElementById('uploadForm').submit();
            }
        });
    }
});
</script>

@endsection
