<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendataan Bahan & Produsen</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .pilih-supplier-btn {
            min-width: 160px;
            justify-content: space-between;
            white-space: nowrap;
        }
        .supplier-name-text {
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            max-width: 120px;
            vertical-align: middle;
        }
        .supplier-list-scroll {
            max-height: 60vh;
            overflow: auto;
        }
        .supplier-row:hover { background: #f8f9fa; cursor: pointer; }
        .price-updated { animation: highlight 1s; }
        @keyframes highlight {
            from { background: #e6ffea; }
            to { background: transparent; }
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
                        Desain:
                        <strong>{{ $recaps->first()->desainRumah->Nama_Desain ?? 'ID: '.$recaps->first()->ID_Desain_Rumah }}</strong>
                    </h5>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0 align-middle">
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
                                    <tr class="recap-row"
                                        data-id-rekap="{{ $recap->ID_Rekap }}"
                                        data-id-bahan="{{ $recap->ID_Bahan }}">
                                        <td style="width:50px;">{{ $index + 1 }}</td>

                                        <td style="min-width:140px;">
                                            {{ \Carbon\Carbon::parse($recap->Tanggal_Hitung)->format('d M Y') }}
                                            <small class="text-muted d-block">
                                                {{ \Carbon\Carbon::parse($recap->Tanggal_Hitung)->format('H:i') }}
                                            </small>
                                        </td>

                                        <td style="min-width:200px;">{{ $recap->bahan->Nama_Bahan ?? 'ID: '.$recap->ID_Bahan }}</td>

                                        <td class="text-end vol-teoritis">{{ number_format($recap->Volume_Teoritis, 2, ',', '.') }}</td>
                                        <td class="text-end vol-final fw-bold">{{ number_format($recap->Volume_Final, 2, ',', '.') }}</td>

                                        <td class="text-center">{{ $recap->Satuan_Saat_Ini }}</td>

                                        <td class="text-end harga-cell">
                                            <span class="harga-value">
                                                @if($recap->Harga_Satuan_Saat_Ini)
                                                    Rp {{ number_format($recap->Harga_Satuan_Saat_Ini, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>

                                        <td class="text-end fw-bold text-success total-cell">
                                            <span class="total-value">
                                                @if($recap->Total_Harga)
                                                    Rp {{ number_format($recap->Total_Harga, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>

                                        <td style="min-width:180px;">
                                            <button type="button"
                                                    class="btn btn-sm btn-light border d-flex align-items-center gap-2 pilih-supplier-btn"
                                                    data-id-rekap="{{ $recap->ID_Rekap }}"
                                                    data-id-bahan="{{ $recap->ID_Bahan }}"
                                                    data-current-name="{{ $recap->supplier->Nama_Supplier ?? '' }}">
                                                <span class="supplier-name-text">
                                                    {{ $recap->supplier->Nama_Supplier ?? 'Pilih Supplier' }}
                                                </span>
                                                <i class="bi bi-chevron-down small"></i>
                                            </button>
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

<!-- ================= MODALS ================= -->

<!-- Modal Supplier -->
<div class="modal fade" id="modalSupplier" tabindex="-1" aria-labelledby="modalSupplierLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <button class="btn btn-primary btn-sm mb-3"
                        data-bs-toggle="modal"
                        data-bs-target="#modalTambahSupplier">
                    Tambah Supplier
                </button>

                <table class="table table-bordered table-sm">
                    <thead><tr><th>No</th><th>ID Supplier</th><th>Nama Supplier</th></tr></thead>
                    <tbody>
                        @foreach ($suppliers as $index => $supplier)
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

                    <button type="submit" class="btn btn-primary w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Supplier (modal-xl) -->
<div class="modal fade" id="modalPilihSupplier" tabindex="-1" aria-labelledby="modalPilihSupplierLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="pilihSupplierInfo" class="mb-3">
                    <small class="text-muted">Pilih supplier untuk baris rekap ID: <span id="currentRekapId">-</span></small>
                </div>

                <div class="mb-3">
                    <input type="search" id="searchSupplierInput" class="form-control" placeholder="Cari supplier... (ketik nama)">
                </div>

                <div class="supplier-list-scroll">
                    <div class="list-group" id="supplierListGroup">
                        @foreach ($suppliers as $supplier)
                            <div class="list-group-item d-flex justify-content-between align-items-center supplier-row"
                                 data-nama="{{ strtolower($supplier->Nama_Supplier) }}"
                                 data-id-supplier="{{ $supplier->ID_Supplier }}">
                                <div>
                                    <strong>{{ $supplier->Nama_Supplier }}</strong>
                                    <div class="small text-muted">ID: {{ $supplier->ID_Supplier }}</div>
                                </div>

                                <div>
                                    <button class="btn btn-sm btn-outline-primary pilih-ini-btn"
                                            data-id-supplier="{{ $supplier->ID_Supplier }}"
                                            data-nama="{{ $supplier->Nama_Supplier }}">
                                        Pilih
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bahan (modal-xl) -->
<div class="modal fade" id="modalBahan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kelola Harga Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-info">
                    Mengisi harga bahan di sini akan otomatis:
                    <ul class="mb-0">
                        <li>Membuat / memperbarui data <b>list_harga_bahan</b></li>
                        <li>Mengupdate <b>harga & total</b> pada tabel utama (semua rekap yang menggunakan bahan ini)</li>
                    </ul>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Bahan</th>
                                <th>Supplier</th>
                                <th>Harga per Satuan (Rp)</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                // To avoid duplicate bahan rows if recaps contain same bahan many times,
                                // aggregate unique bahan list for the modal. We'll collect unique ID_Bahan from recaps.
                                $uniqueBahan = collect($recaps)->unique('ID_Bahan')->values();
                            @endphp

                            @foreach ($uniqueBahan as $i => $recapB)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>{{ $recapB->bahan->Nama_Bahan ?? 'ID: ' . $recapB->ID_Bahan }}</td>

                                    <td>
                                        <select class="form-select form-select-sm pilihSupplierHarga"
                                                data-id-bahan="{{ $recapB->ID_Bahan }}">
                                            <option value="">-- Pilih Supplier --</option>
                                            @foreach ($suppliers as $sup)
                                                <option value="{{ $sup->ID_Supplier }}">
                                                    {{ $sup->Nama_Supplier }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td style="width: 220px;">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number"
                                                   class="form-control inputHargaBahan"
                                                   data-id-bahan="{{ $recapB->ID_Bahan }}"
                                                   min="0"
                                                   placeholder="Masukkan harga">
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary btnSimpanHarga"
                                                data-id-bahan="{{ $recapB->ID_Bahan }}">
                                            Simpan
                                        </button>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>

                    </table>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Helpers
    function formatRupiah(number) {
        if (number === null || number === undefined || isNaN(number) || Number(number) === 0) return '-';
        return 'Rp ' + Number(number).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    function showToast(message, type='success') {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="toast align-items-center text-bg-${type} border-0 position-fixed" role="alert" aria-live="assertive" aria-atomic="true" style="right:1rem; bottom:1rem; z-index:12000;">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`;
        document.body.appendChild(wrapper);
        const toastEl = wrapper.querySelector('.toast');
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => wrapper.remove());
    }

    // Modal pilih supplier logic
    const modalPilihSupplierEl = document.getElementById('modalPilihSupplier');
    const modalPilihSupplier = new bootstrap.Modal(modalPilihSupplierEl);
    let currentRekapId = null;
    let currentBahanIdForRekap = null;

    document.querySelectorAll('.pilih-supplier-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            currentRekapId = this.getAttribute('data-id-rekap');
            currentBahanIdForRekap = this.getAttribute('data-id-bahan');
            document.getElementById('currentRekapId').textContent = currentRekapId;
            modalPilihSupplier.show();
            setTimeout(() => { document.getElementById('searchSupplierInput').focus(); }, 250);
        });
    });

    // supplier search filter
    const searchInput = document.getElementById('searchSupplierInput');
    searchInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('#supplierListGroup .supplier-row').forEach(row => {
            const nama = row.getAttribute('data-nama') || '';
            row.style.display = (!q || nama.indexOf(q) !== -1) ? '' : 'none';
        });
    });

    // pilih supplier -> update recap via AJAX
    document.querySelectorAll('.pilih-ini-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const idSupplier = this.getAttribute('data-id-supplier');
            const namaSupplier = this.getAttribute('data-nama');

            if (!currentRekapId) {
                showToast('ID rekap tidak terdeteksi. Tutup modal lalu coba lagi.', 'danger');
                return;
            }

            // disable button while saving
            this.disabled = true;
            const original = this.innerHTML;
            this.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memilih...`;

            try {
                const res = await fetch('{{ route("rekap.updateSupplier") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ID_Rekap: currentRekapId,
                        ID_Supplier: idSupplier
                    })
                });

                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menyimpan supplier');

                // update supplier button text
                const selector = `.pilih-supplier-btn[data-id-rekap="${currentRekapId}"]`;
                const targetBtn = document.querySelector(selector);
                if (targetBtn) {
                    const spanName = targetBtn.querySelector('.supplier-name-text');
                    if (spanName) spanName.textContent = data.supplier_name || namaSupplier || 'Pilih Supplier';
                    targetBtn.setAttribute('data-current-name', data.supplier_name || namaSupplier || '');
                    // update price & total cells for that row
                    const row = document.querySelector(`.recap-row[data-id-rekap="${data.ID_Rekap}"]`);
                    if (row) {
                        const hargaCell = row.querySelector('.harga-value');
                        const totalCell = row.querySelector('.total-value');
                        if (hargaCell) hargaCell.textContent = formatRupiah(data.harga);
                        if (totalCell) totalCell.textContent = formatRupiah(data.total_harga);
                        // highlight
                        row.classList.add('price-updated');
                        setTimeout(()=> row.classList.remove('price-updated'), 900);
                    }
                }

                showToast('Supplier berhasil diperbarui.', 'success');
                modalPilihSupplier.hide();
            } catch (err) {
                console.error(err);
                showToast(err.message || 'Gagal menyimpan supplier.', 'danger');
            } finally {
                this.disabled = false;
                this.innerHTML = original;
            }
        });
    });

    // MODAL BAHAN: Save harga per row
    document.querySelectorAll('.btnSimpanHarga').forEach(btn => {
        btn.addEventListener('click', async function () {
            const idBahan = this.getAttribute('data-id-bahan');
            // cari select dan input terkait
            const select = document.querySelector(`.pilihSupplierHarga[data-id-bahan="${idBahan}"]`);
            const input = document.querySelector(`.inputHargaBahan[data-id-bahan="${idBahan}"]`);
            if (!select || !input) {
                showToast('Elemen tidak ditemukan.', 'danger');
                return;
            }
            const idSupplier = select.value;
            const harga = input.value;

            if (!idSupplier) {
                showToast('Pilih supplier terlebih dahulu.', 'warning');
                return;
            }
            if (!harga || Number(harga) <= 0) {
                showToast('Masukkan harga yang valid.', 'warning');
                return;
            }

            // disable
            this.disabled = true;
            const origText = this.innerHTML;
            this.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...`;

            try {
                const res = await fetch('{{ route("bahan.simpanHarga") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ID_Bahan: idBahan,
                        ID_Supplier: idSupplier,
                        Harga_per_Satuan: harga
                    })
                });

                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menyimpan harga');

                // update all recap rows that have this ID_Bahan and same supplier (and optionally update all with same ID_Bahan)
                document.querySelectorAll(`.recap-row[data-id-bahan="${idBahan}"]`).forEach(row => {
                    // if recap row has ID_Supplier equal to idSupplier (we store in button data-current-name maybe),
                    // but to simplify, update price for rows that have ID_Supplier == idSupplier or even update all rows with same bahan
                    const hargaCell = row.querySelector('.harga-value');
                    const totalCell = row.querySelector('.total-value');
                    // compute new total using Volume_Final (stored text). Retrieve numeric volume_final by removing dots/comma
                    const volFinalText = row.querySelector('.vol-final').textContent.trim().replace(/\./g, '').replace(',', '.');
                    const volFinal = parseFloat(volFinalText) || 0;
                    const newTotal = volFinal * Number(data.Harga_per_Satuan);
                    if (hargaCell) hargaCell.textContent = formatRupiah(data.Harga_per_Satuan);
                    if (totalCell) totalCell.textContent = formatRupiah(newTotal);
                    row.classList.add('price-updated');
                    setTimeout(()=> row.classList.remove('price-updated'), 900);
                });

                showToast('Harga bahan tersimpan dan rekap diperbarui.', 'success');
            } catch (err) {
                console.error(err);
                showToast(err.message || 'Gagal menyimpan harga.', 'danger');
            } finally {
                this.disabled = false;
                this.innerHTML = origText;
            }
        });
    });

    // If session success after adding supplier (server-side redirect), show Data Supplier modal again
    @if (session('success'))
        const modalSupplier = new bootstrap.Modal(document.getElementById('modalSupplier'));
        modalSupplier.show();
    @endif

});
</script>

</body>
</html>
