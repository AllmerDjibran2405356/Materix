<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Koneksi Database</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f3f4f6; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 500px; width: 100%; }
        .success { color: #166534; background-color: #dcfce7; padding: 1rem; border-radius: 4px; border: 1px solid #86efac; }
        .error { color: #991b1b; background-color: #fee2e2; padding: 1rem; border-radius: 4px; border: 1px solid #fca5a5; }
        h1 { margin-top: 0; }
        ul { list-style: none; padding: 0; }
        li { margin-bottom: 0.5rem; }
        code { background: #eee; padding: 2px 4px; border-radius: 4px; }
    </style>
</head>
<body>

    <div class="card">
        <h1>Status Koneksi</h1>

        @if($status === 'success')
            <div class="success">
                <h3>✅ {{ $message }}</h3>
                <p>Jumlah data di tabel users: <strong>{{ $userCount }}</strong></p>
            </div>
        @else
            <div class="error">
                <h3>❌ Terjadi Kesalahan</h3>
                <p>{{ $message }}</p>
            </div>
        @endif

        <hr>
        <p><strong>Detail Konfigurasi:</strong></p>
        <ul>
            <li>Host: <code>{{ $config['host'] }}</code></li>
            <li>Database: <code>{{ $config['database'] }}</code></li>
        </ul>
    </div>

</body>
</html>