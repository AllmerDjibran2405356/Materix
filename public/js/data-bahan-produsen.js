$(document).ready(function() {

    // ==========================================
    // 0. SETUP UMUM & HELPER
    // ==========================================

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    if (localStorage.getItem('bukaModalSupplier') === 'true') {
        localStorage.removeItem('bukaModalSupplier');
        $('#modalSupplier').modal('show');
    }

    function showToast(message, type = 'success') {
        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
        const toastHtml = `
            <div class="toast align-items-center text-white ${bgClass} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi ${icon} me-2"></i> ${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`;
        $('.toast-container').append(toastHtml);
        const toast = new bootstrap.Toast($('.toast-container .toast:last-child'));
        toast.show();
        setTimeout(() => { $('.toast-container .toast:first-child').remove(); }, 3000);
    }

    function formatRupiah(angka) {
        if (!angka) return '';
        let number_string = angka.toString().replace(/[^0-9]/g, '');
        let sisa = number_string.length % 3,
            rupiah = number_string.substr(0, sisa),
            ribuan = number_string.substr(sisa).match(/\d{3}/g);
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return rupiah;
    }

    function cleanRupiah(formatted) {
        return formatted.toString().replace(/\./g, '').replace('Rp ', '').trim();
    }

    // ==========================================
    // 1. LOGIKA HARGA BAHAN (MODAL HARGA)
    // ==========================================

    function updateHargaUI($row) {
        const $select = $row.find('.supplier-select');
        const $selectedOption = $select.find('option:selected');
        const $inputHarga = $row.find('.harga-input');
        const $cellTgl = $row.find('.cell-tgl');
        const $btnSimpan = $row.find('.btn-simpan-harga');

        const hargaRaw = $selectedOption.attr('data-harga');
        const idHarga = $selectedOption.attr('data-id-harga');
        const tgl = $selectedOption.attr('data-tgl');

        $btnSimpan.data('harga-id', idHarga);
        $btnSimpan.prop('disabled', true)
                  .removeClass('btn-warning').addClass('btn-success')
                  .html('<i class="bi bi-save"></i>');

        if (hargaRaw && hargaRaw !== "" && hargaRaw !== "0") {
            $inputHarga.val(formatRupiah(hargaRaw));
            $inputHarga.attr('placeholder', '');
            $cellTgl.text(tgl);
        } else {
            $inputHarga.val('');
            $inputHarga.attr('placeholder', 'Kosong');
            $cellTgl.text('-');
        }
    }

    $('#tabelHargaBahan').on('change', '.supplier-select', function() {
        updateHargaUI($(this).closest('tr'));
    });

    $('#tabelHargaBahan').on('input', '.harga-input', function() {
        const $row = $(this).closest('tr');
        const $btnSimpan = $row.find('.btn-simpan-harga');
        let val = $(this).val();
        $(this).val(formatRupiah(val));

        $btnSimpan.prop('disabled', false)
                  .removeClass('btn-success').addClass('btn-warning')
                  .html('<i class="bi bi-save"></i> Simpan');
    });

    $('#tabelHargaBahan').on('click', '.btn-simpan-harga', function() {
        const $btn = $(this);
        const $row = $btn.closest('tr');
        const $select = $row.find('.supplier-select');
        const $selectedOption = $select.find('option:selected');

        const url = $btn.data('url');
        const idBahan = $btn.data('bahan-id');
        const idSupplier = $select.val();
        const idHarga = $btn.data('harga-id');
        let hargaFormatted = $row.find('.harga-input').val();
        let hargaClean = cleanRupiah(hargaFormatted);

        if (!idSupplier || !hargaClean) {
            showToast('Pilih supplier dan isi harga!', 'error');
            return;
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: url,
            type: "POST",
            data: {
                ID_Harga: idHarga,
                ID_Bahan: idBahan,
                ID_Supplier: idSupplier,
                Harga_Per_Satuan: hargaClean
            },
            success: function(response) {
                showToast('Tersimpan!', 'success');
                $btn.removeClass('btn-warning').addClass('btn-success')
                    .html('<i class="bi bi-check-lg"></i>')
                    .prop('disabled', true);

                if (response.data) {
                    $btn.data('harga-id', response.data.id);
                    $selectedOption.attr('data-id-harga', response.data.id);
                    $selectedOption.attr('data-tgl', response.data.updated_at);
                    $row.find('.cell-tgl').text(response.data.updated_at);
                }
                $selectedOption.attr('data-harga', hargaClean);
                let text = $selectedOption.text();
                if(text.includes('(Belum ada)')) {
                    $selectedOption.text(text.replace('(Belum ada)', '').trim());
                }
            },
            error: function(xhr) {
                showToast('Gagal menyimpan.', 'error');
                $btn.prop('disabled', false).html('<i class="bi bi-save"></i> Retry');
            }
        });
    });

    $('#tabelHargaBahan tbody tr').each(function() {
        let $input = $(this).find('.harga-input');
        let raw = $input.val();
        if(raw) { $input.val(formatRupiah(raw)); }
        updateHargaUI($(this));
    });


    // ==========================================
    // 2. LOGIKA SUPPLIER (SWAP FLOW)
    // ==========================================

    function switchModal(fromModalId, toModalId) {
        $(fromModalId).modal('hide');
        setTimeout(function() {
            $(toModalId).modal('show');
        }, 400);
    }

    // --- A. NAVIGASI ---

    $('#btnBukaModalTambahSupplier').click(function() {
        switchModal('#modalSupplier', '#modalTambahSupplier');
    });

    $(document).on('click', '.btn-buka-tambah-alamat', function() {
        let id = $(this).data('id');
        $('#inputIdSupplierAlamat').val(id);
        switchModal('#modalSupplier', '#modalTambahAlamat');
    });

    $(document).on('click', '.btn-buka-tambah-kontak', function() {
        let id = $(this).data('id');
        $('#inputIdSupplierKontak').val(id);
        switchModal('#modalSupplier', '#modalTambahKontak');
    });

    $(document).on('click', '.btn-buka-edit-supplier', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        let url = $(this).data('url-update').replace(':id', id);

        $('#editSupplierId').val(id);
        $('#editNamaSupplier').val(nama);
        $('#formEditSupplier').attr('action', url);

        switchModal('#modalSupplier', '#modalEditSupplier');
    });

    $('.btn-kembali-ke-list').click(function() {
        let currentModal = $(this).closest('.modal');
        switchModal(currentModal, '#modalSupplier');
    });


    // --- B. LOGIKA SIMPAN (CRUD) ---

    // 1. Simpan Supplier Baru
    $('#formTambahSupplier').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = form.find('button[type="submit"]');
        let formData = new FormData(this);

        btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                form[0].reset();
                let newData = response.data;
                let rowCount = $('#bodyTabelSupplier tr').length + 1;

                let urlUpdate = newData.Url_Update;
                let urlHapusSupplier = newData.Url_Hapus_Supplier;
                let urlHapusAlamat = `${newData.Url_Hapus_Alamat}?id_supplier=${newData.ID_Supplier}&alamat=${encodeURIComponent(newData.Alamat_Awal)}`;
                let urlHapusKontak = `${newData.Url_Hapus_Kontak}?id_supplier=${newData.ID_Supplier}&kontak=${encodeURIComponent(newData.Kontak_Awal)}`;

                let newRow = `
                    <tr>
                        <td>${rowCount}</td>
                        <td class="fw-bold">${newData.Nama_Supplier}</td>
                        <td>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1 bg-light p-1 rounded">
                                    <span class="small"><i class="bi bi-geo-alt"></i> ${newData.Alamat_Awal}</span>
                                    <button type="button" class="btn btn-danger btn-sm p-0 px-1 btn-hapus-alamat" data-url="${urlHapusAlamat}"><i class="bi bi-trash" style="font-size: 0.8rem;"></i></button>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary btn-sm py-0 btn-buka-tambah-alamat" style="font-size: 0.75rem;" data-id="${newData.ID_Supplier}">+ Alamat</button>
                        </td>
                        <td>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1 bg-light p-1 rounded">
                                    <span class="small"><i class="bi bi-telephone"></i> ${newData.Kontak_Awal}</span>
                                    <button type="button" class="btn btn-danger btn-sm p-0 px-1 btn-hapus-kontak" data-url="${urlHapusKontak}"><i class="bi bi-trash" style="font-size: 0.8rem;"></i></button>
                                </div>
                            </div>
                            <button class="btn btn-outline-success btn-sm py-0 btn-buka-tambah-kontak" style="font-size: 0.75rem;" data-id="${newData.ID_Supplier}">+ Kontak</button>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-sm btn-buka-edit-supplier" data-id="${newData.ID_Supplier}" data-nama="${newData.Nama_Supplier}" data-url-update="${urlUpdate}"><i class="bi bi-pencil"></i></button>
                            <button type="button" class="btn btn-danger btn-sm btn-hapus-supplier ms-1" data-id="${newData.ID_Supplier}" data-url="${urlHapusSupplier}"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#bodyTabelSupplier').append(newRow);

                showToast('Supplier berhasil ditambahkan!', 'success');
                switchModal('#modalTambahSupplier', '#modalSupplier');
            },
            error: function(xhr) {
                let msg = 'Gagal menyimpan data.';
                if(xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                Swal.fire('Error', msg, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).text('Simpan');
            }
        });
    });

    // 2. Simpan Alamat/Kontak/Edit (Reload)
    $('#formTambahAlamat, #formTambahKontak, #formEditSupplier').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = form.find('button[type="submit"]');

        btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                localStorage.setItem('bukaModalSupplier', 'true'); // Flag untuk auto-open
                location.reload();
            },
            error: function(xhr) {
                Swal.fire('Error', 'Gagal menyimpan data.', 'error');
                btn.prop('disabled', false).text('Simpan');
            }
        });
    });


    // --- C. LOGIKA HAPUS (DELETE) ---

    // 1. Hapus Alamat / Kontak
    $(document).on('click', '.btn-hapus-alamat, .btn-hapus-kontak', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const $row = $(this).closest('.d-flex');

        Swal.fire({
            title: 'Hapus Item?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url, type: 'DELETE',
                    success: function() {
                        showToast('Berhasil dihapus', 'success');
                        $row.fadeOut(300, function() { $(this).remove(); });
                    },
                    error: function() { showToast('Gagal menghapus', 'error'); }
                });
            }
        });
    });

    // 2. Hapus Supplier Utama
    $(document).on('click', '.btn-hapus-supplier', function(e) {
        e.preventDefault();
        let btn = $(this);
        let url = btn.data('url');
        let row = btn.closest('tr');

        Swal.fire({
            title: 'Hapus Supplier?',
            text: "Semua data alamat, kontak, dan harga terkait supplier ini akan ikut terhapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    success: function(response) {
                        showToast('Supplier dihapus', 'success');
                        row.fadeOut(300, function() {
                            $(this).remove();
                            if($('#bodyTabelSupplier tr').length === 0) {
                                $('#bodyTabelSupplier').html('<tr><td colspan="5" class="text-center text-muted">Data Kosong</td></tr>');
                            }
                        });
                    },
                    error: function(xhr) {
                        let msg = 'Gagal menghapus data.';
                        if(xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                        Swal.fire('Error', msg, 'error');
                        btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                    }
                });
            }
        });
    });


    // ==========================================
    // 4. LOGIKA TABEL UTAMA (AUTO UPDATE HARGA)
    // ==========================================

    // Hitung Ulang Grand Total di UI (Tanpa Reload)
    function recalculateGrandTotal() {
        let total = 0;
        $('.cell-total-harga').each(function() {
            // Ambil text (misal: "Rp 150.000") -> bersihkan -> jadi int (150000)
            let val = cleanRupiah($(this).text());
            if(val) total += parseInt(val);
        });
        // Update tampilan Grand Total
        $('#grand-total-display').text('Rp ' + formatRupiah(total));
    }

    // Saat user mengganti Supplier di Tabel Utama
    $(document).on('change', '.select-supplier-main', function() {
        let select = $(this);
        let row = select.closest('tr');
        let rekapId = select.data('id');
        let url = select.data('url');
        let supplierId = select.val();

        // 1. Visual Feedback
        select.prop('disabled', true);
        row.find('.cell-harga-satuan').text('...');
        row.find('.cell-total-harga').text('...');

        // 2. Kirim AJAX
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                ID_Rekap: rekapId,
                ID_Supplier: supplierId
            },
            success: function(response) {
                // 3. Update UI Baris Ini
                let harga = "Rp " + response.data.harga_satuan;
                let total = "Rp " + response.data.total_harga;

                row.find('.cell-harga-satuan').text(harga);
                row.find('.cell-total-harga').text(total);

                showToast('Harga diperbarui!', 'success');

                // 4. Update Grand Total
                recalculateGrandTotal();

                // Info jika harga 0
                if(response.data.harga_satuan === "0") {
                    Swal.fire({
                        icon: 'info',
                        title: 'Harga Kosong',
                        text: 'Supplier ini belum memiliki data harga untuk bahan tersebut.'
                    });
                }
            },
            error: function(xhr) {
                showToast('Gagal mengupdate supplier', 'error');
                // Kembalikan ke nilai sebelumnya (optional, requires storing prev value)
            },
            complete: function() {
                select.prop('disabled', false);
            }
        });
    });

});
