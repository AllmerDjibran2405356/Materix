<div class="modal fade" id="tambahMaterialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahMaterial" data-project-id="{{ $project->ID_Desain_Rumah }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bahan_id" class="form-label">Bahan</label>
                                <div class="input-group">
                                    <select class="form-select" id="bahan_id" name="bahan_id" required>
                                        <option value="">Pilih Bahan</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#tambahBahanModal">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="komponen_id" class="form-label">Komponen</label>
                                <select class="form-select" id="komponen_id" name="komponen_id" required>
                                    <option value="">Pilih Komponen</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier_id" class="form-label">Supplier</label>
                                <div class="input-group">
                                    <select class="form-select" id="supplier_id" name="supplier_id" required>
                                        <option value="">Pilih Supplier</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#tambahSupplierModal">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jumlah" class="form-label">Jumlah</label>
                                <input type="number" class="form-control" id="jumlah" name="jumlah" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanMaterial()">Simpan</button>
            </div>
        </div>
    </div>
</div>