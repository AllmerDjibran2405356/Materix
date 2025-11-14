@extends('layouts.main')

@section('Materix', 'MateRix | Smart Database Konstruksi')

@section('content')
<link rel="stylesheet" href="{{ asset('css/landing.css') }}">

{{-- ================= Container 1 ================= --}}
<section class="hero-container">
    <div class="hero-overlay"></div>
    <img src="{{ asset('images/landingpage.png') }}" alt="Materix Building" class="hero-bg">
    <div class="hero-text">
        <h1>Optimalkan Setiap Material,<br>Maksimalkan Hasil Pembangunan.</h1>
    </div>
</section>

{{-- ================= Container 2 ================= --}}
<section class="about-container">
    <div class="about-content">
        <div class="about-text">
            <h2>Tentang MateRix</h2>
            <p>
                Materix adalah sistem berbasis web untuk menghitung kebutuhan material konstruksi secara otomatis.
                Aplikasi ini menganalisis desain bangunan dan mengintegrasikan data harga bahan untuk menghasilkan estimasi biaya proyek yang akurat.
                Dengan Materix, perencanaan konstruksi jadi lebih cepat, efisien, dan minim kesalahan.
            </p>
        </div>
        <div class="about-image">
            <img src="{{ asset('images/building.jpg') }}" alt="Gedung Materix">
        </div>
    </div>
</section>

{{-- ================= Container 3 ================= --}}
<section class="fetures-header">
    <div class="features-header">
        <h2 class="features-header">Apa yang Bisa Anda Lakukan?</h2>
    </div>

</section>
<section class="features-container">
    
    <div class="features-list">
        <div class="feature-item">
            <div class="feature-img">
                <img src="{{ asset('icons/digitalisasi.svg') }}" alt="Digitalisasi">
            </div>
            <div class="feture-text">
                <h3>Digitalisasi & Analisis Desain Bangunan</h3>
                <p>Anda dapat dengan mudah menginputkan desain bangunan untuk mendapatkan estimasi otomatis mengenai kebutuhan bahan yang diperlukan dalam proyek.</p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-img">
                <img src="{{ asset('icons/supplier.svg') }}" alt="Supplier">
            </div>
            <div class="feture-text">
                <h3>Kelola Data Bahan dan Supplier</h3>
                <p>Materix membantu Anda mengontrol harga bahan dan memilih supplier yang sesuai dengan estimasi proyek agar efisien dan tepat anggaran.</p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-img">
                <img src="{{ asset('icons/laporan.svg') }}" alt="Laporan">
            </div>
            <div class="feture-text">
                <h3>Perhitungan & Laporan Estimasi Proyek</h3>
                <p>Pengguna dapat melihat hasil estimasi dan laporan langsung yang dapat diunduh sebagai PDF untuk kebutuhan dokumentasi dan presentasi proyek.</p>
            </div>
        </div>

    </div>
</section>
@endsection
