<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAB - {{ $project->Nama_Desain }}</title>
</head>
<body>
    <!-- Tombol Kembali -->
    <div>
        <button onclick="window.location.href='{{ route('detailProyek.show', $project->ID_Desain_Rumah) }}'">
            < Kembali ke Detail Proyek
        </button>
    </div>

    <!-- Header -->
    <div>
        <h1>Rencana Anggaran Biaya (RAB)</h1>
        <h2>{{ $project->Nama_Desain }}</h2>
        <div>
            <span>Tanggal: {{ \Carbon\Carbon::parse($project->Tanggal_Dibuat)->translatedFormat('d F Y') }}</span>
            <span>File: {{ $project->Nama_File }}</span>
        </div>
        <div>
            <h3>Total RAB</h3>
            <h2>Rp {{ number_format($grandTotal, 0, ',', '.') }}</h2>
        </div>
    </div>

    <!-- Ringkasan -->
    <div>
        <div>
            <h4>Total Item Bahan</h4>
            <h3>{{ $totalItems }}</h3>
            <p>Jenis bahan yang dibutuhkan</p>
        </div>
        <div>
            <h4>Supplier Terlibat</h4>
            <h3>{{ $uniqueSuppliers }}</h3>
            <p>Jumlah supplier penyedia</p>
        </div>
        <div>
            <h4>Status RAB</h4>
            @if($totalItems > 0)
                <span>LENGKAP</span>
            @else
                <span>BELUM ADA DATA</span>
            @endif
            <p>Perhitungan selesai</p>
        </div>
    </div>

    <!-- Tombol Export -->
    <div>
        <button onclick="exportToExcel()">Export Excel</button>
        <button onclick="exportToPDF()">Export PDF</button>
        <button onclick="refreshData()">Refresh</button>
    </div>

    <!-- Loading -->
    <div id="loadingSpinner" style="display: none;">
        <p>Memuat data...</p>
    </div>

    <!-- Tabel Utama -->
    <div>
        @if($message)
            <div>
                <h4>Belum Ada Data RAB</h4>
                <p>{{ $message }}</p>
                <p>Silakan hitung kebutuhan bahan terlebih dahulu untuk melihat RAB.</p>
            </div>
        @else
            <table id="rabTable">
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
                        <td>
                            @if($recap->bahan && $recap->bahan->kategori)
                                {{ $recap->bahan->kategori->Nama_Kelompok_Bahan }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ number_format($recap->Volume_Final, 2) }}</td>
                        <td>{{ $recap->Satuan_Saat_Ini ?? 'Unit' }}</td>
                        <td>Rp {{ number_format($recap->Harga_Satuan_Saat_Ini, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($recap->Total_Harga, 0, ',', '.') }}</td>
                        <td>
                            @if($recap->supplier)
                                <div>
                                    <span>{{ $recap->supplier->Nama_Supplier }}</span>
                                    <div>
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
                                        Telepon: {{ $kontakSupplier }}
                                    </div>
                                    <div>
                                        @php
                                            $alamatSupplier = '-';
                                            if ($recap->supplier->alamat) {
                                                if ($recap->supplier->alamat instanceof \Illuminate\Database\Eloquent\Collection) {
                                                    $alamatSupplier = $recap->supplier->alamat->first()->Alamat_Supplier ?? '-';
                                                } else {
                                                    $alamatSupplier = $recap->supplier->alamat->Alamat_Supplier ?? '-';
                                                }
                                            }
                                        @endphp
                                        Alamat: {{ $alamatSupplier }}
                                    </div>
                                </div>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6">GRAND TOTAL</td>
                        <td colspan="2">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

    <!-- Perbandingan Harga -->
    @if($groupedPrices->count() > 0 && !$message)
    <div>
        <h3>Perbandingan Harga Bahan dari Berbagai Supplier</h3>
        <table>
            <thead>
                <tr>
                    <th>Bahan</th>
                    @foreach($suppliers as $supplier)
                    <th>{{ $supplier->Nama_Supplier }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($groupedPrices as $bahanId => $hargaList)
                @php
                    $bahan = $hargaList->first()->bahan ?? null;
                @endphp
                @if($bahan)
                <tr>
                    <td>{{ $bahan->Nama_Bahan }}</td>
                    @foreach($suppliers as $supplier)
                    <td>
                        @php
                            $harga = $hargaList->where('ID_Supplier', $supplier->ID_Supplier)->first();
                        @endphp
                        @if($harga)
                            Rp {{ number_format($harga->Harga_Per_Satuan, 0, ',', '.') }}
                            <div>{{ $harga->satuan->Nama_Satuan ?? '-' }}</div>
                            @if($harga->Tanggal_Update_Data)
                            <div>{{ \Carbon\Carbon::parse($harga->Tanggal_Update_Data)->format('d/m/Y') }}</div>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <script>
        function exportToExcel() {
            window.location.href = "{{ route('rab.export-excel', $project->ID_Desain_Rumah) }}";
        }

        function exportToPDF() {
            window.location.href = "{{ route('rab.export-pdf', $project->ID_Desain_Rumah) }}";
        }

        function refreshData() {
            document.getElementById('loadingSpinner').style.display = 'block';
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        }
    </script>
</body>
</html>
