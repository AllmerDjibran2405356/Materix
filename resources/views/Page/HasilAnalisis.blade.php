@extends('layouts.app')
@section('title', 'Viewer - ' . $desain->Nama_Desain)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/HasilAnalisis.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Style tambahan untuk tombol back */
        .btn-back-viewer {
            position: fixed;
            top: 20px;
            left: 20px;
            background: white;
            color: #333;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            z-index: 2000 !important; /* Z-index tinggi */
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 20px;
        }

        .btn-back-viewer:hover {
            background: #f8f9fa;
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Pastikan container tidak menutupi */
        #viewer-container {
            position: relative;
            width: 100%;
            height: 100vh;
        }
    </style>
@endsection

@section('content')
@if(empty($ifcUrl))
    <div class="container mt-5">
        <div class="alert alert-danger">Error: File IFC tidak ditemukan di server.</div>
    </div>
@else
    <!-- TOMBOL BACK DI LUAR CONTAINER UNTUK PASTI TERLIHAT -->
    <a href="{{ route('detailProyek.show', $desain->ID_Desain_Rumah) }}"
       class="btn-back-viewer"
       title="Kembali ke Detail Proyek"
       id="back-button">
        <i class="bi bi-arrow-left"></i>
    </a>

    <div id="viewer-container">
        <!-- Overlay Controls -->
        <div class="title-overlay"><h3>{{ $desain->Nama_Desain }}</h3></div>
        <div class="controls-overlay">
            <h5>🎮 Controls</h5>
            <div class="control-item"><div class="control-icon">🖱️</div><span>Klik Kiri: Putar | Kanan: Geser</span></div>
            <div class="control-item"><div class="control-icon">⌨️</div><span><b>W-A-S-D</b>: Bergerak</span></div>
            <div class="control-item"><div class="control-icon">🚀</div><span><b>Shift</b>: Lari Cepat</span></div>
            <div class="control-item"><div class="control-icon">🔍</div><span><b>Scroll</b>: Zoom In/Out</span></div>
        </div>

        <!-- Loading -->
        <div id="loading-overlay">
            <div class="loader-spinner"></div>
            <div id="loading-text" style="font-weight:600; color:#444;">Menghubungkan...</div>
        </div>

        <!-- Properties Panel -->
        <div id="properties-panel">
            <div class="prop-header">
                <h4>Detail Objek</h4>
                <button class="close-btn" onclick="closeProperties()">×</button>
            </div>
            <div id="properties-content" class="prop-content">
                <p style="color:#9ca3af; text-align:center; margin-top:50px;">
                    Klik pada objek model<br>untuk melihat detail.
                </p>
            </div>
        </div>

        <!-- Tombol Simpan -->
        <button class="btn-save-floating" onclick="saveProject()" id="save-button">
            <span>💾</span> Simpan Data
        </button>
    </div>
@endif

<script>
    // INJECT DATA DARI LARAVEL KE JAVASCRIPT
    window.IFC_URL = "{{ $ifcUrl ?? '' }}";
    window.ANALYSIS_DATA = @json($data);
    window.WORKS_DATA = @json($works);
    window.ID_DESAIN = "{{ $desain->ID_Desain_Rumah }}";

    // ROUTE API
    window.API_SEARCH_URL = "{{ route('api.cari_komponen') }}";
    window.API_GET_JOBS = "{{ route('api.get_jobs') }}";
    window.API_SAVE_JOB = "{{ route('api.save_job') }}";
    window.API_REMOVE_JOB = "{{ route('api.remove_job') }}";
    window.API_FINAL_SAVE = "{{ route('api.final_save') }}";
    window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]') ?
                       document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

    function closeProperties() {
        const panel = document.getElementById('properties-panel');
        const container = document.getElementById('viewer-container');
        if (panel) panel.classList.remove('active');
        if (container) container.classList.remove('panel-open');
    }

    function saveProject() {
        console.log('Tombol Simpan diklik');
        if (typeof window.triggerFinalSave === 'function') {
            window.triggerFinalSave();
        } else {
            alert('Fungsi save belum tersedia. Tunggu model selesai dimuat.');
        }
    }

    // Debug: Pastikan tombol back ada dan terlihat
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded - Viewer Page');

        const backBtn = document.getElementById('back-button');
        if (backBtn) {
            console.log('Tombol Back ditemukan:', backBtn);
            // Force style untuk memastikan terlihat
            backBtn.style.display = 'flex';
            backBtn.style.visibility = 'visible';
            backBtn.style.opacity = '1';
            backBtn.style.zIndex = '2000';
            backBtn.style.position = 'fixed';
            backBtn.style.top = '20px';
            backBtn.style.left = '20px';
        } else {
            console.error('Tombol Back tidak ditemukan!');
        }

        const saveBtn = document.getElementById('save-button');
        if (saveBtn) {
            console.log('Tombol Simpan ditemukan:', saveBtn);
        }
    });
</script>

<script type="module" src="{{ asset('js/HasilAnalisis.js') }}?v={{ time() }}"></script>
@endsection
