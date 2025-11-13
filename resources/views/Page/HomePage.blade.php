@extends('layouts.app')

@section('title', 'Home | MateRix Smart Database Konstruksi')

@section('content')
<section class="hero-home">
    <div class="container py-4 px-3" style="background: linear-gradient(to right, #FF6600, #FF8533); border-radius: 10px; color: #fff;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-2">Selamat Datang, {{ $user->name }}!</h2>
                <p>Halo, {{ $user->name }}! Saatnya wujudkan rencana konstruksimu dengan lebih efisien</p> {{-- bagian ini yang buat ngatur munculnya si halo, user --}}
            </div>
            <div class="col-md-4 text-end">
                <img src="{{ asset('images/worker.png') }}" alt="Worker" style="max-height: 150px;"> {{-- ini buat gambar --}}
            </div>
        </div>
    </div>
</section>

{{-- 2 Tombol dibawah Hero  --}}
<section class="fitur-section py-5">
   <div class="container">
       <div class="row text-center">
           <div class="col-md-6 mb-4">
               <a href="{{ route('Kalkulasi.index') }}" class="btn btn-primary btn-lg w-100 shadow-sm">
                   Kalkulasi Material
               </a>
           </div>
           <div class="col-md-6 mb-4">
               <a href="{{ route('Bahan.index') }}" class="btn btn-secondary btn-lg w-100 shadow-sm">
                   Harga Bahan & Produsen Material
               </a>
           </div>

{{-- Riwayat Bangunan kalcer (kalo user baru nampilin pesan singkat cihuy) --}}
<section class="riwayat-section py-5 bg-light">
    <div class="container">
        <h3 class="fw-bold mb-4">Riwayat Bangunan</h3>

        @if ($projects->isEmpty())
            <div class="alert alert-info text-center shadow-sm" role="alert" style="border-radius: 10px;">
                Belum ada proyek desain rumah yang kamu unggah 😅 <br>
                Yuk mulai kalkulasi pertamamu sekarang!
                <br>
                <a href="{{ route('Kalkulasi.index') }}" class="btn btn-warning mt-3">Mulai Sekarang</a>
            </div>
        @else
            <div class="list-group shadow-sm">
                @foreach ($projects as $project)
                    <a href="{{ route('Kalkulasi.show', $project->id) }}" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 fw-bold text-primary">{{ $project->Nama_desain ?? 'Tanpa Judul' }}</h5>
                            <p class="mb-1 text-muted">
                                {{ Str::limit($project->Deskripsi ?? 'Tidak ada deskripsi proyek', 100) }}
                            </p>
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