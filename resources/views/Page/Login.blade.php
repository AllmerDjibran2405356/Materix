<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Akun</title>

    {{-- Hanya link Bootstrap, persis seperti file Daftar.blade.php kamu --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Kita tetap butuh app.css HANYA untuk .form-text-error --}}
    @vite(['resources/css/app.css'])
</head>
<body>

{{-- Kita pakai container Bootstrap standar --}}
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="col-md-6 col-lg-5">

        <h2 class="text-center mb-4">Masuk Akun</h2>

        {{-- "Penangkap" Pesan Sukses (dari Halaman Registrasi) --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- "Penangkap" Pesan Error (jika login gagal) --}}
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- Form yang terhubung ke 'AuthController' --}}
        <form action="{{ route('login.submit') }}" method="POST">
            @csrf

            {{-- Input untuk EMAIL (sesuai logika Controller) --}}
            <div class="form-group mb-3">
                <label for="credential" class="form-label">Alamat Surel (Email)</label>
                <input type="email" class="form-control" id="credential" name="credential" value="{{ old('credential') }}" required>
                @error('credential')
                    <div class="form-text-error d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Input untuk PASSWORD --}}
            <div class="form-group mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" id="password" name="password" required>
                 @error('password')
                    <div class="form-text-error d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tombol Submit --}}
            <button type="submit" class="btn btn-dark w-100 py-2 mt-3">
                Masuk
            </button>
        
            {{-- Link ke Halaman Registrasi --}}
            <div class="text-center mt-3">
                <a href="{{ route('daftar.form') }}">Belum punya akun? Daftar di sini</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>