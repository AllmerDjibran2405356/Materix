<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Header</title>

  <link rel="stylesheet" href="{{ asset('/css/navbar.css') }}">
  {{-- Tambahkan Bootstrap CSS --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
</head>
<body>
  <nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="{{ route('landing') }}">
          <img src="{{ asset('/images/materixlogos.png') }}" alt="Logo" class="navbar-logo">
          <span class="logo-text">aterix</span>
      </a>

  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <div class="ms-auto d-flex align-items-center gap-2">
          {{-- Tombol Masuk dengan style putih --}}
          <a class="btn btn-masuk-custom" href="{{ route('login.form') }}">
            Masuk
          </a>
          
          {{-- Tombol Daftar dengan style outline putih --}}
          <a class="btn btn-daftar-custom" href="{{ route('daftar.form') }}">
            Daftar
          </a>
        </div>
      </div>
    </div>
  </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
