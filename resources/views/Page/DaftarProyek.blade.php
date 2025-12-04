@extends('layouts.app')

@section('title', 'Home | MateRix Smart Database Konstruksi')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/daftarProyek.css') }}">
    <link href="{{ asset('css/homepage.css') }}" rel="stylesheet">
@endsection

@section('content')

{{-- Tombol Back, diletakkan di luar main-content --}}
<div class="position-fixed" style="top: 20px; left: 20px; z-index: 2000;">
    <button onclick="window.location.href='{{ route('HomePage') }}'"
            class="btn btn-light shadow-sm rounded-circle"
            style="width: 50px; height: 50px;"
            title="Kembali ke Halaman Utama">
        <i class="bi bi-arrow-left fs-5"></i>
    </button>
</div>

<div class="main-content">

    <section class="tombol-section">
        <a href="{{ route('Unggah.index') }}">Input Desain</a>
        <a>Input Data Bahan & Supplier</a>
    </section>

    {{-- List Bangunan --}}
    <section class="riwayat-section py-5 bg-light">
        <div class="container">
            <h3 class="fw-bold mb-4">Daftar Proyek Bangunan</h3>

            @if($projects->isEmpty())
                <div class="alert alert-info text-center shadow-sm" role="alert" style="border-radius: 10px;">
                    Belum ada proyek desain rumah 😅 <br>
                    <a href="{{ route('Unggah.index') }}" class="btn btn-warning mt-3">Mulai Proyek Baru</a>
                </div>
            @else
                <div class="list-group shadow-sm">
                    @foreach ($projects as $project)
                        <a href="{{ route('detailProyek.show', $project->ID_Desain_Rumah) }}"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 fw-bold text-primary">{{ $project->Nama_Desain ?? 'Tanpa Judul' }}</h5>
                                <p>{{ Str::limit($project->Deskripsi ?? 'Tidak ada deskripsi proyek', 100) }}</p>
                            </div>
                            <small class="text-secondary">
                                {{ \Carbon\Carbon::parse($project->Tanggal_dibuat)->format('d M Y') }}
                            </small>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
