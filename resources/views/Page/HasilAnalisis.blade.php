@extends('layouts.app')
@section('title', 'Viewer IFC')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/HasilAnalisis.css') }}">
<style>
    /* Styling Wajib */
    #viewer-container {
        position: relative;
        width: 100%;
        height: 75vh; /* Tinggi fixed */
        background: linear-gradient(to bottom, #f0f0f0, #e0e0e0);
        border-radius: 12px;
        overflow: hidden;
        margin-top: 20px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    /* Overlay Loading yang Cantik */
    #loading-overlay {
        position: absolute;
        inset: 0; /* top-left-right-bottom: 0 */
        background: rgba(255,255,255,0.9);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 50;
        backdrop-filter: blur(4px);
        transition: opacity 0.5s ease;
    }

    .loader-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #ddd;
        border-top: 4px solid #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    }

    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
@endsection

@section('content')

<div class="page-spacer">
    <h3>Menampilkan: {{ $desain->Nama_Desain }}</h3>

    @if(empty($ifcUrl))
        <div class="alert alert-danger">
            <strong>Error 404:</strong> File IFC tidak ditemukan di storage.
        </div>
    @else
        <div id="viewer-container">
            <div id="loading-overlay">
                <div class="loader-spinner"></div>
                <div id="loading-text" style="font-weight:600; color:#444;">Menghubungkan ke Engine...</div>
            </div>
        </div>
    @endif
</div>

<script>
    window.IFC_URL = "{{ $ifcUrl ?? '' }}";
</script>

<script type="module" src="{{ asset('js/HasilAnalisis.js') }}?v={{ time() }}"></script>

@endsection
