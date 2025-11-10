<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    </head>
<body>

<div class="register-container">
    <div class="form-wrapper">
    
        <div class="form-section">
            <h2>Buat Akun</h2>

            <form method="POST" action="{{ route('register.submit') }}"> 
                @csrf 
                
                {{-- Nama Depan dan Nama Belakang --}}
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="first_name">Nama Depan</label>
                            <input type="text" id="first_name" name="first_name" class="form-control-custom" placeholder="Masukkan nama depan" value="{{ old('first_name') }}">
                            @error('first_name')
                                <div class="form-text-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="last_name">Nama Belakang</label>
                            <input type="text" id="last_name" name="last_name" class="form-control-custom" placeholder="Masukkan nama belakang" value="{{ old('last_name') }}">
                            @error('last_name')
                                <div class="form-text-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Nama Pengguna dan Surel --}}
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="username">Nama Pengguna</label>
                            <input type="text" id="username" name="username" class="form-control-custom" placeholder="Masukkan Nama Pengguna" value="{{ old('username') }}">
                            @error('username')
                                <div class="form-text-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="email">Surel</label>
                            <input type="email" id="email" name="email" class="form-control-custom" placeholder="Masukkan Surel" value="{{ old('email') }}">
                            @error('email')
                                <div class="form-text-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Kata Sandi --}}
                <div class="form-group mb-2">
                    <label for="password">Kata Sandi</label>
                    <input type="password" id="password" name="password" class="form-control-custom" placeholder="Buat Kata Sandi">
                    @error('password')
                        <div class="form-text-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Konfirmasi Kata Sandi --}}
                <div class="form-group mb-2">
                    <label for="password_confirmation">Konfirmasi Kata Sandi*</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control-custom" placeholder="Konfirmasi Kata Sandi">
                </div>

               {{-- Tombol Buat Akun --}}
                <button type="submit" class="btn-buat-akun">Buat Akun</button>

                <div class="login-prompt">
                    <a href="/login" class="login-link">
                        Sudah punya akun? Masuk
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>