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
    <link rel="stylesheet" href="/css/navbar.css">
    <link href="{{ asset('css/homepage.css') }}" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/unggah.js'])
</head>

<body class="bg-light">
    {{-- NAVBAR --}}
    <nav class="navbar navbar-expand-lg custom-navbar shadow-sm **fixed-top**">
        <div class="container">
            {{-- ▼▼▼ LOGO MATERIX MENGARAH KE HOMEPAGE ▼▼▼ --}}
            <a class="navbar-brand" href="{{ route('HomePage') }}">
                <img src="/images/materixlogos.png" alt="MateRix Logo" class="navbar-logo">
                <span class="logo-text">aterix</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    
                    @auth
                        {{-- User Sudah Login --}}
                        
                        <li class="nav-item dropdown">
                            {{-- NAVBAR DROPDOWN DENGAN FOTO --}}
                            <a class="nav-link dropdown-toggle p-0" 
                                href="#" 
                                id="navbarDropdown" 
                                role="button" 
                                data-bs-toggle="dropdown">
                                <img src="{{ auth()->user()->getAvatarUrl() }}" 
                                     alt="Profile" 
                                     class="rounded-circle"
                                     width="40" 
                                     height="40"
                                     style="object-fit: cover; border: 2px solid rgba(255,255,255,0.3);">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <div class="dropdown-header text-center">
                                        <img src="{{ auth()->user()->getAvatarUrl() }}" 
                                             alt="Profile" 
                                             class="rounded-circle mb-2"
                                             width="60" 
                                             height="60"
                                             style="object-fit: cover;">
                                        <h6 class="mb-0">{{ auth()->user()->username }}</h6>
                                        <small class="text-muted">{{ auth()->user()->email }}</small>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('pengaturan') }}">
                                        <i class="bi bi-gear me-2"></i>Pengaturan Akun
                                    </a>
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

    {{-- KONTEN UTAMA --}}
    <main>
        @yield('content')
    </main>

    {{-- SCRIPT --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Include SEMUA modal --}}
    @if(auth()->check())
        @include('partials.pengaturanpartials')
    @endif

    {{-- SCRIPT PREVIEW AVATAR --}}
    <script>
        // Preview avatar sebelum upload
        document.getElementById('avatarInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

    @stack('scripts')

   <script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');

    // Preview image saja
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Validasi file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File terlalu besar! Maksimal 2MB.');
                    avatarInput.value = '';
                    return;
                }

                // Validasi file type
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Format file tidak didukung!');
                    avatarInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
</body>
</html>