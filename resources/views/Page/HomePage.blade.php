@extends('layouts.app')

@section('title', 'Home | MateRix Smart Database Konstruksi')

@section('styles')
    <link href="{{ asset('css/homepage.css') }}" rel="stylesheet">
@endsection

@section('content')
<section class="hero-home">
    <img src="{{ asset('images/landingpage.jpg') }}" alt="Background" class="hero-bg">

    <div class="hero-overlay"></div>

    <div class="hero-content">
        <div class="hero-column">
            <div class="speech-bubble">
                <p class="mb-0 fw-semibold">
                    Hai! Mari kita mulai perhitungan material konstruksi bangunanmu!
                </p>
            </div>

            <img src="{{ asset('images/pekerja.png') }}" alt="Worker" class="hero-character">
        </div>

        <div class="hero-text">
            <h2 class="fw-bold">Selamat Datang,<br>{{ $user->username }}!</h2>
        </div>
    </div>
</section>

<section class="fitur-section py-5">
    <div class="container">
        <div class="dual-container-wrapper">

            <div class="fitur-box">
                <img src="../images/icon-kalkulasi.png" alt="icon" class="fitur-icon">
                <a href="{{ route('DaftarProyek.index') }}" class="fitur-btn">
                    Kalkulasi Material
                </a>
            </div>

            <div class="fitur-box">
                <img src="../images/icon-chart.png" alt="icon" class="fitur-icon">
                <a href="{{ route('master-bahan.index') }}" class="fitur-btn">
                    Harga Bahan & Produsen Material
                </a>
            </div>

        </div>
    </div>
</section>

{{-- Riwayat Bangunan --}}
<section class="riwayat-section py-5 bg-light">
    <div class="container">

        @if ($projects->isEmpty())

            <div class="riwayat-empty-wrapper">

                <img src="{{ asset('images/bobwonder.png') }}" class="riwayat-character" alt="Character">

                <div class="riwayat-empty-box">
                    <p class="riwayat-empty-text">
                        Belum ada proyek desain rumah yang kamu unggah 😅 <br>
                        Yuk mulai proyek pertamamu!
                    </p>
                    <div class="btn-wrapper">
                        <a href="{{ route('Unggah.index') }}" class="mulai-btn">
                            + Mulai Proyek Baru
                        </a>
                    </div>
                </div>
            </div>

        @else

            <div class="riwayat-list-wrapper">

                <div class="riwayat-list-header">
                    <span>Lanjutkan Proyek Terakhir</span>
                    <a href="{{ route('DaftarProyek.index') }}" class="lihat-semua">Lihat Semua</a>
                </div>

                <div class="list-group">
                    @foreach ($projects as $project)
                        <a href="{{ route('detailProyek.show', $project->ID_Desain_Rumah) }}"
                           class="riwayat-item">

                            <div>
                                <h5>{{ $project->Nama_Desain ?? 'Tanpa Judul' }}</h5>
                                <p>{{ \Carbon\Carbon::parse($project->Tanggal_dibuat)->format('d/m/Y') }}</p>
                            </div>

                            <span class="dots">⋮</span>
                        </a>
                    @endforeach
                </div>

                <div class="riwayat-footer">
                    <a href="{{ route('Unggah.index') }}" class="mulai-btn">
                        + Mulai Proyek Baru
                    </a>
                </div>

            </div>

        @endif

    </div>
</section>
@endsection
