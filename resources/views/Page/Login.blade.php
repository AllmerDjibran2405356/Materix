<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Akun</title>
    <!-- link Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/login.js'],)

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="main-container">
        <div class="c_login-frame">
            <div class="c_login-left"></div>
            <div class="c_login-right">
                <h1>Selamat Datang!</h1>
                {{-- Form yang terhubung ke 'AuthController' --}}
                <form action="{{ route('login.submit') }}" method="POST">
                    @csrf

                    <!-- input username -->
                    <div class="form-group">
                        <label for="username">Nama Pengguna</label>
                        <input type="text" id="username" name="username" required placeholder="Masukkan nama Pengguna">
                    </div>

                    <!-- input email -->
                    <div class="form-group">
                        <label for="credential" class="form-label">Alamat Surel (Email)</label>
                        <input type="email" class="form-control" id="credential" name="credential" value="{{ old('credential') }}" required placeholder="Masukkan Alamat Surel">
                        @error('credential')
                            <div class="form-text-error d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- input password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan Kata Sandi">
                        @error('password')
                            <div class="form-text-error d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tombol Submit --}}
                    <!-- <button type="submit" class="btn btn-dark w-100 py-2 mt-3"> -->
                    <button type="submit" class="btn-login">
                    Masuk
                    </button>
                
                    {{-- Link ke Halaman Registrasi --}}
                    <div class="text-center mt-3">
                        <a href="{{ route('daftar.form') }}">Belum punya akun? Daftar di sini</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
