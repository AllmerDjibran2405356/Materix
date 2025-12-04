<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendataan Bahan & Produsen</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .pilih-supplier-btn {
            min-width: 160px;
            white-space: nowrap;
        }
        .supplier-name-text {
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            max-width: 120px;
            vertical-align: middle;
        }
    </style>
</head>

<body class="bg-light">

<div class="container mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Pendataan Bahan & Produsen</h3>
        <div>
            <a href="#" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalSupplier">
                <i class="bi bi-truck"></i> Data Supplier
            </a>
            <a href="#" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalBahan">
                <i class="bi bi-box-seam"></i> Data Bahan
            </a>
        </div>
    </div>

    <!-- Tabel Utama -->
    <section>
        @if ($recaps->isEmpty())
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle"></i>
                Belum ada data rekapitulasi.
            </div>
        @else
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Desain: <strong>{{ $recaps->first()->desainRumah->Nama_Desain ?? 'ID: '.$recaps->first()->ID_Desain_Rumah }}</strong></h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Bahan</th>
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
                                    <td>{{ $recap->bahan->Nama_Bahan ?? 'ID: '.$recap->ID_Bahan }}</td>
                                    <td class="text-end">{{ number_format($recap->Volume_Final, 2, ',', '.') }}</td>
                                    <td class="text-center">{{ $recap->Satuan_Saat_Ini }}</td>
                                    <td class="text-end">
                                        @if($recap->Harga_Satuan_Saat_Ini)
                                            Rp {{ number_format($recap->Harga_Satuan_Saat_Ini, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        @if($recap->Total_Harga)
                                            Rp {{ number_format($recap->Total_Harga, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('rekap.updateSupplier') }}" method="POST" class="d-flex">
                                            @csrf
                                            <input type="hidden" name="ID_Rekap" value="{{ $recap->ID_Rekap }}">
                                            <select name="ID_Supplier" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="">Pilih Supplier</option>
                                                @foreach ($suppliers as $sup)
                                                    <option value="{{ $sup->ID_Supplier }}"
                                                        {{ $recap->ID_Supplier == $sup->ID_Supplier ? 'selected' : '' }}>
                                                        {{ $sup->Nama_Supplier }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="5" class="text-end">Grand Total</td>
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

<!-- ================= MODALS ================= -->

<!-- Modal Supplier -->
<div class="modal fade" id="modalSupplier" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Tombol Tambah Supplier -->
                <button class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalTambahSupplier">
                    <i class="bi bi-plus"></i> Tambah Supplier
                </button>

                <!-- Tabel Supplier -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Supplier</th>
                                <th>Nama Supplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suppliers as $index => $supplier)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $supplier->ID_Supplier }}</td>
                                <td>{{ $supplier->Nama_Supplier }}</td>
                            </tr>
                            @endforeach
                            @if($suppliers->isEmpty())
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada data supplier</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
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
                <form action="{{ route('supplier.tambah') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Supplier</label>
                        <input type="text" name="Nama_Supplier" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat Supplier</label>
                        <input type="text" name="Alamat_Supplier" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kontak Supplier</label>
                        <input type="text" name="Kontak_Supplier" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bahan -->
<div class="modal fade" id="modalBahan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Harga Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Tombol Tambah Harga -->
                <button class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalTambahHarga">
                    <i class="bi bi-plus"></i> Tambah Harga
                </button>

                <!-- Tabel Harga Bahan - SANGAT SEDERHANA -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Bahan</th>
                                <th>Supplier</th>
                                <th class="text-end">Harga (Rp)</th>
                                <th>Terakhir Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $counter = 0;
                            @endphp

                            {{-- Tampilkan langsung dari $materialPrices (data dari database) --}}
                            @if($materialPrices->count() > 0)
                                @foreach ($materialPrices as $index => $harga)
                                    @php
                                        $counter++;
                                        // Cari nama bahan
                                        $namaBahan = 'Unknown';
                                        if (isset($bahanList[$harga->ID_Bahan])) {
                                            $namaBahan = $bahanList[$harga->ID_Bahan];
                                        }

                                        // Cari nama supplier
                                        $namaSupplier = 'Unknown';
                                        foreach ($suppliers as $sup) {
                                            if ($sup->ID_Supplier == $harga->ID_Supplier) {
                                                $namaSupplier = $sup->Nama_Supplier;
                                                break;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $counter }}</td>
                                        <td>{{ $namaBahan }}</td>
                                        <td>{{ $namaSupplier }}</td>
                                        <td class="text-end fw-bold text-success">
                                            Rp {{ number_format($harga->Harga_Per_Satuan, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            @if($harga->Tanggal_Update_Data)
                                                {{ \Carbon\Carbon::parse($harga->Tanggal_Update_Data)->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        <i class="bi bi-inbox"></i> Belum ada data harga bahan
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Harga - HANYA BAGIAN INPUT HARGA YANG DIPERBAIKI -->
<div class="modal fade" id="modalTambahHarga" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Harga Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('bahan.simpanHarga') }}" method="POST" id="formHarga">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Pilih Bahan</label>
                        <select name="ID_Bahan" class="form-select" required>
                            <option value="">-- Pilih Bahan --</option>
                            @if(!empty($bahanList) && is_array($bahanList))
                                @foreach ($bahanList as $idBahan => $namaBahan)
                                    <option value="{{ $idBahan }}">{{ $namaBahan }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Supplier</label>
                        <select name="ID_Supplier" class="form-select" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->ID_Supplier }}">{{ $sup->Nama_Supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga per Satuan (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <!-- HAPUS validasi HTML5 yang ketat -->
                            <input type="number"
                                   name="Harga_Per_Satuan"
                                   id="inputHarga"
                                   class="form-control"
                                   min="1"
                                   required
                                   placeholder="Contoh: 10000, 15000, 20000">
                        </div>
                        <div class="form-text">
                            Masukkan harga dalam angka bulat (tanpa titik/koma)
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanHarga">
                            <i class="bi bi-save"></i> Simpan Harga
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Notifikasi Toast -->
@if(session('success') || session('error'))
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
    @if(session('success'))
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto"><i class="bi bi-check-circle"></i> Sukses</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white">
            <strong class="me-auto"><i class="bi bi-exclamation-triangle"></i> Error</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            {{ session('error') }}
        </div>
    </div>
    @endif
</div>
@endif


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script tambahan untuk handle form submission -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formHarga = document.getElementById('formHarga');
    const inputHarga = document.getElementById('inputHarga');

    if (formHarga) {
        // Hapus validasi HTML5 yang terlalu ketat
        formHarga.setAttribute('novalidate', 'novalidate');

        formHarga.addEventListener('submit', function(e) {
            // Validasi custom
            const harga = inputHarga.value.trim();

            if (!harga || isNaN(harga) || parseInt(harga) < 1) {
                e.preventDefault();
                alert('Masukkan harga yang valid (minimal Rp 1)');
                inputHarga.focus();
                return false;
            }

            // Konversi ke integer sebelum submit
            inputHarga.value = parseInt(harga);

            return true;
        });
    }

    // Auto-hide toast setelah 5 detik
    setTimeout(() => {
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(toast => {
            const bsToast = new bootstrap.Toast(toast);
            bsToast.hide();
        });
    }, 5000);
});
</script>

</body>
</html>
