<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendataan Bahan & Produsen</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap icons (opsional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Pendataan Bahan & Produsen</h3>

        <div>
            <!-- Tombol Popup Supplier -->
            <a href="#" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalSupplier">
                <i class="bi bi-truck"></i> Data Supplier
            </a>

            <!-- Tombol Popup Bahan -->
            <a href="#" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalBahan">
                <i class="bi bi-box-seam"></i> Data Bahan
            </a>
        </div>
    </div>

    <!-- Tabel Data -->
    <section>
        @if ($recaps->isEmpty())
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle"></i>
                Belum ada data rekapitulasi. Silakan lakukan perhitungan RAB terlebih dahulu.
            </div>
        @else
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        Desain: <strong>{{ $recaps->first()->desainRumah->Nama_Desain ?? 'ID: '.$recaps->first()->ID_Desain_Rumah }}</strong>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0">
                            <thead class="table-dark align-middle">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Hitung</th>
                                    <th>Nama Bahan</th>
                                    <th class="text-end">Vol. Teoritis</th>
                                    <th class="text-end">Vol. Final</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Total Harga</th>
                                    <th>Supplier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recaps as $index => $recap)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>

                                        {{-- Tanggal --}}
                                        <td>
                                            {{ \Carbon\Carbon::parse($recap->Tanggal_Hitung)->format('d M Y') }}
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($recap->Tanggal_Hitung)->format('H:i') }}
                                            </small>
                                        </td>

                                        {{-- Nama Bahan --}}
                                        <td>{{ $recap->bahan->Nama_Bahan ?? 'ID: '.$recap->ID_Bahan }}</td>

                                        {{-- Volume --}}
                                        <td class="text-end">{{ number_format($recap->Volume_Teoritis, 2, ',', '.') }}</td>
                                        <td class="text-end fw-bold">{{ number_format($recap->Volume_Final, 2, ',', '.') }}</td>

                                        <td class="text-center">{{ $recap->Satuan_Saat_Ini }}</td>

                                        {{-- Harga --}}
                                        <td class="text-end">Rp {{ number_format($recap->Harga_Satuan_Saat_Ini, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold text-success">Rp {{ number_format($recap->Total_Harga, 0, ',', '.') }}</td>

                                        {{-- Supplier --}}
                                        <td>
                                            {{ $recap->supplier->Nama_Supplier ?? 'ID: ' . $recap->ID_Supplier }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="7" class="text-end text-uppercase">Grand Total Estimasi</td>
                                    <td class="text-end text-primary">
                                        Rp {{ number_format($recaps->sum('Total_Harga'), 0, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>

<!-- ========================================================= -->
<!-- ======================= MODALS =========================== -->
<!-- ========================================================= -->

<!-- Modal Supplier -->
<div class="modal fade" id="modalSupplier" tabindex="-1" aria-labelledby="modalSupplierLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Data Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <button class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalTambahSupplier">
                    Tambah Supplier
                </button>

                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Supplier</th>
                            <th>Nama Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($suppliers as $supplier)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $supplier->ID_Supplier }}</td>
                                <td>{{ $supplier->Nama_Supplier }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>

<!-- Modal Tambah Supplier -->
<div class="modal fade" id="modalTambahSupplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Tambah Supplier Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Nama Supplier</label>
                        <input type="text" class="form-control" placeholder="Masukkan nama supplier">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <input type="text" class="form-control" placeholder="Alamat supplier">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" class="form-control" placeholder="Nomor telepon">
                    </div>

                    <button type="button" class="btn btn-primary w-100">Simpan</button>
                </form>
            </div>

        </div>
    </div>
</div>


<!-- Modal Bahan -->
<div class="modal fade" id="modalBahan" tabindex="-1" aria-labelledby="modalBahanLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Data Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Simpan</button>
            </div>

            <div class="modal-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Bahan</th>
                            <th>ID Supplier</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materialPrices as $materialPrice)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $materialPrice->ID_Bahan }}</td>
                                <td>{{ $materialPrice->ID_Supplier }}</td>
                                <td>{{ $materialPrice->Harga_Per_Satuan }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const modalSupplier = new bootstrap.Modal(document.getElementById('modalSupplier'));
        const modalTambah = new bootstrap.Modal(document.getElementById('modalTambahSupplier'));

        // Ketika modal tambah supplier ditutup → kembali ke modal supplier
        document.getElementById('modalTambahSupplier').addEventListener('hidden.bs.modal', function () {
            modalSupplier.show();
        });

        // Saat tombol Simpan ditekan → tutup modal tambah → kembali otomatis
        document.getElementById('btnSimpanSupplier').addEventListener('click', function () {
            modalTambah.hide(); // setelah hide, event hidden.bs.modal akan membuka modalSupplier
        });

    });
</script>

</body>
</html>
