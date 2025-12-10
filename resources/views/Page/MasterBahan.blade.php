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

        {{-- Header & Search --}}
        <a href="{{ route('HomePage')}}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Homepage
        </a>
        <div class="d-flex justify-content-end align-items-center mb-4">
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

                                    {{-- Kolom Cek Harga (Reused Logic) --}}
                                    <td>
                                        <select class="form-select form-select-sm supplier-select">
                                            <option value="" selected disabled>-- Cek Harga Supplier --</option>
                                            @foreach($suppliers as $sup)
                                                @php
                                                    // Cari harga di collection global (Optimized)
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

                                    {{-- Tombol Simpan Harga --}}
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            {{-- Tombol Simpan Harga --}}
                                            <button class="btn btn-success btn-sm btn-simpan-harga" disabled
                                                    data-url="{{ route('bahan.updateHargaInline') }}"
                                                    data-bahan-id="{{ $bahan->ID_Bahan }}"
                                                    title="Simpan Harga">
                                                <i class="bi bi-save"></i>
                                            </button>

                                            {{-- Tombol Hapus Bahan --}}
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
            {{-- Pagination --}}
            <div class="card-footer">
                {{ $bahans->links() }}
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH/EDIT BAHAN --}}
<div class="modal fade" id="modalFormBahan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formBahan" method="POST">
                @csrf
                <input type="hidden" name="_method" value="POST" id="methodBahan">
                <div class="modal-header">
                    <h5 class="modal-title" id="judulModalBahan">Tambah Bahan Baru</h5>
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

<div class="toast-container position-fixed top-0 end-0 p-3"></div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- KITA GUNAKAN JS YANG SAMA TAPI KITA MODIFIKASI SEDIKIT AGAR BISA DIPAKAI DI 2 HALAMAN --}}
<script src="{{ asset('js/master-bahan.js') }}?v={{ time() }}"></script>

@endsection
