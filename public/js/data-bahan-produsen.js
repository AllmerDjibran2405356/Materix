// ========== GLOBAL VARIABLES ==========
let hargaCache = {}; // Cache untuk harga berdasarkan bahan dan supplier
let originalTableHTML = null; // Simpan HTML asli tabel untuk fallback

// ========== UTILITY FUNCTIONS ==========
window.formatNumber = function(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

window.formatHarga = function(harga) {
    return 'Rp ' + formatNumber(harga);
};

window.isValidNumber = function(value) {
    return !isNaN(value) && parseInt(value) > 0;
};

// Fungsi untuk set ID Supplier ke form alamat/kontak
window.setSupplierId = function(id) {
    const alamatInput = document.getElementById('inputIdSupplierAlamat');
    const kontakInput = document.getElementById('inputIdSupplierKontak');

    if (alamatInput) alamatInput.value = id;
    if (kontakInput) kontakInput.value = id;
};

// ========== TOAST NOTIFICATION ==========
function showToast(type, message) {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();

    const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
    const color = type === 'success' ? 'success' : 'danger';

    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-${color} text-white">
                <i class="bi ${icon} me-2"></i>
                <strong class="me-auto">${type === 'success' ? 'Sukses' : 'Error'}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 3000
    });

    toast.show();

    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// ========== HARGA CACHE MANAGEMENT ==========
async function loadHargaCache() {
    try {
        const response = await fetch('/api/harga-cache');
        if (response.ok) {
            hargaCache = await response.json();
            console.log('Harga cache loaded:', Object.keys(hargaCache).length, 'items');
        } else {
            console.error('Failed to load harga cache:', response.status);
        }
    } catch (error) {
        console.error('Failed to load harga cache:', error);
    }
}

function getHargaFromCache(bahanId, supplierId) {
    const key = `${bahanId}-${supplierId}`;
    return hargaCache[key] || null;
}

function updateHargaCache(bahanId, supplierId, harga) {
    const key = `${bahanId}-${supplierId}`;
    hargaCache[key] = harga;
}

// ========== HANDLE TABEL HARGA DENGAN AUTO-UPDATE ==========
function initializeHargaTable() {
    console.log('Initializing harga table...');

    const hargaInputs = document.querySelectorAll('.harga-input');
    const supplierSelects = document.querySelectorAll('.supplier-select');
    const simpanButtons = document.querySelectorAll('.btn-simpan-harga');

    console.log(`Found ${hargaInputs.length} harga inputs, ${supplierSelects.length} supplier selects`);

    // Load cache saat pertama kali
    loadHargaCache();

    // Set initial state untuk semua input harga
    hargaInputs.forEach(input => {
        const originalHarga = input.getAttribute('data-original-harga');
        const currentValue = input.value;

        // Pastikan nilai awal sesuai dengan data-original-harga
        if (!currentValue && originalHarga) {
            input.value = originalHarga;
        }

        // Update state awal
        updateHargaInputState(input, originalHarga);
    });

    // Set initial state untuk semua tombol simpan
    simpanButtons.forEach(button => {
        const hargaId = button.getAttribute('data-harga-id');
        const supplierSelect = document.querySelector(`.supplier-select[data-harga-id="${hargaId}"]`);
        const hargaInput = document.querySelector(`.harga-input[data-harga-id="${hargaId}"]`);

        if (supplierSelect && hargaInput) {
            const originalSupplier = supplierSelect.getAttribute('data-original-supplier');
            const currentSupplier = supplierSelect.value;
            const originalHarga = hargaInput.getAttribute('data-original-harga');
            const currentHarga = hargaInput.value;

            const isSupplierChanged = currentSupplier !== originalSupplier;
            const isHargaChanged = currentHarga !== originalHarga;

            button.disabled = !(isSupplierChanged || isHargaChanged);
        }
    });

    // Handle perubahan dropdown supplier
    supplierSelects.forEach(select => {
        select.addEventListener('change', async function() {
            const hargaId = this.getAttribute('data-harga-id');
            const bahanId = this.getAttribute('data-bahan-id');
            const originalSupplier = this.getAttribute('data-original-supplier');
            const currentSupplier = this.value;
            const row = this.closest('tr');
            const hargaInput = row.querySelector('.harga-input');
            const simpanBtn = row.querySelector('.btn-simpan-harga');

            // Reset harga input
            hargaInput.value = '';
            hargaInput.placeholder = 'Loading...';

            if (currentSupplier) {
                // Cari harga dari cache atau database
                const cachedHarga = getHargaFromCache(bahanId, currentSupplier);

                if (cachedHarga !== null && cachedHarga !== undefined) {
                    // Gunakan harga dari cache
                    hargaInput.value = cachedHarga;
                    hargaInput.placeholder = '';
                    updateHargaInputState(hargaInput, cachedHarga);
                } else {
                    // Fetch dari API
                    try {
                        const response = await fetch(`/api/harga/${bahanId}/${currentSupplier}`);

                        if (response.ok) {
                            const data = await response.json();

                            if (data.success && data.harga !== null && data.harga !== undefined) {
                                hargaInput.value = data.harga;
                                hargaInput.placeholder = '';
                                updateHargaCache(bahanId, currentSupplier, data.harga);
                                updateHargaInputState(hargaInput, data.harga);
                            } else {
                                hargaInput.placeholder = 'Kosong';
                                hargaInput.value = '';
                            }
                        } else {
                            hargaInput.placeholder = 'Kosong';
                            hargaInput.value = '';
                        }
                    } catch (error) {
                        console.error('Error fetching harga:', error);
                        hargaInput.placeholder = 'Kosong';
                        hargaInput.value = '';
                    }
                }
            } else {
                hargaInput.placeholder = 'Kosong';
                hargaInput.value = '';
            }

            // Update tombol simpan state
            if (simpanBtn) {
                const isSupplierChanged = currentSupplier !== originalSupplier;
                const hasHarga = hargaInput.value && parseFloat(hargaInput.value) >= 1;
                simpanBtn.disabled = !(isSupplierChanged || hasHarga);

                // Tampilkan indicator
                if (isSupplierChanged) {
                    this.classList.add('is-changed');
                } else {
                    this.classList.remove('is-changed');
                }
            }
        });

        // Trigger change event untuk supplier yang sudah terpilih
        if (select.value) {
            setTimeout(() => {
                select.dispatchEvent(new Event('change'));
            }, 300);
        }
    });

    // Handle perubahan input harga
    hargaInputs.forEach(input => {
        input.addEventListener('input', function() {
            const hargaId = this.getAttribute('data-harga-id');
            const originalHarga = this.getAttribute('data-original-harga');
            const currentHarga = this.value;
            const simpanBtn = document.querySelector(`.btn-simpan-harga[data-harga-id="${hargaId}"]`);

            updateHargaInputState(this, originalHarga);

            if (simpanBtn) {
                const supplierSelect = document.querySelector(`.supplier-select[data-harga-id="${hargaId}"]`);
                const originalSupplier = supplierSelect ? supplierSelect.getAttribute('data-original-supplier') : '';
                const currentSupplier = supplierSelect ? supplierSelect.value : '';

                const isSupplierChanged = currentSupplier !== originalSupplier;
                const isHargaChanged = currentHarga !== originalHarga;

                // Enable tombol jika ada perubahan supplier atau harga
                simpanBtn.disabled = !(isSupplierChanged || isHargaChanged);
            }
        });

        // Validasi saat blur
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value) || 0;
            const originalHarga = this.getAttribute('data-original-harga');
            const originalHargaNum = parseFloat(originalHarga) || 0;

            if (value < 1 && value !== 0) {
                this.value = originalHargaNum || '';
                this.placeholder = originalHargaNum ? '' : 'Kosong';
                this.classList.remove('is-changed');

                const hargaId = this.getAttribute('data-harga-id');
                const simpanBtn = document.querySelector(`.btn-simpan-harga[data-harga-id="${hargaId}"]`);
                if (simpanBtn) simpanBtn.disabled = true;
            }

            updateHargaInputState(this, originalHarga);
        });

        // Set initial state saat modal dibuka
        const originalHarga = input.getAttribute('data-original-harga');
        updateHargaInputState(input, originalHarga);
    });

    // Handle tombol simpan
    simpanButtons.forEach(button => {
        button.addEventListener('click', function() {
            const hargaId = this.getAttribute('data-harga-id');
            const bahanId = this.getAttribute('data-bahan-id');
            saveHargaChanges(hargaId, bahanId);
        });
    });
}

function updateHargaInputState(input, originalHarga) {
    const currentHarga = parseFloat(input.value) || 0;
    const originalHargaNum = parseFloat(originalHarga) || 0;

    // Hapus class is-changed jika tidak ada perubahan
    if (currentHarga === originalHargaNum) {
        input.classList.remove('is-changed');
    } else if (currentHarga >= 1) {
        input.classList.add('is-changed');
    } else {
        input.classList.remove('is-changed');
    }
}

// Fungsi untuk menyimpan perubahan harga
async function saveHargaChanges(hargaId, bahanId) {
    const row = document.querySelector(`tr[data-harga-id="${hargaId}"]`);
    if (!row) {
        showToast('error', 'Data tidak ditemukan!');
        return;
    }

    const hargaInput = row.querySelector('.harga-input');
    const supplierSelect = row.querySelector('.supplier-select');
    const simpanBtn = row.querySelector('.btn-simpan-harga');

    if (!hargaInput || !supplierSelect) {
        showToast('error', 'Form tidak lengkap!');
        return;
    }

    const newHarga = parseFloat(hargaInput.value) || 0;
    const newSupplierId = supplierSelect.value;

    // Validasi
    if (!newSupplierId) {
        showToast('error', 'Pilih supplier terlebih dahulu!');
        supplierSelect.focus();
        return;
    }

    if (newHarga < 1) {
        showToast('error', 'Harga harus lebih dari 0!');
        hargaInput.focus();
        return;
    }

    // Tampilkan loading
    const originalBtnContent = simpanBtn.innerHTML;
    simpanBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    simpanBtn.disabled = true;

    try {
        const url = `/harga-bahan/${hargaId}`;

        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ID_Bahan: bahanId,
                ID_Supplier: newSupplierId,
                Harga_Per_Satuan: newHarga
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Update UI lokal
            hargaInput.setAttribute('data-original-harga', newHarga);
            supplierSelect.setAttribute('data-original-supplier', newSupplierId);

            // Update cache
            updateHargaCache(bahanId, newSupplierId, newHarga);

            // Reset UI
            hargaInput.classList.remove('is-changed');
            supplierSelect.classList.remove('is-changed');

            // Tampilkan success message
            showToast('success', data.message || 'Harga berhasil diperbarui!');

            // Refresh tabel utama setelah 1 detik
            setTimeout(async () => {
                try {
                    await refreshMainTable();

                    // Update tombol simpan setelah refresh
                    const newSimpanBtn = document.querySelector(`.btn-simpan-harga[data-harga-id="${hargaId}"]`);
                    if (newSimpanBtn) {
                        newSimpanBtn.innerHTML = '<i class="bi bi-save"></i>';
                        newSimpanBtn.disabled = true;
                        newSimpanBtn.title = 'Simpan Perubahan';
                    }
                } catch (refreshError) {
                    console.error('Refresh failed:', refreshError);
                    showToast('info', 'Data disimpan. Refresh halaman untuk melihat perubahan lengkap.');
                }
            }, 1000);

        } else {
            throw new Error(data.message || 'Server returned error');
        }

    } catch (error) {
        console.error('Save error:', error);

        // Reset tombol
        simpanBtn.innerHTML = originalBtnContent;
        simpanBtn.disabled = false;

        showToast('error', 'Gagal menyimpan: ' + error.message);
    }
}

// ========== FUNGSI REFRESH TABEL UTAMA ==========
async function refreshMainTable() {
    try {
        console.log('Refreshing main table...');

        // Tampilkan loading indicator pada tbody saja
        const tableBody = document.querySelector('#mainTable tbody');
        if (!tableBody) {
            console.error('Table body not found');
            return;
        }

        // Simpan original content
        const originalContent = tableBody.innerHTML;

        // Tampilkan loading
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span>Memperbarui data...</span>
                    </div>
                </td>
            </tr>
        `;

        // Ambil project ID dari URL
        const currentPath = window.location.pathname;
        const projectIdMatch = currentPath.match(/\/data-proyek\/(\d+)/);

        if (!projectIdMatch) {
            console.error('Cannot find project ID in URL');
            tableBody.innerHTML = originalContent;
            return;
        }

        const projectId = projectIdMatch[1];
        const refreshUrl = `/data-proyek/${projectId}/refresh?_=${Date.now()}`;

        // Fetch data
        const response = await fetch(refreshUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        if (data.success && data.html) {
            // Update hanya tbody
            tableBody.innerHTML = data.html;

            // Re-attach event listeners untuk form
            reattachFormListeners();

            console.log('Table updated successfully');
            showToast('success', 'Data berhasil diperbarui');
        } else {
            throw new Error(data.message || 'Invalid response');
        }

    } catch (error) {
        console.error('Error refreshing main table:', error);

        // Restore original content
        const tableBody = document.querySelector('#mainTable tbody');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger py-4">
                        <i class="bi bi-exclamation-triangle"></i>
                        Gagal memuat data terbaru
                        <br>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh Halaman
                        </button>
                    </td>
                </tr>
            `;
        }

        showToast('error', 'Gagal memperbarui: ' + error.message);
    }
}

// ========== REATTACH FORM LISTENERS ==========
function reattachFormListeners() {
    // Re-attach event listeners untuk semua form supplier di tabel utama
    document.querySelectorAll('#mainTable form.supplier-form').forEach(form => {
        // Hapus event listener lama
        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);

        const select = newForm.querySelector('select[name="ID_Supplier"]');

        if (select) {
            select.addEventListener('change', function() {
                const recapId = this.getAttribute('data-recap-id');
                const bahanId = this.getAttribute('data-bahan-id');
                const supplierId = this.value;

                if (!supplierId) {
                    showToast('error', 'Pilih supplier terlebih dahulu');
                    return;
                }

                // Tampilkan loading
                const originalText = this.options[this.selectedIndex].text;
                this.options[this.selectedIndex].text = 'Menyimpan...';
                this.disabled = true;

                // Kirim data
                const formData = new FormData(newForm);
                formData.append('ID_Rekap', recapId);
                formData.append('ID_Supplier', supplierId);

                fetch(newForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', data.message || 'Supplier berhasil diperbarui');
                        // Refresh tabel setelah 500ms
                        setTimeout(() => refreshMainTable(), 500);
                    } else {
                        showToast('error', data.message || 'Gagal mengubah supplier');
                        // Reset select
                        this.options[this.selectedIndex].text = originalText;
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Terjadi kesalahan saat menyimpan');
                    // Reset select
                    this.options[this.selectedIndex].text = originalText;
                    this.disabled = false;
                });
            });
        }
    });

    console.log('Supplier form listeners re-attached');
}

// ========== FORM VALIDATIONS ==========
function initializeFormValidations() {
    // Form tambah harga
    const formHarga = document.getElementById('formHarga');
    if (formHarga) {
        formHarga.setAttribute('novalidate', 'novalidate');
        formHarga.addEventListener('submit', function(e) {
            const harga = document.getElementById('inputHarga')?.value.trim();
            if (!harga || isNaN(harga) || parseInt(harga) < 1) {
                e.preventDefault();
                showToast('error', 'Masukkan harga yang valid (minimal Rp 1)');
                document.getElementById('inputHarga')?.focus();
                return false;
            }
            return true;
        });
    }

    // Form edit supplier
    const formEditSupplier = document.getElementById('formEditSupplier');
    if (formEditSupplier) {
        formEditSupplier.setAttribute('novalidate', 'novalidate');
        formEditSupplier.addEventListener('submit', function(e) {
            const namaSupplier = document.getElementById('editNamaSupplier')?.value.trim();
            if (!namaSupplier) {
                e.preventDefault();
                showToast('error', 'Nama supplier tidak boleh kosong');
                document.getElementById('editNamaSupplier')?.focus();
                return false;
            }
            return confirm('Apakah Anda yakin ingin mengupdate data supplier ini?');
        });
    }

    // Form tambah alamat
    const formTambahAlamat = document.getElementById('formTambahAlamat');
    if (formTambahAlamat) {
        formTambahAlamat.setAttribute('novalidate', 'novalidate');
        formTambahAlamat.addEventListener('submit', function(e) {
            const alamat = formTambahAlamat.querySelector('textarea[name="Alamat_Supplier"]')?.value.trim();
            if (!alamat) {
                e.preventDefault();
                showToast('error', 'Alamat supplier tidak boleh kosong');
                formTambahAlamat.querySelector('textarea[name="Alamat_Supplier"]')?.focus();
                return false;
            }
            return true;
        });
    }

    // Form tambah kontak
    const formTambahKontak = document.getElementById('formTambahKontak');
    if (formTambahKontak) {
        formTambahKontak.setAttribute('novalidate', 'novalidate');
        formTambahKontak.addEventListener('submit', function(e) {
            const kontak = formTambahKontak.querySelector('input[name="Kontak_Supplier"]')?.value.trim();
            if (!kontak) {
                e.preventDefault();
                showToast('error', 'Kontak supplier tidak boleh kosong');
                formTambahKontak.querySelector('input[name="Kontak_Supplier"]')?.focus();
                return false;
            }
            return true;
        });
    }

    // Form tambah supplier
    const formTambahSupplier = document.querySelector('#modalTambahSupplier form');
    if (formTambahSupplier) {
        formTambahSupplier.setAttribute('novalidate', 'novalidate');
        formTambahSupplier.addEventListener('submit', function(e) {
            const nama = this.querySelector('input[name="Nama_Supplier"]')?.value.trim();
            const alamat = this.querySelector('input[name="Alamat_Supplier"]')?.value.trim();
            const kontak = this.querySelector('input[name="Kontak_Supplier"]')?.value.trim();

            let hasError = false;

            if (!nama) {
                e.preventDefault();
                showToast('error', 'Nama supplier tidak boleh kosong');
                this.querySelector('input[name="Nama_Supplier"]')?.focus();
                hasError = true;
            }
            if (!alamat && !hasError) {
                e.preventDefault();
                showToast('error', 'Alamat supplier tidak boleh kosong');
                this.querySelector('input[name="Alamat_Supplier"]')?.focus();
                hasError = true;
            }
            if (!kontak && !hasError) {
                e.preventDefault();
                showToast('error', 'Kontak supplier tidak boleh kosong');
                this.querySelector('input[name="Kontak_Supplier"]')?.focus();
                hasError = true;
            }

            return !hasError;
        });
    }
}

// ========== DELETE HANDLERS ==========
function initializeDeleteHandlers() {
    // Hapus alamat
    document.querySelectorAll('.btn-hapus-alamat').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const alamatId = this.getAttribute('data-id');
            const supplierNama = this.getAttribute('data-nama');

            if (confirm(`Apakah Anda yakin ingin menghapus alamat ini dari supplier "${supplierNama}"?`)) {
                fetch(`/supplier/alamat/${alamatId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        showToast('success', 'Alamat berhasil dihapus');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Gagal menghapus alamat');
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Gagal menghapus alamat: ' + error.message);
                });
            }
        });
    });

    // Hapus kontak
    document.querySelectorAll('.btn-hapus-kontak').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const kontakId = this.getAttribute('data-id');
            const supplierNama = this.getAttribute('data-nama');

            if (confirm(`Apakah Anda yakin ingin menghapus kontak ini dari supplier "${supplierNama}"?`)) {
                fetch(`/supplier/kontak/${kontakId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        showToast('success', 'Kontak berhasil dihapus');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Gagal menghapus kontak');
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Gagal menghapus kontak: ' + error.message);
                });
            }
        });
    });
}

// ========== EDIT HANDLERS ==========
function initializeEditHandlers() {
    // Edit supplier
    document.querySelectorAll('.btn-edit-supplier').forEach(button => {
        button.addEventListener('click', function() {
            const supplierId = this.getAttribute('data-id');
            loadSupplierData(supplierId);
        });
    });

    // Edit harga (modal lama - jika masih ada)
    document.querySelectorAll('.btn-edit-harga').forEach(button => {
        button.addEventListener('click', function() {
            const hargaId = this.getAttribute('data-id');
            loadHargaData(hargaId);
        });
    });
}

async function loadSupplierData(supplierId) {
    try {
        const response = await fetch(`/supplier/${supplierId}/edit`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();
        if (data.success) {
            const supplier = data.data;
            const form = document.getElementById('formEditSupplier');
            if (form) {
                form.action = `/supplier/${supplierId}`;
                document.getElementById('editSupplierId').value = supplier.id;
                document.getElementById('editNamaSupplier').value = supplier.nama;
            }
        } else {
            showToast('error', 'Gagal memuat data supplier: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Terjadi kesalahan saat memuat data supplier: ' + error.message);
    }
}

async function loadHargaData(hargaId) {
    try {
        const response = await fetch(`/harga-bahan/${hargaId}/edit`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();
        if (data.success) {
            const harga = data.data;
            const form = document.getElementById('formEditHarga');
            if (form) {
                form.action = `/harga-bahan/${hargaId}`;
                document.getElementById('editHargaId').value = harga.id;
                document.getElementById('editNamaBahan').value = harga.nama_bahan;
                document.getElementById('editNamaSupplier').value = harga.nama_supplier;
                document.getElementById('editHargaPerSatuan').value = harga.harga;
            }
        } else {
            showToast('error', 'Gagal memuat data harga: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Terjadi kesalahan saat memuat data: ' + error.message);
    }
}

// ========== MODAL INITIALIZATION ==========
function initializeModals() {
    // Modal supplier - reattach listeners saat modal dibuka
    const modalSupplier = document.getElementById('modalSupplier');
    if (modalSupplier) {
        modalSupplier.addEventListener('shown.bs.modal', function() {
            setTimeout(() => {
                initializeDeleteHandlers();
                initializeEditHandlers();
            }, 100);
        });
    }

    // Modal bahan - initialize harga table
    const modalBahan = document.getElementById('modalBahan');
    if (modalBahan) {
        modalBahan.addEventListener('shown.bs.modal', function() {
            setTimeout(() => {
                initializeHargaTable();
            }, 100);
        });
    }
}

// ========== INITIALIZE TABLE REFRESH ==========
function initializeTableRefresh() {
    const mainTable = document.querySelector('.card .table-responsive table');
    if (mainTable) {
        originalTableHTML = mainTable.innerHTML;
        console.log('Original table HTML saved for fallback');
    }
}

// ========== HIGHLIGHT UPDATED ROW ==========
function highlightUpdatedRow(bahanId, supplierId) {
    // Cari row di tabel utama yang sesuai dengan bahan dan supplier
    const rows = document.querySelectorAll('#mainTable tbody tr');

    rows.forEach(row => {
        const bahanCell = row.querySelector('td:nth-child(2)');
        const supplierSelect = row.querySelector('select[name="ID_Supplier"]');

        if (bahanCell && supplierSelect) {
            const bahanText = bahanCell.textContent.trim();
            const supplierValue = supplierSelect.value;

            // Sesuaikan dengan struktur data Anda
            if (supplierValue == supplierId) {
                row.classList.add('highlight-update');

                // Hapus highlight setelah 3 detik
                setTimeout(() => {
                    row.classList.remove('highlight-update');
                }, 3000);
            }
        }
    });
}

// ========== INITIALIZE ALL FUNCTIONALITY ==========
function initializeAll() {
    console.log('Initializing Data Bahan dan Produsen...');

    // Initialize table refresh
    initializeTableRefresh();

    // Initialize form validations
    initializeFormValidations();

    // Initialize delete handlers
    initializeDeleteHandlers();

    // Initialize edit handlers
    initializeEditHandlers();

    // Initialize modals
    initializeModals();

    // Attach supplier form listeners saat pertama kali load
    reattachFormListeners();

    console.log('Initialization complete');
}

// ========== EVENT LISTENERS ==========
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Data Bahan dan Produsen');

    // Initialize semua functionality
    initializeAll();

    // Auto-hide toast yang ada
    setTimeout(() => {
        document.querySelectorAll('.toast').forEach(toast => {
            const bsToast = new bootstrap.Toast(toast);
            bsToast.hide();
        });
    }, 5000);
});

// ========== EXPORT FUNCTIONS FOR GLOBAL USE ==========
window.refreshMainTable = refreshMainTable;
window.showToast = showToast;
window.setSupplierId = setSupplierId;
window.initializeHargaTable = initializeHargaTable;

// ========== DEBUG HELPER ==========
window.debugRefresh = async function() {
    const currentPath = window.location.pathname;
    const projectIdMatch = currentPath.match(/\/data-proyek\/(\d+)/);

    if (!projectIdMatch) {
        console.error('Cannot find project ID in URL');
        return;
    }

    const projectId = projectIdMatch[1];
    const url = `/data-proyek/${projectId}/refresh`;

    console.log('Debug URL:', url);

    try {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        console.log('Response status:', response.status);
        console.log('Content-Type:', response.headers.get('content-type'));

        const text = await response.text();
        console.log('Raw response (first 500 chars):', text.substring(0, 500));

        try {
            const json = JSON.parse(text);
            console.log('Parsed JSON:', json);
        } catch(e) {
            console.log('Not valid JSON:', e.message);
        }

    } catch(error) {
        console.error('Test error:', error);
    }
};

console.log('Data Bahan dan Produsen JavaScript loaded successfully');
