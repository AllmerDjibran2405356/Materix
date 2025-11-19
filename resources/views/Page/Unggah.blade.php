@extends('layouts.app')
@section('title', 'Unggah File IFC')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/unggah.css') }}">
@endsection

@section('content')

<div class="container mt-5 pt-4">

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

    <form id="uploadForm" action="{{ route('Unggah.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div id="drop-zone">

            {{-- Jika belum upload file --}}
            @if(!session('uploaded_file'))
                <h4 class="fw-bold">Upload File Desain IFC</h4>
                <p>Klik tombol atau seret file ke sini</p>

                <input type="file" id="fileInput" name="file" accept=".ifc,.IFC">

                <button type="button" id="triggerInput" class="btn btn-light mt-3">
                    Pilih File
                </button>

            @else
                {{-- Jika file sudah diupload --}}
                <h4 class="fw-bold">File Berhasil Diupload</h4>
                <p class="mb-1">{{ session('uploaded_file') }}</p>

                <input type="file" id="fileInput" name="file" accept=".ifc,.IFC">

                <button type="button" id="triggerInput" class="btn btn-warning mt-3">
                    Ubah File
                </button>
            @endif

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
