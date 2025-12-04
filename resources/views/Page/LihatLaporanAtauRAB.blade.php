<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAB - {{ $project->Nama_Desain }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .summary-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .summary-card:hover {
            transform: translateY(-5px);
        }
        .summary-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        .btn-export {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .total-box {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .material-badge {
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        .status-complete {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        .supplier-contact {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .price-comparison th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card header-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="card-title mb-1">
                                    <i class="fas fa-file-invoice-dollar me-2"></i>Rencana Anggaran Biaya (RAB)
                                </h1>
                                <h4 class="card-subtitle mb-3">{{ $project->Nama_Desain }}</h4>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ \Carbon\Carbon::parse($project->Tanggal_Dibuat)->translatedFormat('d F Y') }}
                                    </span>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-file me-1"></i>
                                        {{ $project->Nama_File }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="total-box">
                                    <h5 class="mb-1">Total RAB</h5>
                                    <h2 class="mb-0 fw-bold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h6 class="card-subtitle mb-2 text-muted">Total Item Bahan</h6>
                                <h3 class="card-title mb-0">{{ $totalItems }}</h3>
                                <p class="card-text small">Jenis bahan yang dibutuhkan</p>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-boxes summary-icon text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h6 class="card-subtitle mb-2 text-muted">Supplier Terlibat</h6>
                                <h3 class="card-title mb-0">{{ $uniqueSuppliers }}</h3>
                                <p class="card-text small">Jumlah supplier penyedia</p>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-truck summary-icon text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h6 class="card-subtitle mb-2 text-muted">Status RAB</h6>
                                @if($totalItems > 0)
                                    <span class="status-badge status-complete">LENGKAP</span>
                                @else
                                    <span class="status-badge status-pending">BELUM ADA DATA</span>
                                @endif
                                <p class="card-text small mt-2">Perhitungan selesai</p>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-check-circle summary-icon text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-export" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </button>
                    <button class="btn btn-export" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </button>
                    <button class="btn btn-secondary" onclick="refreshData()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data...</p>
        </div>

        <!-- Data Table -->
        <div class="table-container">
            @if($message)
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h4>Belum Ada Data RAB</h4>
                    <p>{{ $message }}</p>
                    <p class="text-muted">Silakan hitung kebutuhan bahan terlebih dahulu untuk melihat RAB.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table id="rabTable" class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Nama Bahan</th>
                                <th width="15%">Kategori</th>
                                <th width="10%">Volume</th>
                                <th width="10%">Satuan</th>
                                <th width="15%">Harga Satuan</th>
                                <th width="15%">Total Harga</th>
                                <th width="10%">Supplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recaps as $index => $recap)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $recap->bahan->Nama_Bahan ?? 'Unknown' }}</strong>
                                </td>
                                <td>
                                    @if($recap->bahan && $recap->bahan->kategori)
                                        <span class="material-badge">
                                            {{ $recap->bahan->kategori->Nama_Kelompok_Bahan }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($recap->Volume_Final, 2) }}</td>
                                <td>{{ $recap->Satuan_Saat_Ini ?? 'Unit' }}</td>
                                <td class="text-end">
                                    Rp {{ number_format($recap->Harga_Satuan_Saat_Ini, 0, ',', '.') }}
                                </td>
                                <td class="text-end fw-bold">
                                    Rp {{ number_format($recap->Total_Harga, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if($recap->supplier)
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                    type="button"
                                                    data-bs-toggle="dropdown">
                                                {{ $recap->supplier->Nama_Supplier }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <span class="dropdown-item-text">
                                                        <i class="fas fa-phone me-2"></i>
                                                        {{-- Perbaikan di sini --}}
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
                                                        {{ $kontakSupplier }}
                                                    </span>
                                                </li>
                                                <li>
                                                    <span class="dropdown-item-text">
                                                        <i class="fas fa-map-marker-alt me-2"></i>
                                                        {{-- Perbaikan di sini --}}
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
                                                        {{ $alamatSupplier }}
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="6" class="text-end fw-bold">GRAND TOTAL</td>
                                <td class="text-end fw-bold text-success" colspan="2">
                                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

        <!-- Price Comparison (Harga Bahan dari Berbagai Supplier) -->
        @if($groupedPrices->count() > 0 && !$message)
        <div class="table-container mt-4">
            <h5 class="mb-3">
                <i class="fas fa-balance-scale me-2 text-warning"></i>
                Perbandingan Harga Bahan dari Berbagai Supplier
            </h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered price-comparison">
                    <thead>
                        <tr class="bg-light">
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
                            <td class="fw-bold">{{ $bahan->Nama_Bahan }}</td>
                            @foreach($suppliers as $supplier)
                            <td class="text-end">
                                @php
                                    $harga = $hargaList->where('ID_Supplier', $supplier->ID_Supplier)->first();
                                @endphp
                                @if($harga)
                                    Rp {{ number_format($harga->Harga_Per_Satuan, 0, ',', '.') }}
                                    <small class="text-muted d-block">
                                        {{ $harga->satuan->Nama_Satuan ?? '-' }}
                                    </small>
                                    @if($harga->Tanggal_Update_Data)
                                    <small class="text-muted d-block">
                                        {{ \Carbon\Carbon::parse($harga->Tanggal_Update_Data)->format('d/m/Y') }}
                                    </small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Inisialisasi DataTable
        $(document).ready(function() {
            if ($('#rabTable').length) {
                $('#rabTable').DataTable({
                    "pageLength": 10,
                    "order": [[0, 'asc']],
                    "language": {
                        "lengthMenu": "Tampilkan _MENU_ data per halaman",
                        "zeroRecords": "Tidak ada data yang ditemukan",
                        "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                        "infoEmpty": "Tidak ada data tersedia",
                        "infoFiltered": "(disaring dari _MAX_ total data)",
                        "search": "Cari:",
                        "paginate": {
                            "first": "Pertama",
                            "last": "Terakhir",
                            "next": "Berikutnya",
                            "previous": "Sebelumnya"
                        }
                    }
                });
            }
        });

        // Function untuk export Excel
        function exportToExcel() {
            window.location.href = "{{ route('rab.export-excel', $project->ID_Desain_Rumah) }}";
        }

        // Function untuk export PDF
        function exportToPDF() {
            window.location.href = "{{ route('rab.export-pdf', $project->ID_Desain_Rumah) }}";
        }

        // Function untuk refresh data
        function refreshData() {
            showLoading();
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        }

        // Function untuk menampilkan loading
        function showLoading() {
            document.getElementById('loadingSpinner').style.display = 'block';
        }

        // Function untuk menyembunyikan loading
        function hideLoading() {
            document.getElementById('loadingSpinner').style.display = 'none';
        }

        // AJAX untuk mengambil data rekap
        function fetchRecapData() {
            showLoading();

            $.ajax({
                url: "{{ route('rab.get-recap-data', $project->ID_Desain_Rumah) }}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update UI dengan data baru
                        updateTable(response.data);
                        hideLoading();
                    } else {
                        alert('Error: ' + response.message);
                        hideLoading();
                    }
                },
                error: function(xhr, status, error) {
                    alert('Terjadi kesalahan saat mengambil data.');
                    hideLoading();
                }
            });
        }
    </script>
</body>
</html>
