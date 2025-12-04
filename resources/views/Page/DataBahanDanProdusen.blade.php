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

    <!-- Custom CSS -->
    <link href="{{ asset('css/data-bahan-produsen.css') }}" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                        <table class="table table-bordered table-striped mb-0" id="mainTable">
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
                            <!-- Tabel body akan diisi oleh partial -->
                            @include('partials.main-table')
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
    <div class="modal-dialog modal-lg">
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

                <!-- Tabel Supplier dengan Detail Alamat & Kontak -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>ID Supplier</th>
                                <th>Nama Supplier</th>
                                <th>Alamat</th>
                                <th>Kontak</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suppliers as $index => $supplier)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $supplier->ID_Supplier }}</td>
                                <td class="fw-bold">{{ $supplier->Nama_Supplier }}</td>
                                <td>
                                    @if($supplier->alamat && $supplier->alamat->count() > 0)
                                        <div class="mb-2">
                                            @foreach($supplier->alamat as $alamat)
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="badge badge-alamat text-start flex-grow-1 me-2">
                                                        <i class="bi bi-geo-alt"></i> {{ $alamat->Alamat_Supplier }}
                                                    </span>
                                                    <button class="btn btn-danger btn-sm btn-hapus-alamat"
                                                            data-id="{{ $alamat->ID_Alamat ?? $alamat->id }}"
                                                            data-nama="{{ $supplier->Nama_Supplier }}"
                                                            title="Hapus Alamat">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalTambahAlamat"
                                                onclick="setSupplierId({{ $supplier->ID_Supplier }})">
                                            <i class="bi bi-plus"></i> Tambah Alamat
                                        </button>
                                    @else
                                        <span class="text-muted">Belum ada alamat</span>
                                        <br>
                                        <button class="btn btn-outline-primary btn-sm mt-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalTambahAlamat"
                                                onclick="setSupplierId({{ $supplier->ID_Supplier }})">
                                            <i class="bi bi-plus"></i> Tambah Alamat
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->kontak && $supplier->kontak->count() > 0)
                                        <div class="mb-2">
                                            @foreach($supplier->kontak as $kontak)
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="badge badge-kontak text-start flex-grow-1 me-2">
                                                        <i class="bi bi-telephone"></i> {{ $kontak->Kontak_Supplier }}
                                                    </span>
                                                    <button class="btn btn-danger btn-sm btn-hapus-kontak"
                                                            data-id="{{ $kontak->ID_Kontak ?? $kontak->id }}"
                                                            data-nama="{{ $supplier->Nama_Supplier }}"
                                                            title="Hapus Kontak">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button class="btn btn-outline-success btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalTambahKontak"
                                                onclick="setSupplierId({{ $supplier->ID_Supplier }})">
                                            <i class="bi bi-plus"></i> Tambah Kontak
                                        </button>
                                    @else
                                        <span class="text-muted">Belum ada kontak</span>
                                        <br>
                                        <button class="btn btn-outline-success btn-sm mt-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalTambahKontak"
                                                onclick="setSupplierId({{ $supplier->ID_Supplier }})">
                                            <i class="bi bi-plus"></i> Tambah Kontak
                                        </button>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-edit-supplier"
                                            data-id="{{ $supplier->ID_Supplier }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditSupplier"
                                            title="Edit Supplier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            @if($suppliers->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox"></i> Belum ada data supplier
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

<!-- Modal Tambah Alamat Supplier -->
<div class="modal fade" id="modalTambahAlamat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Alamat Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('supplier.tambahAlamat') }}" method="POST" id="formTambahAlamat">
                    @csrf
                    <input type="hidden" name="ID_Supplier" id="inputIdSupplierAlamat">
                    <div class="mb-3">
                        <label class="form-label">Alamat Supplier</label>
                        <textarea name="Alamat_Supplier" class="form-control" rows="3" required
                                  placeholder="Masukkan alamat lengkap supplier"></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Alamat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Kontak Supplier -->
<div class="modal fade" id="modalTambahKontak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kontak Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('supplier.tambahKontak') }}" method="POST" id="formTambahKontak">
                    @csrf
                    <input type="hidden" name="ID_Supplier" id="inputIdSupplierKontak">
                    <div class="mb-3">
                        <label class="form-label">Kontak Supplier</label>
                        <input type="text" name="Kontak_Supplier" class="form-control" required
                               placeholder="Contoh: 081234567890 atau email@supplier.com">
                        <div class="form-text">
                            Bisa berupa nomor telepon, email, atau kontak lainnya
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Kontak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Supplier -->
<div class="modal fade" id="modalEditSupplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="formEditSupplier">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editSupplierId" name="supplier_id">

                    <div class="mb-3">
                        <label class="form-label">Nama Supplier</label>
                        <input type="text" name="Nama_Supplier" id="editNamaSupplier"
                               class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bahan -->
<div class="modal fade" id="modalBahan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
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

                <!-- Tabel Harga Bahan dengan STRUKTUR BARU -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover" id="tabelHargaBahan">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Tanggal Update</th>
                                <th width="25%">Nama Bahan</th>
                                <th width="25%">Supplier</th>
                                <th width="20%">Harga (Rp)</th>
                                <th width="10%" class="text-center">Simpan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $counter = 0;
                            @endphp

                            @if($materialPrices->count() > 0)
                                @foreach ($materialPrices as $index => $harga)
                                    @php
                                        $counter++;
                                        $namaBahan = $harga->bahan->Nama_Bahan ?? 'Unknown';
                                        $namaSupplier = $harga->supplier->Nama_Supplier ?? 'Unknown';
                                    @endphp
                                    <tr data-harga-id="{{ $harga->ID_Harga }}" data-bahan-id="{{ $harga->ID_Bahan }}">
                                        <td>{{ $counter }}</td>
                                        <td>
                                            @if($harga->Tanggal_Update_Data)
                                                {{ \Carbon\Carbon::parse($harga->Tanggal_Update_Data)->format('d/m/Y H:i') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $namaBahan }}</td>
                                        <td>
                                            <select class="form-select form-select-sm supplier-select"
                                                    data-harga-id="{{ $harga->ID_Harga }}"
                                                    data-bahan-id="{{ $harga->ID_Bahan }}"
                                                    data-original-supplier="{{ $harga->ID_Supplier }}">
                                                <option value="">-- Pilih Supplier --</option>
                                                @foreach ($suppliers as $sup)
                                                    <option value="{{ $sup->ID_Supplier }}"
                                                        {{ $harga->ID_Supplier == $sup->ID_Supplier ? 'selected' : '' }}>
                                                        {{ $sup->Nama_Supplier }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number"
                                                       class="form-control harga-input"
                                                       data-harga-id="{{ $harga->ID_Harga }}"
                                                       data-bahan-id="{{ $harga->ID_Bahan }}"
                                                       data-original-harga="{{ $harga->Harga_Per_Satuan }}"
                                                       value="{{ $harga->Harga_Per_Satuan }}"
                                                       min="1"
                                                       placeholder="Kosong">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-success btn-sm btn-simpan-harga"
                                                    data-harga-id="{{ $harga->ID_Harga }}"
                                                    data-bahan-id="{{ $harga->ID_Bahan }}"
                                                    disabled
                                                    title="Simpan Perubahan">
                                                <i class="bi bi-save"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
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

<!-- Modal Tambah Harga -->
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

<!-- Modal Edit Harga (Lama) -->
<div class="modal fade" id="modalEditHarga" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Harga Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="formEditHarga">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editHargaId" name="harga_id">

                    <div class="mb-3">
                        <label class="form-label">Nama Bahan</label>
                        <input type="text" id="editNamaBahan" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <input type="text" id="editNamaSupplier" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Harga per Satuan (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number"
                                   name="Harga_Per_Satuan"
                                   id="editHargaPerSatuan"
                                   class="form-control"
                                   min="1"
                                   required>
                        </div>
                        <div class="form-text">
                            Masukkan harga baru dalam angka bulat
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Harga
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Notifikasi Toast -->
@if(session('success') || session('error'))
<div class="toast-container position-fixed top-0 end-0 p-3">
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

<!-- Custom JavaScript -->
<script src="{{ asset('js/data-bahan-produsen.js') }}"></script>

</body>
</html>
