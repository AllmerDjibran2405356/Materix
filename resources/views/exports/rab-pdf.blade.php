<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RAB - {{ $project->Nama_Desain }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 20px;
        }
        .header h2 {
            color: #666;
            margin: 5px 0;
            font-size: 16px;
        }
        .project-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        th {
            background-color: #2c3e50;
            color: white;
            padding: 6px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .total-row {
            background-color: #2ecc71 !important;
            color: white;
            font-weight: bold;
        }
        .summary-box {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding: 10px;
            background-color: #ecf0f1;
            border-radius: 5px;
            font-size: 9px;
        }
        .summary-item {
            text-align: center;
            flex: 1;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            color: #7f8c8d;
            font-size: 8px;
        }
        .supplier-info {
            font-size: 8px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>RENCANA ANGGARAN BIAYA (RAB)</h1>
        <h2>{{ $project->Nama_Desain }}</h2>
        <p>Tanggal Cetak: {{ $exportDate }}</p>
    </div>

    <!-- Project Info -->
    <div class="project-info">
        <strong>Informasi Proyek:</strong><br>
        Nama Desain: {{ $project->Nama_Desain }}<br>
        File: {{ $project->Nama_File }}<br>
        Tanggal Dibuat: {{ \Carbon\Carbon::parse($project->Tanggal_Dibuat)->format('d/m/Y') }}
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Bahan</th>
                <th>Kategori</th>
                <th>Volume</th>
                <th>Satuan</th>
                <th>Harga Satuan</th>
                <th>Total Harga</th>
                <th>Supplier</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recaps as $index => $recap)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $recap->bahan->Nama_Bahan ?? 'Unknown' }}</td>
                <td>{{ $recap->bahan->kategori->Nama_Kelompok_Bahan ?? '-' }}</td>
                <td>{{ number_format($recap->Volume_Final, 2) }}</td>
                <td>{{ $recap->Satuan_Saat_Ini ?? 'Unit' }}</td>
                <td>Rp {{ number_format($recap->Harga_Satuan_Saat_Ini, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($recap->Total_Harga, 0, ',', '.') }}</td>
                <td>
                    {{ $recap->supplier->Nama_Supplier ?? '-' }}
                    @if($recap->supplier && $recap->supplier->kontak)
                        @php
                            $kontakSupplier = '-';
                            if ($recap->supplier->kontak) {
                                if ($recap->supplier->kontak instanceof \Illuminate\Database\Eloquent\Collection) {
                                    $kontakSupplier = $recap->supplier->kontak->first()->Kontak_Supplier ?? '-';
                                } else {
                                    $kontakSupplier = $recap->supplier->kontak->Kontak_Supplier ?? '-';
                                }
                            }
                        @endphp
                        <div class="supplier-info">
                            Telp: {{ $kontakSupplier }}
                        </div>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" style="text-align: right;">GRAND TOTAL</td>
                <td colspan="2">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Summary -->
    <div class="summary-box">
        <div class="summary-item">
            <strong>Total Item Bahan</strong><br>
            {{ $totalItems }}
        </div>
        <div class="summary-item">
            <strong>Supplier Terlibat</strong><br>
            {{ $uniqueSuppliers }}
        </div>
        <div class="summary-item">
            <strong>Total RAB</strong><br>
            Rp {{ number_format($grandTotal, 0, ',', '.') }}
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Dokumen ini dicetak secara otomatis dari Sistem RAB CV Semesta Karya Sejahtera<br>
        © {{ date('Y') }} - Hak Cipta Dilindungi Undang-Undang
    </div>
</body>
</html>
