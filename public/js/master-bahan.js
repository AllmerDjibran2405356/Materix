$(document).ready(function() {

    // SETUP CSRF
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Helper Toast
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
        let sisa = number_string.length % 3, rupiah = number_string.substr(0, sisa), ribuan = number_string.substr(sisa).match(/\d{3}/g);
        if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        return rupiah;
    }

    function cleanRupiah(formatted) { return formatted.toString().replace(/\./g, ''); }

    // ===============================================
    // 1. LOGIKA HARGA BAHAN
    // ===============================================

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

    $('#tabelMasterBahan').on('change', '.supplier-select', function() { updateHargaUI($(this).closest('tr')); });

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

        if (!idSupplier || !hargaClean) { showToast('Pilih supplier dan isi harga!', 'error'); return; }

        $btn.prop('disabled', true).html('...');

        $.ajax({
            url: url, type: "POST",
            data: { ID_Harga: idHarga, ID_Bahan: idBahan, ID_Supplier: idSupplier, Harga_Per_Satuan: hargaClean },
            success: function(response) {
                showToast('Harga tersimpan!', 'success');
                $btn.removeClass('btn-warning').addClass('btn-success').html('<i class="bi bi-check-lg"></i>');

                let $opt = $select.find('option:selected');
                $opt.attr('data-harga', hargaClean);
                $opt.attr('data-tgl', 'Baru saja');
                $row.find('.cell-tgl').text('Baru saja');

                let text = $opt.text();
                if(text.includes('(Belum ada)')) $opt.text(text.replace('(Belum ada)', '').trim());
            },
            error: function() { showToast('Gagal menyimpan.', 'error'); $btn.prop('disabled', false).html('<i class="bi bi-save"></i>'); }
        });
    });

    // ===============================================
    // 2. LOGIKA CRUD BAHAN (HANYA EDIT & HAPUS)
    // ===============================================

    // A. Buka Modal Edit (Hanya ini yang tersisa)
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

    // B. Simpan Perubahan (Edit)
    $('#formBahan').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                $('#modalFormBahan').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 1500, showConfirmButton: false }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                let msg = 'Gagal memproses data';
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // C. Hapus Bahan (Masih diperlukan kah? Jika AHSP baku, mungkin hapus juga perlu dihilangkan?)
    // Jika ingin tetap ada fitur hapus untuk bahan "sampah", biarkan kode di bawah.
    // Jika tidak, hapus blok kode ini dan hapus tombol di View.
    $(document).on('click', '.btn-hapus-bahan', function() {
        let url = $(this).data('url');
        Swal.fire({
            title: 'Hapus Bahan?',
            text: "Pastikan bahan ini TIDAK digunakan dalam rumus AHSP!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
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
});
