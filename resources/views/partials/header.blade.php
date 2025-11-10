<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Header</title>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">
      <img src="{{ asset('images/Materix.png') }}" alt="Logo" width="60" height="50" class="d-inline-block align-text-top">
      ateRix
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <div class="ms-auto d-flex align-items-center">
        <a class="btn btn-masuk" href="/masuk">Masuk</a>
        <a class="btn btn-daftar me-3" href="/daftar">Daftar</a>
      </div>
    </div>
  </div>
</nav>
</body>
</html>
