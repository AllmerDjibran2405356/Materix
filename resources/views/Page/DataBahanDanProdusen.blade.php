@extends('layouts.app')

@section('title', 'Pendataan Bahan & Produsen')

@section('styles')
    <link href="{{ asset('css/data-bahan-produsen.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="body">
    <div class="main-container">
        <h1>Pendataan Bahan & Produsen</h1>
        {{-- Ubah 'mb-2' menjadi 'mb-1' (jarak sangat kecil) atau 'mb-0' (tanpa jarak) --}}
        <div class="header-wrapper d-flex justify-content-between align-items-end mb-1">

            <div class="container-back">
                @if(isset($project))
                    <a href="{{ route('detailProyek.show', $project->ID_Desain_Rumah) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Detail Proyek
                    </a>
                @else
                    <a href="#" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                @endif
            </div>

            <div class="container-btn">
                <a href="#" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalSupplier">
                    <i class="bi bi-people-fill"></i> Tambah Data Supplier
                </a>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBahan">
                    <i class="bi bi-box-seam"></i> Tambah Data Bahan
                </a>
            </div>
        </div>

        <section>
            @if ($recaps->isEmpty())
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i> Belum ada data rekapitulasi.
                </div>
            @else
                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white" style="background-color: #0A2568;">
                        <h5 class="mb-0">
                            Desain: <strong>{{ $recaps->first()->desainRumah->Nama_Desain ?? 'ID: '.$recaps->first()->ID_Desain_Rumah }}</strong>
                        </h5>
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
                                @include('partials.main-table')
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    </div>

    <div class="modal fade" id="modalSupplier" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Data Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <button type="button" class="btn btn-primary btn-sm mb-3" id="btnBukaModalTambahSupplier">
                        <i class="bi bi-plus"></i> Tambah Supplier
                    </button>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Supplier</th>
                                    <th>Alamat</th>
                                    <th>Kontak</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="bodyTabelSupplier">
                                @foreach ($suppliers as $index => $supplier)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-bold">{{ $supplier->Nama_Supplier }}</td>
                                    <td>
                                        @if($supplier->alamat && $supplier->alamat->count() > 0)
                                            <div class="mb-2">
                                                @foreach($supplier->alamat as $alamat)
                                                    <div class="d-flex justify-content-between align-items-center mb-1 bg-light p-1 rounded">
                                                        <span class="small"><i class="bi bi-geo-alt"></i> {{ $alamat->Alamat_Supplier }}</span>
                                                        <button class="btn btn-danger btn-sm p-0 px-1 btn-hapus-alamat"
                                                                data-url="{{ route('supplier.hapusAlamat', ['id_supplier' => $supplier->ID_Supplier, 'alamat' => $alamat->Alamat_Supplier]) }}">
                                                            <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted small fst-italic mb-1">Kosong</div>
                                        @endif

                                        <button class="btn btn-outline-primary btn-sm py-0 btn-buka-tambah-alamat"
                                                style="font-size: 0.75rem;"
                                                data-id="{{ $supplier->ID_Supplier }}">
                                            + Alamat
                                        </button>
                                    </td>
                                    <td>
                                        @if($supplier->kontak && $supplier->kontak->count() > 0)
                                            <div class="mb-2">
                                                @foreach($supplier->kontak as $kontak)
                                                    <div class="d-flex justify-content-between align-items-center mb-1 bg-light p-1 rounded">
                                                        <span class="small"><i class="bi bi-telephone"></i> {{ $kontak->Kontak_Supplier }}</span>
                                                        <button class="btn btn-danger btn-sm p-0 px-1 btn-hapus-kontak"
                                                                data-url="{{ route('supplier.hapusKontak', ['id_supplier' => $supplier->ID_Supplier, 'kontak' => $kontak->Kontak_Supplier]) }}">
                                                            <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted small fst-italic mb-1">Kosong</div>
                                        @endif

                                        <button class="btn btn-outline-success btn-sm py-0 btn-buka-tambah-kontak"
                                                style="font-size: 0.75rem;"
                                                data-id="{{ $supplier->ID_Supplier }}">
                                            + Kontak
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-warning btn-sm btn-buka-edit-supplier"
                                                data-id="{{ $supplier->ID_Supplier }}"
                                                data-nama="{{ $supplier->Nama_Supplier }}"
                                                data-url-update="{{ route('supplier.update', ':id') }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <button type="button" class="btn btn-danger btn-sm btn-hapus-supplier ms-1"
                                                data-id="{{ $supplier->ID_Supplier }}"
                                                data-url="{{ route('supplier.hapus', $supplier->ID_Supplier) }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
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

    <div class="modal fade" id="modalTambahSupplier" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('supplier.tambah') }}" method="POST" id="formTambahSupplier">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Supplier Baru</h5>
                        <button type="button" class="btn-close btn-kembali-ke-list"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Supplier</label>
                            <input type="text" name="Nama_Supplier" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Awal</label>
                            <input type="text" name="Alamat_Supplier" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kontak Awal</label>
                            <input type="text" name="Kontak_Supplier" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-kembali-ke-list">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahAlamat" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('supplier.tambahAlamat') }}" method="POST" id="formTambahAlamat">
                    @csrf
                    <input type="hidden" name="ID_Supplier" id="inputIdSupplierAlamat">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Alamat</h5>
                        <button type="button" class="btn-close btn-kembali-ke-list"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Alamat Baru</label>
                            <textarea name="Alamat_Supplier" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-kembali-ke-list">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahKontak" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('supplier.tambahKontak') }}" method="POST" id="formTambahKontak">
                    @csrf
                    <input type="hidden" name="ID_Supplier" id="inputIdSupplierKontak">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Kontak</h5>
                        <button type="button" class="btn-close btn-kembali-ke-list"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kontak Baru</label>
                            <input type="text" name="Kontak_Supplier" class="form-control" required placeholder="Telp / Email">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-kembali-ke-list">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditSupplier" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST" id="formEditSupplier">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editSupplierId" name="supplier_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Supplier</h5>
                        <button type="button" class="btn-close btn-kembali-ke-list"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Supplier</label>
                            <input type="text" name="Nama_Supplier" id="editNamaSupplier" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-kembali-ke-list">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBahan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Data Harga Bahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-hover align-middle" id="tabelHargaBahan">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="25%">Nama Bahan</th>
                                    <th width="30%">Pilih Supplier</th>
                                    <th width="15%">Update Terakhir</th>
                                    <th width="20%">Harga (Rp)</th>
                                    <th width="5%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $counter = 1;
                                @endphp

                                {{-- Loop berdasarkan Master Bahan agar SEMUA bahan muncul --}}
                                @foreach ($bahanList as $idBahan => $namaBahan)
                                    @php
                                        // 1. Ambil semua data harga untuk bahan ini saja
                                        $pricesForMaterial = $materialPrices->where('ID_Bahan', $idBahan);

                                        // 2. Jadikan Key-Value array supaya mudah dicari berdasarkan ID Supplier
                                        // Format: [ID_Supplier => ObjectHarga]
                                        $priceMap = $pricesForMaterial->keyBy('ID_Supplier');

                                        // 3. Tentukan data awal (Default ke supplier pertama yang punya harga, atau null)
                                        $defaultData = $pricesForMaterial->first();

                                        // 4. Siapkan nilai awal untuk Input & Tanggal
                                        $currentHarga = $defaultData ? $defaultData->Harga_Per_Satuan : '';
                                        $currentTgl = $defaultData && $defaultData->Tanggal_Update_Data
                                            ? \Carbon\Carbon::parse($defaultData->Tanggal_Update_Data)->format('d/m/Y H:i')
                                            : '-';
                                        $currentIdHarga = $defaultData ? $defaultData->ID_Harga : '';
                                        $currentSupplierId = $defaultData ? $defaultData->ID_Supplier : '';
                                    @endphp

                                    <tr class="row-bahan" data-bahan-id="{{ $idBahan }}">
                                        <td>{{ $counter++ }}</td>
                                        <td class="fw-bold">{{ $namaBahan }}</td>
                                        <td>
                                            <select class="form-select form-select-sm supplier-select">
                                                {{-- Opsi Placeholder --}}
                                                @if(!$defaultData)
                                                    <option value="" selected disabled>-- Pilih Supplier --</option>
                                                @endif

                                                @foreach ($suppliers as $sup)
                                                    @php
                                                        $hasPrice = $priceMap->has($sup->ID_Supplier);
                                                        $data = $hasPrice ? $priceMap->get($sup->ID_Supplier) : null;

                                                        // PERBAIKAN: Gunakan (int) agar angka menjadi bulat (20000) bukan (20000.00)
                                                        // Ini mencegah bug saat JavaScript membaca data
                                                        $valHarga = $hasPrice ? (int)$data->Harga_Per_Satuan : '';

                                                        $valIdHarga = $hasPrice ? $data->ID_Harga : '';
                                                        $valTgl = $hasPrice && $data->Tanggal_Update_Data
                                                            ? \Carbon\Carbon::parse($data->Tanggal_Update_Data)->format('d/m/Y H:i')
                                                            : '-';

                                                        $isSelected = ($currentSupplierId == $sup->ID_Supplier);
                                                    @endphp

                                                    <option value="{{ $sup->ID_Supplier }}"
                                                            data-harga="{{ $valHarga }}"
                                                            data-id-harga="{{ $valIdHarga }}"
                                                            data-tgl="{{ $valTgl }}"
                                                            {{ $isSelected ? 'selected' : '' }}>
                                                        {{ $sup->Nama_Supplier }} {{ $hasPrice ? '' : '(Belum ada)' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="text-muted small cell-tgl">{{ $currentTgl }}</td>

                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Rp</span>

                                                {{-- PENTING: Gunakan type="text", BUKAN "number" --}}
                                                <input type="text"
                                                    class="form-control harga-input text-end"
                                                    value="{{ $defaultData ? number_format($defaultData->Harga_Per_Satuan, 0, ',', '.') : '' }}"
                                                    placeholder="{{ $defaultData ? '' : 'Kosong' }}">
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <button class="btn btn-success btn-sm btn-simpan-harga" disabled
                                                    data-url="{{ route('bahan.updateHargaInline') }}"
                                                    data-harga-id="{{ $currentIdHarga }}"
                                                    data-bahan-id="{{ $idBahan }}"
                                                    title="Simpan Perubahan">
                                                <i class="bi bi-save"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach

                                @if(empty($bahanList))
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-info-circle"></i> Tidak ada bahan yang terdaftar.
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
</div>

<div class="toast-container position-fixed top-0 end-0 p-3"></div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/data-bahan-produsen.js') }}?v={{ time() }}"></script>

@endsection
