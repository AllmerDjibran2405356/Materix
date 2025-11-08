<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MateRix</title>

  {{-- Bootstrap & Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  {{-- Custom CSS --}}
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

  {{-- NAVBAR --}}
  @include('layouts.navbarLoggedOut')

  {{-- HERO --}}
  <section class="hero position-relative d-flex align-items-center justify-content-center">
    <img src="{{ asset('images/banner.jpg') }}" alt="MateRix Banner" class="hero-bg">

    <div class="hero-overlay-orange"></div>

    <div class="container hero-text text-white text-center">
      <h2>Optimalkan Setiap Material,<br>Maksimalkan Hasil Pembangunan.</h2>
    </div>
  </section>

  {{-- ABOUT SECTION --}}
  <section class="about">
    <div class="container">
      <h2 class="about-title">Tentang MateRix</h2>
      <div class="about-box">
        <p>
          MateRix adalah sistem berbasis web untuk menghitung kebutuhan material konstruksi secara otomatis.
          Aplikasi ini menganalisis desain bangunan dan mengintegrasikan data harga bahan untuk menghasilkan
          estimasi biaya proyek yang akurat. Dengan MateRix, perencanaan konstruksi jadi lebih cepat, efisien,
          dan minim kesalahan.
        </p>
      </div>
    </div>
  </section>

  {{-- FEATURES SECTION --}}
  <section class="features">
    <div class="container text-white">
      <h2 class="section-title">Apa yang Bisa Anda Lakukan?</h2>

      <div class="feature-item">
        <img src="{{ asset('images/icon-analisis.png') }}" alt="Digitalisasi" class="feature-icon">
        <div class="feature-text">
          <h3>Digitalisasi & Analisis Desain Bangunan</h3>
          <p>Anda dapat dengan mudah mengunggah desain bangunan, untuk mendapatkan analisis otomatis mengenai kebutuhan material yang diperlukan dalam proyek.</p>
        </div>
      </div>

      <div class="feature-item">
        <img src="{{ asset('images/icon-supplier.png') }}" alt="Supplier" class="feature-icon">
        <div class="feature-text">
          <h3>Kelola Data Bahan dan Supplier</h3>
          <p>Melalui fitur ini, anda bisa memperbarui harga bahan dan memilih supplier yang sesuai, sehingga perhitungan anggaran proyek menjadi lebih akurat dan terkini.</p>
        </div>
      </div>

      <div class="feature-item">
        <img src="{{ asset('images/icon-estimasi.png') }}" alt="Estimasi" class="feature-icon">
        <div class="feature-text">
          <h3>Perhitungan & Laporan Estimasi Proyek</h3>
          <p>Pengguna dapat melihat hasil analisis dalam bentuk laporan lengkap yang bisa diunduh sebagai PDF atau Excel untuk kebutuhan dokumentasi dan perencanaan.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- Bootstrap JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
