<div class="d-flex gap-2 mb-3 flex-wrap">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahMaterialModal">
        Tambah Material
    </button>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahBahanModal">
        Tambah Bahan
    </button>
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#tambahKategoriModal">
        Tambah Kategori
    </button>
    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#tambahSatuanModal">
        Tambah Satuan
    </button>
    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#tambahSupplierModal">
        Tambah Supplier
    </button>
    
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('materials.export.pdf', $project->ID_Desain_Rumah) }}" class="btn btn-danger">
            Export PDF
        </a>
        <a href="{{ route('materials.export.excel', $project->ID_Desain_Rumah) }}" class="btn btn-success">
            Export Excel
        </a>
    </div>
</div>