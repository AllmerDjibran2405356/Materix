@extends('layouts.app')

@section('title', 'Daftar Proyek')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/daftarProyek.css') }}">
@endsection

@section('content')

<div class="main-content">

    <div class="left-sidebar">
        <div class="btn-wrapper">
            <a href="{{ route('Unggah.index') }}" class="unggah-btn">
                Input Design
            </a>
        </div>
        <div class="btn-wrapper">
            <a href="#" class="data-btn">
                Input Data Bahan & Supplier
            </a>
        </div>        
    </div>

    <div class="right-content">

        <div class="search-wrapper">
            <input type="text" placeholder="Search">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
        </div>

    <section class="riwayat-section">
        <div class="container">

            @if($projects->isEmpty())

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
                <div class="list-group">
                    @foreach ($projects as $project)
                        <a href="{{ route('detailProyek.show', $project->ID_Desain_Rumah) }}"
                           class="riwayat-item">

                            <div>
                                <h5>{{ $project->Nama_desain ?? 'Tanpa Judul' }}</h5>
                                <p>{{ \Carbon\Carbon::parse($project->Tanggal_dibuat)->format('d/m/Y | H:i') }}</p>
                            </div>

                            <span class="dots">⋮</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
