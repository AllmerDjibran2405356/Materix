<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Materix - @yield('title', 'Selamat Datang')</title>

    {{-- Link CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">

    {{-- NAVBAR --}}
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
                    
                    @auth
                        {{-- User Sudah Login --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('HomePage') }}">Dashboard</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                {{ auth()->user()->username ?? 'Akun Saya' }} 
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                {{-- HANYA menu Pengaturan, TANPA logout --}}
                                <li><a class="dropdown-item" href="{{ route('pengaturan') }}">
                                    <i class="bi bi-gear me-2"></i>Pengaturan Akun
                                </a></li>
                                {{-- HAPUS bagian logout dari sini --}}
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

    {{-- KONTEN UTAMA --}}
    <main>
        @yield('content')
    </main>

    {{-- SCRIPT --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Include SEMUA modal (pengaturan + logout) untuk user yang login --}}
    @if(auth()->check())
        @include('partials.pengaturanpartials')
    @endif

    @stack('scripts')
</body>
</html>