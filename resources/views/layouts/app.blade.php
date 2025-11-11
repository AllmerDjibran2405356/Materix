<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materix - @yield('title', 'Selamat Datang')</title>

    {{-- Link CSS (Bootstrap, Icons, Fonts) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- Link CSS dan JS Kustom kita (dari Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-light">

    {{-- =================================== --}}
    {{--         NAVBAR UTAMA ANDA           --}}
    {{-- =================================== --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/" style="font-family: 'Alte Haas Grotesk', sans-serif; font-size: 1.5rem;">
                Materix
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    
                    {{-- Cek apakah user sudah login --}}
                    @auth
                        {{-- User Sudah Login --}}
                        <li class="nav-item">
                            <a class="nav-link" href="#">Dashboard</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                {{-- Menampilkan nama user yang login --}}
                                {{ auth()->user()->username ?? 'Akun Saya' }} 
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('pengaturan') }}">Pengaturan Akun</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    {{-- Tombol Logout harus di dalam form POST --}}
                                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        {{-- User Belum Login --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login.form') }}">Masuk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('daftar.form') }}">Daftar</a>
                        </li>
                    @endauth

                </ul>
            </div>
        </div>
    </nav>

    {{-- =================================== --}}
    {{--       WADAH UNTUK KONTEN            --}}
    {{-- =================================== --}}
    <main>
        @yield('content')
    </main>

    {{-- =================================== --}}
    {{--     SCRIPT JS (DI BAWAH BODY)       --}}
    {{-- =================================== --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Wadah untuk script kustom  --}}
    @stack('scripts')
</body>
</html>