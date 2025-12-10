$(document).ready(function() {

    // ==========================================
    // 0. SETUP UMUM
    // ==========================================

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // AUTO-OPEN MODAL SUPPLIER (SETELAH REFRESH)
    if (localStorage.getItem('bukaModalSupplier') === 'true') {
        localStorage.removeItem('bukaModalSupplier');
        $('#modalSupplier').modal('show');
    }

    // HELPER FUNCTIONS
    function showToast(message, type = 'success') {
        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
        const toastHtml = `
            <div class="toast align-items-center text-white ${bgClass} border-0 mb-2">
                <div class="d-flex"><div class="toast-body"><i class="bi ${icon} me-2"></i> ${message}</div></div>
            </div>`;
        $('.toast-container').append(toastHtml);
        new bootstrap.Toast($('.toast-container .toast:last-child')).show();
    }

    function formatRupiah(angka) {
        if (!angka) return '';
        let number_string = angka.toString().replace(/[^0-9]/g, '');
        let sisa = number_string.length % 3,
            rupiah = number_string.substr(0, sisa),
            ribuan = number_string.substr(sisa).match(/\d{3}/g);
        if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        return rupiah;
    }

    function cleanRupiah(formatted) { return formatted.toString().replace(/\./g, ''); }


    // ==========================================
    // 1. LOGIKA HARGA BAHAN (MASTER LIST)
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
        $btnSimpan.prop('disabled', true).removeClass('btn-warning').addClass('btn-success').html('<i class="bi bi-save"></i>');

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

    $('#tabelMasterBahan').on('change', '.supplier-select', function() {
        updateHargaUI($(this).closest('tr'));
    });

    $('#tabelMasterBahan').on('input', '.harga-input', function() {
        const $row = $(this).closest('tr');
        const $btnSimpan = $row.find('.btn-simpan-harga');
        $(this).val(formatRupiah($(this).val()));
        $btnSimpan.prop('disabled', false).removeClass('btn-success').addClass('btn-warning').html('<i class="bi bi-save"></i> Simpan');
    });

    $('#tabelMasterBahan').on('click', '.btn-simpan-harga', function() {
        const $btn = $(this);
        const $row = $btn.closest('tr');
        const $select = $row.find('.supplier-select');

        const url = $btn.data('url');
        const idBahan = $btn.data('bahan-id');
        const idSupplier = $select.val();
        const idHarga = $btn.data('harga-id');
        let hargaClean = cleanRupiah($row.find('.harga-input').val());

        if (!idSupplier) { showToast('Pilih supplier dulu!', 'error'); return; }
        if (!hargaClean) { showToast('Isi harga dulu!', 'error'); return; }

        $btn.prop('disabled', true).html('...');

        $.ajax({
            url: url, type: "POST",
            data: { ID_Harga: idHarga, ID_Bahan: idBahan, ID_Supplier: idSupplier, Harga_Per_Satuan: hargaClean },
            success: function(response) {
                showToast('Harga tersimpan!', 'success');
                $btn.removeClass('btn-warning').addClass('btn-success').html('<i class="bi bi-check-lg"></i>');

                let $opt = $select.find('option:selected');
                $opt.attr('data-harga', hargaClean);
                $opt.attr('data-id-harga', response.data.id);
                $opt.attr('data-tgl', 'Baru saja');
                $row.find('.cell-tgl').text('Baru saja');

                let text = $opt.text();
                if(text.includes('(Belum ada)')) $opt.text(text.replace('(Belum ada)', '').trim());
            },
            error: function() { showToast('Gagal menyimpan.', 'error'); $btn.prop('disabled', false).html('<i class="bi bi-save"></i>'); }
        });
    });


    // ==========================================
    // 2. LOGIKA CRUD BAHAN (EDIT & HAPUS)
    // ==========================================

    $(document).on('click', '.btn-edit-bahan', function() {
        let id = $(this).data('id');
        let nama = $(this).data('nama');
        let satuan = $(this).data('satuan');

        $('#judulModalBahan').text('Edit Bahan');
        $('#methodBahan').val('PUT');
        $('#formBahan').attr('action', '/master-bahan/update/' + id);
        $('#inputNamaBahan').val(nama);
        $('#inputSatuanBahan').val(satuan);
        $('#modalFormBahan').modal('show');
    });

    $('#formBahan').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(),
            success: function(response) {
                $('#modalFormBahan').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 1500, showConfirmButton: false }).then(() => location.reload());
            },
            error: function(xhr) {
                let msg = 'Gagal memproses data';
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    $(document).on('click', '.btn-hapus-bahan', function() {
        let url = $(this).data('url');
        Swal.fire({
            title: 'Hapus Bahan?',
            text: "PERINGATAN: Menghapus bahan ini akan menghapus semua history harganya. Pastikan bahan ini tidak digunakan dalam rumus AHSP!",
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url, type: 'DELETE',
                    success: function(response) {
                        Swal.fire('Terhapus!', response.message, 'success').then(() => location.reload());
                    },
                    error: function() { Swal.fire('Error', 'Gagal menghapus data', 'error'); }
                });
            }
        });
    });


    // ==========================================
    // 3. LOGIKA SUPPLIER (SWAP MODAL FLOW)
    // ==========================================

    function switchModal(fromModalId, toModalId) {
        $(fromModalId).modal('hide');
        setTimeout(function() { $(toModalId).modal('show'); }, 400);
    }

    // A. NAVIGASI MAJU
    $('#btnBukaModalTambahSupplier').click(function() { switchModal('#modalSupplier', '#modalTambahSupplier'); });

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

    // B. NAVIGASI MUNDUR
    $('.btn-kembali-ke-list').click(function() {
        let currentModal = $(this).closest('.modal');
        switchModal(currentModal, '#modalSupplier');
    });

    // C. CRUD SUPPLIER (AJAX)

    // 1. TAMBAH SUPPLIER
    $('#formTambahSupplier').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: form.attr('action'), type: 'POST', data: new FormData(this), contentType: false, processData: false,
            success: function(response) {
                form[0].reset();
                let newData = response.data;
                let rowCount = $('#bodyTabelSupplier tr').length + 1;

                // URL & Query Strings
                let urlUpdate = newData.Url_Update;
                let urlHapusSupplier = newData.Url_Hapus_Supplier;
                let urlHapusAlamat = `${newData.Url_Hapus_Alamat}?id_supplier=${newData.ID_Supplier}&alamat=${encodeURIComponent(newData.Alamat_Awal)}`;
                let urlHapusKontak = `${newData.Url_Hapus_Kontak}?id_supplier=${newData.ID_Supplier}&kontak=${encodeURIComponent(newData.Kontak_Awal)}`;

                // Generate HTML Row
                let newRow = `
                    <tr>
                        <td>${rowCount}</td>
                        <td class="fw-bold">${newData.Nama_Supplier}</td>
                        <td>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1 bg-light p-1 rounded">
                                    <span class="small"><i class="bi bi-geo-alt"></i> ${newData.Alamat_Awal}</span>
                                    <button type="button" class="btn btn-danger btn-sm p-0 px-1 btn-hapus-alamat" data-url="${urlHapusAlamat}">
                                        <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                    </button>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary btn-sm py-0 btn-buka-tambah-alamat" style="font-size: 0.75rem;" data-id="${newData.ID_Supplier}">+ Alamat</button>
                        </td>
                        <td>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1 bg-light p-1 rounded">
                                    <span class="small"><i class="bi bi-telephone"></i> ${newData.Kontak_Awal}</span>
                                    <button type="button" class="btn btn-danger btn-sm p-0 px-1 btn-hapus-kontak" data-url="${urlHapusKontak}">
                                        <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                    </button>
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
                let msg = 'Gagal menyimpan.';
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire('Error', msg, 'error');
            },
            complete: function() { btn.prop('disabled', false).text('Simpan'); }
        });
    });

    // 2. SIMPAN ALAMAT/KONTAK/EDIT (RELOAD)
    $('#formTambahAlamat, #formTambahKontak, #formEditSupplier').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: form.attr('action'), type: 'POST', data: form.serialize(),
            success: function(response) {
                localStorage.setItem('bukaModalSupplier', 'true');
                location.reload();
            },
            error: function(xhr) {
                Swal.fire('Error', 'Gagal menyimpan data.', 'error');
                btn.prop('disabled', false).text('Simpan');
            }
        });
    });

    // 3. DELETE ITEMS
    $(document).on('click', '.btn-hapus-alamat, .btn-hapus-kontak', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const $row = $(this).closest('.d-flex');

        Swal.fire({
            title: 'Hapus Item?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya'
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

    $(document).on('click', '.btn-hapus-supplier', function(e) {
        e.preventDefault();
        let btn = $(this);
        let url = btn.data('url');
        let row = btn.closest('tr');

        Swal.fire({
            title: 'Hapus Supplier?',
            text: "Semua data alamat, kontak, dan harga akan ikut terhapus!",
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                $.ajax({
                    url: url, type: 'DELETE',
                    success: function(response) {
                        showToast('Supplier dihapus', 'success');
                        row.fadeOut(300, function() { $(this).remove(); });
                    },
                    error: function(xhr) {
                        let msg = 'Gagal menghapus data.';
                        if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal.fire('Error', msg, 'error');
                        btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                    }
                });
            }
        });
    });

});
