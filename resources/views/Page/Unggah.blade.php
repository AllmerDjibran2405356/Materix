@extends('layouts.app')
@section('title', 'Unggah File IFC')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/unggah.css') }}">
@endsection

@section('content')

<div class="unggah-page-wrapper">

    <div class="unggah-logo-wrapper">
        <img src="../images/materixlogos.png" alt="Materix Logo">
    </div>

    <h1 class="unggah-title">Unggah desain anda.</h1>

    {{-- Notifikasi sukses --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
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

    <div class="unggah-container">
        <form id="uploadForm" action="{{ route('Unggah.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div id="drop-zone">
                <div class="drop-border">
                    {{-- Jika belum upload file --}}
                    @if(!session('uploaded_file'))
                        <p class="drop-main-text">
                            <span class="orange-text">Tarik dan letakkan gambar</span><br>
                            atau telusuri untuk mengunggah.
                        </p>
                        <input type="file" id="fileInput" name="file" accept=".ifc,.IFC">

                        <button type="button" id="triggerInput" class="unggah-btn">Unggah berkas.</button>

                        <p class="drop-sub-text">Unggah berkas Desain IFC anda.</p>
                @else
                    {{-- Jika file sudah diupload --}}
                    <p class="drop-main-text">
                        <span class="orange-text">Tarik dan letakkan gambar</span><br>
                        atau telusuri untuk mengunggah.
                    </p>
                    <p class="mb-1">{{ session('uploaded_file') }}</p>

                    <input type="file" id="fileInput" name="file" accept=".ifc,.IFC">

                    <button id="triggerInput" type="button" class="unggah-btn">Ubah berkas.</button>

                    <p class="drop-sub-text">Ubah berkas Desain IFC anda.</p>

                @endif
                </div>
            </div>
        </form>

</div>

<script>
// Klik tombol
document.getElementById('triggerInput').addEventListener('click', () => {
    document.getElementById('fileInput').click();
});

// Begitu file dipilih -> submit form otomatis
document.getElementById('fileInput').addEventListener('change', (e) => {
    if(e.target.files.length > 0){
        document.getElementById('uploadForm').submit();
    }
});
</script>

@endsection
