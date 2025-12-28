@extends('layouts.app')

@section('title', 'Detail Proyek')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/detailProyek.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
@endsection

@section('content')

<div class="detailProyek-container">
    <a href="{{ route('DaftarProyek.index') }}" class="back-home-btn">
        <i class="bi bi-arrow-left"></i>
        <span>Kembali ke Daftar Proyek</span>
    </a>

    <h1 class="project-title">“{{ $project->Nama_Desain ?? 'Tanpa Judul' }}”</h1>

    <a href="{{ route('viewer', ['id' => $project->ID_Desain_Rumah]) }}" class="big-button">
        <img src="{{ asset('images/eye.png') }}" class="big-btn-icon" alt="icon">
        <span>Lihat Desain & Pilih Pekerjaan</span>
    </a>

    <div class="bottom-button-wrapper">

        <a href="{{ route('dataProyek.index', ['id' => $project->ID_Desain_Rumah]) }}" class="card-btn">
            <img src="{{ asset('images/file.png') }}" class="card-icon" alt="icon">
            <span>Data Material</span>
        </a>

        <a href="{{ route('laporan.index', ['id' => $project->ID_Desain_Rumah]) }}" class="card-btn">
            <img src="{{ asset('images/report.png') }}" class="card-icon" alt="icon">
            <span>Anggaran Material</span>
        </a>

    </div>
</div>

@endsection
