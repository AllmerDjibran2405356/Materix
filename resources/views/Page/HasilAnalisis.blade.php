@extends('layouts.app')
@section('title', 'Viewer - ' . $desain->Nama_Desain)

@section('styles')
    {{-- Panggil CSS External --}}
    <link rel="stylesheet" href="{{ asset('css/HasilAnalisis.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
@if(empty($ifcUrl))
    <div class="container mt-5">
        <div class="alert alert-danger">Error: File IFC tidak ditemukan di server.</div>
    </div>
@else
    <div id="viewer-container">

        <div class="viewer-header-group">

            <a href="{{ route('detailProyek.show', $desain->ID_Desain_Rumah) }}"
               class="btn-back-viewer"
               title="Kembali ke Detail Proyek"
               id="back-button">
                <i class="bi bi-arrow-left"></i>
            </a>

            <div class="viewer-title-badge">
                <h3>{{ $desain->Nama_Desain }}</h3>
            </div>

        </div>

        <div class="controls-overlay">
            <h5>🎮 Controls</h5>
            <div class="control-item"><div class="control-icon">🖱️</div><span>Klik Kiri: Putar | Kanan: Geser</span></div>
            <div class="control-item"><div class="control-icon">⌨️</div><span><b>W-A-S-D</b>: Bergerak</span></div>
            <div class="control-item"><div class="control-icon">🚀</div><span><b>Shift</b>: Lari Cepat</span></div>
            <div class="control-item"><div class="control-icon">🔍</div><span><b>Scroll</b>: Zoom In/Out</span></div>
        </div>

        <div id="loading-overlay">
            <div class="loader-spinner"></div>
            <div id="loading-text" style="font-weight:600; color:#444;">Menghubungkan...</div>
        </div>

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

        <button class="btn-save-floating" onclick="saveProject()" id="save-button">
            <span>💾</span> Simpan Data
        </button>
    </div>
@endif

<script>
    // INJECT DATA
    window.IFC_URL = "{{ $ifcUrl ?? '' }}";
    window.ANALYSIS_DATA = @json($data);
    window.WORKS_DATA = @json($works);
    window.ID_DESAIN = "{{ $desain->ID_Desain_Rumah }}";

    // ROUTES
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

    document.addEventListener('DOMContentLoaded', function() {
        // Cek elemen
        const backBtn = document.getElementById('back-button');
        if (!backBtn) console.error('Tombol Back tidak ditemukan!');
        const saveBtn = document.getElementById('save-button');
        if (!saveBtn) console.error('Tombol Simpan tidak ditemukan!');
    });
</script>

<script type="module" src="{{ asset('js/HasilAnalisis.js') }}?v={{ time() }}"></script>
@endsection
