<div class="modal fade" id="tambahSatuanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Satuan Ukur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahSatuan">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="nama_satuan" class="form-label">Nama Satuan</label>
                                <input type="text" class="form-control" id="nama_satuan" name="nama_satuan" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-primary w-100" onclick="simpanSatuan()">
                                    Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="mt-4">
                    <h6>Daftar Satuan Ukur</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Satuan</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodySatuan">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>