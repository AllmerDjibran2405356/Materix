<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Bahan</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th>Komponen</th>
                <th>Supplier</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Total Harga</th>
                <th width="120">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materials as $index => $material)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $material->bahan->Nama_Bahan ?? '-' }}</td>
                <td>{{ $material->bahan->kategori->Nama_Kelompok_Bahan ?? '-' }}</td>
                <td>{{ $material->bahan->satuanUkur->Nama_Satuan ?? '-' }}</td>
                <td>{{ $material->komponen->Nama_Komponen ?? '-' }}</td>
                <td>{{ $material->supplier->Nama_Supplier ?? '-' }}</td>
                <td>{{ number_format($material->Jumlah, 2) }}</td>
                <td>Rp {{ number_format($material->bahan->hargaBahan->first()->Harga_Per_Satuan ?? 0, 2) }}</td>
                <td>Rp {{ number_format(($material->Jumlah * ($material->bahan->hargaBahan->first()->Harga_Per_Satuan ?? 0)), 2) }}</td>
                <td>
                    <button type="button" class="btn btn-warning btn-sm" onclick="editMaterial({{ $material->id }})">
                        Edit
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="hapusMaterial({{ $material->id }})">
                        Hapus
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Tidak ada data material</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>