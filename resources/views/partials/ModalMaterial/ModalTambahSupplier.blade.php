<div class="modal fade" id="tambahSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahSupplier">
                    @csrf
                    <div class="mb-3">
                        <label for="nama_supplier" class="form-label">Nama Supplier</label>
                        <input type="text" class="form-control" id="nama_supplier" name="nama_supplier" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat_supplier" class="form-label">Alamat Supplier</label>
                        <textarea class="form-control" id="alamat_supplier" name="alamat_supplier" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="kontak_supplier" class="form-label">Kontak Supplier</label>
                        <input type="text" class="form-control" id="kontak_supplier" name="kontak_supplier">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanSupplier()">Simpan</button>
            </div>
        </div>
    </div>
</div>