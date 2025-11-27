@extends('layouts.app')
@section('title', 'Viewer - ' . $desain->Nama_Desain)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/HasilAnalisis.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')

@if(empty($ifcUrl))
    <div class="container mt-5">
        <div class="alert alert-danger">Error: File IFC tidak ditemukan.</div>
    </div>
@else
    <div id="viewer-container">
        {{-- 1. TITLE OVERLAY --}}
        <div class="title-overlay">
            <h3>{{ $desain->Nama_Desain }}</h3>
        </div>

        {{-- 2. CONTROLS OVERLAY --}}
        <div class="controls-overlay">
            <h5>🎮 Controls</h5>
            <div class="control-item"><div class="control-icon">🖱️</div><span>Klik Kiri: Putar | Kanan: Geser</span></div>
            <div class="control-item"><div class="control-icon">⌨️</div><span><b>W-A-S-D</b>: Bergerak</span></div>
            <div class="control-item"><div class="control-icon">🚀</div><span><b>Shift</b>: Lari Cepat</span></div>
            <div class="control-item"><div class="control-icon">🔍</div><span><b>Scroll</b>: Zoom In/Out</span></div>
        </div>

        {{-- 3. LOADING SCREEN --}}
        <div id="loading-overlay">
            <div class="loader-spinner"></div>
            <div id="loading-text" style="font-weight:600; color:#444;">Menghubungkan...</div>
        </div>

        {{-- 4. PROPERTIES SIDEBAR --}}
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
    </div>
@endif

{{-- SCRIPT CONFIG --}}
<script>
    window.IFC_URL = "{{ $ifcUrl ?? '' }}";
    window.ANALYSIS_DATA = @json($data);
    window.WORKS_DATA = @json($works);

    // Config ID & Routes
    window.ID_DESAIN = "{{ $desain->ID_Desain_Rumah }}";
    window.API_SEARCH_URL = "{{ route('api.cari_komponen') }}";

    // Config API Session (Baru)
    window.API_GET_JOBS = "{{ route('api.get_jobs') }}";
    window.API_SAVE_JOB = "{{ route('api.save_job') }}";
    window.API_REMOVE_JOB = "{{ route('api.remove_job') }}";

    // Ambil token CSRF dari meta tag
    window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        : '';

    function closeProperties() {
        document.getElementById('properties-panel').classList.remove('active');
    }
</script>

{{-- SCRIPT MODULE --}}
<script type="module" src="{{ asset('js/HasilAnalisis.js') }}?v={{ time() }}"></script>

@endsection
