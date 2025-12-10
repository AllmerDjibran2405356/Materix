@extends('layouts.app')

@section('title', 'Master Data Bahan')

@section('styles')
    <link href="{{ asset('css/data-bahan-produsen.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="body">
    <div class="main-container">
        <h1>Data Bahan & Harga Global</h1>

        {{-- Header & Navigation --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('HomePage')}}" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke Homepage
                </a>
                {{-- TOMBOL BUKA MODAL SUPPLIER --}}
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalSupplier">
                    <i class="bi bi-people-fill"></i> Kelola Data Supplier
                </button>
            </div>

            <form action="{{ route('master-bahan.index') }}" method="GET" class="d-flex" style="width: 400px;">
                <input type="text" name="q" class="form-control me-2" placeholder="Cari nama bahan..." value="{{ request('q') }}">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i></button>
            </form>
        </div>

        {{-- Tabel Master Bahan --}}
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0 align-middle" id="tabelMasterBahan">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Nama Bahan</th>
                                <th width="10%" class="text-center">Satuan</th>
                                <th width="25%">Cek Harga Supplier</th>
                                <th width="15%">Update Terakhir</th>
                                <th width="15%">Harga (Rp)</th>
                                <th width="5%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bahans as $index => $bahan)
                                <tr data-id="{{ $bahan->ID_Bahan }}">
                                    <td>{{ $bahans->firstItem() + $index }}</td>

                                    {{-- Kolom Nama & Edit --}}
                                    <td>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold nama-bahan-text">{{ $bahan->Nama_Bahan }}</span>
                                            <button class="btn btn-link btn-sm text-decoration-none btn-edit-bahan p-0 ms-2"
                                                data-id="{{ $bahan->ID_Bahan }}"
                                                data-nama="{{ $bahan->Nama_Bahan }}"
                                                data-satuan="{{ $bahan->ID_Satuan_Bahan }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </div>
                                    </td>

                                    <td class="text-center">{{ $bahan->satuan->Nama_Satuan ?? '-' }}</td>

                                    {{-- Kolom Cek Harga --}}
                                    <td>
                                        <select class="form-select form-select-sm supplier-select">
                                            <option value="" selected disabled>-- Cek Harga Supplier --</option>
                                            @foreach($suppliers as $sup)
                                                @php
                                                    $priceData = $allPrices->where('ID_Bahan', $bahan->ID_Bahan)
                                                                           ->where('ID_Supplier', $sup->ID_Supplier)
                                                                           ->first();
                                                    $valHarga = $priceData ? (int)$priceData->Harga_Per_Satuan : '';
                                                    $valIdHarga = $priceData ? $priceData->ID_Harga : '';
                                                    $valTgl = $priceData ? \Carbon\Carbon::parse($priceData->Tanggal_Update_Data)->format('d/m/Y') : '-';
                                                @endphp
                                                <option value="{{ $sup->ID_Supplier }}"
                                                        data-harga="{{ $valHarga }}"
                                                        data-id-harga="{{ $valIdHarga }}"
                                                        data-tgl="{{ $valTgl }}">
                                                    {{ $sup->Nama_Supplier }} {{ $priceData ? '' : '(Belum ada)' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="text-muted small cell-tgl text-center">-</td>

                                    {{-- Input Harga --}}
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control harga-input text-end" placeholder="Pilih Supplier">
                                        </div>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button class="btn btn-success btn-sm btn-simpan-harga" disabled
                                                    data-url="{{ route('bahan.updateHargaInline') }}"
                                                    data-bahan-id="{{ $bahan->ID_Bahan }}"
                                                    title="Simpan Harga">
                                                <i class="bi bi-save"></i>
                                            </button>

                                            <button class="btn btn-danger btn-sm btn-hapus-bahan"
                                                    data-url="{{ route('master-bahan.destroy', $bahan->ID_Bahan) }}"
                                                    title="Hapus Bahan">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Tidak ada data bahan ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $bahans->links() }}
            </div>
        </div>
    </div>
</div>

{{-- =================================================================== --}}
{{-- MODAL SECTION: BAHAN --}}
{{-- =================================================================== --}}

{{-- Modal Edit Bahan --}}
<div class="modal fade" id="modalFormBahan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formBahan" method="POST">
                @csrf
                <input type="hidden" name="_method" value="POST" id="methodBahan">
                <div class="modal-header">
                    <h5 class="modal-title" id="judulModalBahan">Edit Bahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Bahan</label>
                        <input type="text" name="Nama_Bahan" id="inputNamaBahan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Satuan</label>
                        <select name="ID_Satuan_Bahan" id="inputSatuanBahan" class="form-select" required>
                            <option value="">-- Pilih Satuan --</option>
                            @foreach($satuans as $satuan)
                                <option value="{{ $satuan->ID_Satuan_Ukur }}">{{ $satuan->Nama_Satuan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- =================================================================== --}}
{{-- MODAL SECTION: SUPPLIER (INTEGRASI BARU) --}}
{{-- =================================================================== --}}

<div class="modal fade" id="modalSupplier" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kelola Data Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <button type="button" class="btn btn-primary btn-sm mb-3" id="btnBukaModalTambahSupplier">
                    <i class="bi bi-plus"></i> Tambah Supplier Baru
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
                                            style="font-size: 0.75rem;" data-id="{{ $supplier->ID_Supplier }}">+ Alamat</button>
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
                                            style="font-size: 0.75rem;" data-id="{{ $supplier->ID_Supplier }}">+ Kontak</button>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-buka-edit-supplier"
                                            data-id="{{ $supplier->ID_Supplier }}"
                                            data-nama="{{ $supplier->Nama_Supplier }}"
                                            data-url-update="{{ route('supplier.update', ':id') }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-hapus-supplier ms-1"
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

<div class="toast-container position-fixed top-0 end-0 p-3"></div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="{{ asset('js/master-bahan.js') }}?v={{ time() }}"></script>

@endsection
