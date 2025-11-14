{{-- Beritahu Blade untuk "memakai" Master Layout --}}
@extends('layouts.app')

{{-- Ini adalah judul halaman yang akan muncul di tab browser --}}
@section('title', 'Pengaturan Akun')

{{-- Ini adalah konten utama halaman --}}
@section('content')
<link rel="stylesheet" href="{{ asset('css/pengaturan.css') }}">

    <div class="container">
        
        {{-- 1. "Penangkap" Pesan Sukses (jika berhasil ubah info) --}}
        @if(session('success'))
            <div class="alert alert-success shadow-sm mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- 2. "Penangkap" Pesan Error (jika sandi lama salah) --}}
        @if(session('error'))
            <div class="alert alert-danger shadow-sm mb-4">
                {{ session('error') }}
            </div>
        @endif
        

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm mb-4">
                <strong>Terjadi Kesalahan Validasi:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="header">
            <div class="d-flex align-items-center mb-4">
                @auth
                <div class="avatar-container position-relative">
                    <img src="{{ auth()->user()->getAvatarUrl() }}" 
                        alt="Foto Profil" 
                        class="avatar-profile rounded-circle"
                        style="cursor: pointer;"
                        data-bs-toggle="modal" 
                        data-bs-target="#ubahAvatarModal">
                    <div class="avatar-border"></div>
                </div>
                @endauth
                
                <h1>Pengaturan</h1>
            </div>
        </div>

        {{-- Kartu "Akun Saya" --}}
        <div class="section akun-container">
            <div class="section-header">
                <h2>Akun Saya</h2>
                <button class="btn btn-success btn-ubah" data-bs-toggle="modal" data-bs-target="#ubahAkunModal">
                    Ubah
                </button>
            </div>
            <div class="section-body">
                <table class="info-table">
                    <tbody>
                        {{-- Kita gunakan data user yang sedang login --}}
                        @auth
                        <tr>
                            <td>Nama Pengguna</td>
                            <td>{{ auth()->user()->username }}</td>
                        </tr>
                        <tr>
                            <td>Nama Depan</td>
                            <td>{{ auth()->user()->first_name }}</td>
                        </tr>
                        <tr>
                            <td>Nama Belakang</td>
                            <td>{{ auth()->user()->last_name }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>{{ auth()->user()->email }}</td>
                        </tr>
                        <tr>
                            <td>Kata Sandi</td>
                            <td>**********</td>
                        </tr>
                        <tr>
                            <td>Tanggal Bergabung</td>
                            <td>{{ optional(auth()->user()->Tanggal_dibuat)->format('F d, Y') }}</td>
                        </tr>
                        @else
                                               
                        @endauth
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kartu "Informasi Website" --}}
        <div class="section info-container">
            <div class="section-header">
                <h2>Informasi Laman</h2>
            </div>

            <div class="section-body">
                <table class="info-table">
                    <tr>
                        <td>Versi</td>
                        <td>web.release.ver1.0</td>
                    </tr>
                </table>
            </div>
        </div>

            <div class="text-end mt-5">
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
                    <i class="bi bi-box-arrow-right me-2 text-white"></i>Logout
                </button>
            </div>

    </div>

    {{-- "Suntik" semua kode pop-up (Modal) dari file partial --}}
    @include('partials.pengaturanpartials')

@endsection

{{-- Ini adalah @push untuk script kustom Anda --}}
@push('scripts')
<script>
    // Karena kita sudah memindahkan JS ke app.js,
    // bagian ini bisa dibiarkan kosong.
</script>
@endpush