{{-- Beritahu Blade untuk "memakai" Master Layout --}}
@extends('layouts.app')

{{-- Ini adalah judul halaman yang akan muncul di tab browser --}}
@section('title', 'Pengaturan Akun')

{{-- Ini adalah konten utama halaman --}}
@section('content')
    <div class="container pengaturan-container my-4 my-md-5">

        {{-- ▼▼▼ "PENANGKAP" PESAN SEKARANG AKAN BERFUNGSI ▼▼▼ --}}
        
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
        
        {{-- ▲▲▲ BATAS "PENANGKAP" ▲▲▲ --}}

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

        <div class="d-flex align-items-center mb-4">
            
            {{-- Tampilan Foto Profil (Sederhana/Non-Aktif) --}}
            @auth
                <img src="{{ asset('images/almer katelpak.jpg') }}" 
                     alt="Foto Profil" 
                     class="avatar-icon me-3" 
                     style="cursor: default;">
            @endauth
            
            <h1 class="pengaturan-title">Pengaturan</h1>
        </div>

        {{-- Kartu "Akun Saya" --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header section-header">
                <h4 class="mb-0">Akun Saya</h4>
                <button class="btn btn-success btn-ubah" data-bs-toggle="modal" data-bs-target="#ubahAkunModal">
                    Ubah
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-borderless mb-0 pengaturan-table">
                    <tbody>
                        {{-- Kita gunakan data user yang sedang login --}}
                        @auth
                        <tr>
                            <th>Nama Pengguna</th>
                            <td>{{ auth()->user()->username }}</td>
                        </tr>
                        <tr>
                            <th>Nama Depan</th>
                            <td>{{ auth()->user()->first_name }}</td>
                        </tr>
                        <tr>
                            <th>Nama Belakang</th>
                            <td>{{ auth()->user()->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ auth()->user()->email }}</td>
                        </tr>
                        <tr>
                            <th>Kata Sandi</th>
                            <td>**********</td>
                        </tr>
                        <tr>
                            <th>Tanggal Bergabung</th>
                            <td>{{ auth()->user()->Tanggal_dibuat->format('F d, Y') }}</td>
                        </tr>
                        @else
                                               
                        @endauth
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kartu "Informasi Website" --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header section-header">
                <h4 class="mb-0">Informasi Website</h4>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-borderless mb-0 pengaturan-table">
                    <tbody>
                        <tr>
                            <th>Versi</th>
                            <td>web.release.ver1.0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TOMBOL LOGOUT DI POJOK KANAN BAWAH --}}
        <div class="text-end mt-5">
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
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