// Load data kategori
function loadKategoriData() {
    fetch('/projects/kategori/list')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('tbodyKategori');
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center">Tidak ada data kategori</td></tr>';
                return;
            }
            
            data.forEach((kategori, index) => {
                tbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${kategori.Nama_Kelompok_Bahan}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editKategori(${kategori.ID_Kategori}, '${kategori.Nama_Kelompok_Bahan}')">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="hapusKategori(${kategori.ID_Kategori})">
                                Hapus
                            </button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(error => {
            console.error('Error loading kategori:', error);
        });
}

// Load data satuan
function loadSatuanData() {
    fetch('/projects/satuan/list')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('tbodySatuan');
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center">Tidak ada data satuan</td></tr>';
                return;
            }
            
            data.forEach((satuan, index) => {
                tbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${satuan.Nama_Satuan}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editSatuan(${satuan.ID_Satuan_Ukur}, '${satuan.Nama_Satuan}')">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="hapusSatuan(${satuan.ID_Satuan_Ukur})">
                                Hapus
                            </button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(error => {
            console.error('Error loading satuan:', error);
        });
}

// Simpan Kategori
function simpanKategori() {
    const namaKategori = document.getElementById('nama_kategori').value.trim();
    
    if (!namaKategori) {
        alert('Nama kategori harus diisi');
        return;
    }

    const formData = new FormData();
    formData.append('nama_kategori', namaKategori);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch('/projects/kategori/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('nama_kategori').value = '';
            loadKategoriData();
            loadDropdownData();
            alert('Kategori berhasil ditambahkan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error menambahkan kategori');
    });
}

// Simpan Satuan
function simpanSatuan() {
    const namaSatuan = document.getElementById('nama_satuan').value.trim();
    
    if (!namaSatuan) {
        alert('Nama satuan harus diisi');
        return;
    }

    const formData = new FormData();
    formData.append('nama_satuan', namaSatuan);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch('/projects/satuan/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('nama_satuan').value = '';
            loadSatuanData();
            loadDropdownData();
            alert('Satuan berhasil ditambahkan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error menambahkan satuan');
    });
}

// Load dropdown data
function loadDropdownData() {
    // Load bahan
    fetch('/api/bahan-list')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('bahan_id');
            if (select) {
                select.innerHTML = '<option value="">Pilih Bahan</option>';
                data.forEach(bahan => {
                    select.innerHTML += `<option value="${bahan.ID_Bahan}">${bahan.Nama_Bahan}</option>`;
                });
            }
        });

    // Load komponen
    fetch('/api/komponen-list')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('komponen_id');
            if (select) {
                select.innerHTML = '<option value="">Pilih Komponen</option>';
                data.forEach(komponen => {
                    select.innerHTML += `<option value="${komponen.ID_Komponen}">${komponen.Nama_Komponen}</option>`;
                });
            }
        });

    // Load supplier
    fetch('/api/supplier-list')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('supplier_id');
            if (select) {
                select.innerHTML = '<option value="">Pilih Supplier</option>';
                data.forEach(supplier => {
                    select.innerHTML += `<option value="${supplier.ID_Supplier}">${supplier.Nama_Supplier}</option>`;
                });
            }
        });

    // Load kategori untuk modal bahan
    fetch('/projects/kategori/list')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('kategori_id');
            if (select) {
                select.innerHTML = '<option value="">Pilih Kategori</option>';
                data.forEach(kategori => {
                    select.innerHTML += `<option value="${kategori.ID_Kategori}">${kategori.Nama_Kelompok_Bahan}</option>`;
                });
            }
        });

    // Load satuan untuk modal bahan
    fetch('/projects/satuan/list')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('satuan_id');
            if (select) {
                select.innerHTML = '<option value="">Pilih Satuan</option>';
                data.forEach(satuan => {
                    select.innerHTML += `<option value="${satuan.ID_Satuan_Ukur}">${satuan.Nama_Satuan}</option>`;
                });
            }
        });
}

// Simpan Material
function simpanMaterial() {
    const projectId = document.getElementById('formTambahMaterial').getAttribute('data-project-id');
    const formData = new FormData(document.getElementById('formTambahMaterial'));
    
    fetch(`/projects/${projectId}/materials`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#tambahMaterialModal').modal('hide');
            document.getElementById('formTambahMaterial').reset();
            alert('Material berhasil ditambahkan');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error menambahkan material');
    });
}

// Placeholder functions
function editKategori(id, nama) {
    const newNama = prompt('Edit nama kategori:', nama);
    if (newNama && newNama.trim() !== '') {
        alert('Fitur edit kategori akan segera tersedia');
    }
}

function hapusKategori(id) {
    if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
        alert('Fitur hapus kategori akan segera tersedia');
    }
}

function editSatuan(id, nama) {
    const newNama = prompt('Edit nama satuan:', nama);
    if (newNama && newNama.trim() !== '') {
        alert('Fitur edit satuan akan segera tersedia');
    }
}

function hapusSatuan(id) {
    if (confirm('Apakah Anda yakin ingin menghapus satuan ini?')) {
        alert('Fitur hapus satuan akan segera tersedia');
    }
}

function editMaterial(id) {
    alert('Edit material ID: ' + id);
}

function hapusMaterial(id) {
    if (confirm('Apakah Anda yakin ingin menghapus material ini?')) {
        alert('Hapus material ID: ' + id);
    }
}

function simpanBahan() {
    alert('Fitur tambah bahan akan segera tersedia');
}

function simpanSupplier() {
    alert('Fitur tambah supplier akan segera tersedia');
}

// Initialize event listeners ketika DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Modal Kategori
    const kategoriModal = document.getElementById('tambahKategoriModal');
    if (kategoriModal) {
        kategoriModal.addEventListener('show.bs.modal', function () {
            loadKategoriData();
        });
    }
    
    // Modal Satuan
    const satuanModal = document.getElementById('tambahSatuanModal');
    if (satuanModal) {
        satuanModal.addEventListener('show.bs.modal', function () {
            loadSatuanData();
        });
    }

    // Modal Material
    const materialModal = document.getElementById('tambahMaterialModal');
    if (materialModal) {
        materialModal.addEventListener('show.bs.modal', function () {
            loadDropdownData();
        });
    }

    // Modal Bahan
    const bahanModal = document.getElementById('tambahBahanModal');
    if (bahanModal) {
        bahanModal.addEventListener('show.bs.modal', function () {
            loadDropdownData();
        });
    }
});