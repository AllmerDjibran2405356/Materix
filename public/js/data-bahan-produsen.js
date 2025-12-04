// ========== GLOBAL VARIABLES ==========
let hargaCache = {}; // Cache untuk harga berdasarkan bahan dan supplier

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
    document.getElementById('inputIdSupplierAlamat').value = id;
    document.getElementById('inputIdSupplierKontak').value = id;
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
// ========== HARGA CACHE MANAGEMENT ==========
async function loadHargaCache() {
    try {
        const response = await fetch('/api/harga-cache');
        if (response.ok) {
            hargaCache = await response.json();
            console.log('Harga cache loaded:', hargaCache);

            // Debug: Cek cache untuk bahan-supplier tertentu
            const firstRow = document.querySelector('tr[data-harga-id]');
            if (firstRow) {
                const bahanId = firstRow.getAttribute('data-bahan-id');
                const supplierSelect = firstRow.querySelector('.supplier-select');
                if (supplierSelect) {
                    const supplierId = supplierSelect.getAttribute('data-original-supplier');
                    const cacheKey = `${bahanId}-${supplierId}`;
                    console.log(`Cache check for ${cacheKey}:`, hargaCache[cacheKey]);
                }
            }
        } else {
            console.error('Failed to load harga cache:', response.status);
        }
    } catch (error) {
        console.error('Failed to load harga cache:', error);
    }
}

function getHargaFromCache(bahanId, supplierId) {
    const key = `${bahanId}-${supplierId}`;
    console.log(`Getting harga from cache: ${key} =`, hargaCache[key]);
    return hargaCache[key] || null;
}

function updateHargaCache(bahanId, supplierId, harga) {
    const key = `${bahanId}-${supplierId}`;
    hargaCache[key] = harga;
    console.log(`Updated cache: ${key} =`, harga);
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

    hargaInputs.forEach(input => {
        const originalHarga = input.getAttribute('data-original-harga');
        const currentValue = input.value;

        console.log(`Initial state - Input: ${input.getAttribute('data-harga-id')}, Original: ${originalHarga}, Current: ${currentValue}`);

        // Pastikan nilai awal sesuai dengan data-original-harga
        if (currentValue && !input.value) {
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
            console.log(`Initial button state - ID: ${hargaId}, Disabled: ${button.disabled}`);
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

            console.log(`Supplier changed: ${originalSupplier} -> ${currentSupplier} for bahan ${bahanId}`);

            // Reset harga input
            hargaInput.value = '';
            hargaInput.placeholder = 'Loading...';

            if (currentSupplier) {
                // Cari harga dari cache atau database
                const cachedHarga = getHargaFromCache(bahanId, currentSupplier);

                console.log(`Cached harga for ${bahanId}-${currentSupplier}:`, cachedHarga);

                if (cachedHarga !== null && cachedHarga !== undefined) {
                    // Gunakan harga dari cache
                    hargaInput.value = cachedHarga;
                    hargaInput.placeholder = '';
                    updateHargaInputState(hargaInput, cachedHarga);
                    console.log(`Loaded from cache: ${cachedHarga}`);
                } else {
                    console.log(`Cache miss, fetching from API...`);
                    // Fetch dari API
                    try {
                        const response = await fetch(`/api/harga/${bahanId}/${currentSupplier}`);
                        console.log(`API response status: ${response.status}`);

                        if (response.ok) {
                            const data = await response.json();
                            console.log('API response data:', data);

                            if (data.success && data.harga !== null && data.harga !== undefined) {
                                hargaInput.value = data.harga;
                                hargaInput.placeholder = '';
                                updateHargaCache(bahanId, currentSupplier, data.harga);
                                console.log(`Loaded from API: ${data.harga}`);
                            } else {
                                hargaInput.placeholder = 'Kosong';
                                hargaInput.value = '';
                                console.log('No harga found in API response');
                            }
                        } else {
                            hargaInput.placeholder = 'Kosong';
                            hargaInput.value = '';
                            console.error('API request failed:', response.status);
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

                console.log(`Simpan button - Supplier changed: ${isSupplierChanged}, Has harga: ${hasHarga}, Disabled: ${simpanBtn.disabled}`);

                // Tampilkan indicator
                if (isSupplierChanged) {
                    this.classList.add('is-changed');
                } else {
                    this.classList.remove('is-changed');
                }
            }
        });

        // Panggil event change untuk set harga awal berdasarkan supplier yang dipilih
        setTimeout(() => {
            if (select.value) {
                select.dispatchEvent(new Event('change'));
            }
        }, 500);
    });

    // Handle perubahan input harga
    // Handle perubahan input harga
hargaInputs.forEach(input => {
    input.addEventListener('input', function() {
        const hargaId = this.getAttribute('data-harga-id');
        const originalHarga = this.getAttribute('data-original-harga');
        const currentHarga = this.value;
        const simpanBtn = document.querySelector(`.btn-simpan-harga[data-harga-id="${hargaId}"]`);

        console.log(`Harga input changed - ID: ${hargaId}, Original: ${originalHarga}, Current: ${currentHarga}`);

        updateHargaInputState(this, originalHarga);

        if (simpanBtn) {
            const supplierSelect = document.querySelector(`.supplier-select[data-harga-id="${hargaId}"]`);
            const originalSupplier = supplierSelect ? supplierSelect.getAttribute('data-original-supplier') : '';
            const currentSupplier = supplierSelect ? supplierSelect.value : '';

            const isSupplierChanged = currentSupplier !== originalSupplier;
            const isHargaChanged = currentHarga !== originalHarga;

            console.log(`Simpan button check - Supplier changed: ${isSupplierChanged}, Harga changed: ${isHargaChanged}`);

            // Enable tombol jika ada perubahan supplier atau harga
            simpanBtn.disabled = !(isSupplierChanged || isHargaChanged);
            console.log(`Simpan button disabled: ${simpanBtn.disabled}`);
        }
    });

    // Validasi saat blur
    input.addEventListener('blur', function() {
        const value = parseFloat(this.value) || 0;
        const originalHarga = this.getAttribute('data-original-harga');
        const originalHargaNum = parseFloat(originalHarga) || 0;

        console.log(`Harga blur - Value: ${value}, Original: ${originalHargaNum}`);

        if (value < 1 && value !== 0) {
            this.value = originalHargaNum || '';
            this.placeholder = originalHargaNum ? '' : 'Kosong';
            this.classList.remove('is-changed');
            console.log('Reset to original harga');

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

    console.log(`updateHargaInputState - Current: ${currentHarga}, Original: ${originalHargaNum}, Equal: ${currentHarga === originalHargaNum}`);

    // Hapus class is-changed jika tidak ada perubahan
    if (currentHarga === originalHargaNum) {
        input.classList.remove('is-changed');
        console.log('Removed is-changed class');
    } else if (currentHarga >= 1) {
        input.classList.add('is-changed');
        console.log('Added is-changed class');
    } else {
        input.classList.remove('is-changed');
        console.log('Removed is-changed class (invalid harga)');
    }
}

// Fungsi untuk menyimpan perubahan harga
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
    const originalContent = simpanBtn.innerHTML;
    simpanBtn.innerHTML = '<span class="loading-spinner"></span>';
    simpanBtn.disabled = true;

    try {
        // PERBAIKAN: Gunakan route yang benar
        const url = `/harga-bahan/${hargaId}`;
        console.log('Sending PUT request to:', url);

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

        console.log('Response status:', response.status);

        // Coba parse response sebagai JSON
        let data;
        try {
            data = await response.json();
            console.log('Response data:', data);
        } catch (jsonError) {
            console.error('Error parsing JSON:', jsonError);
            throw new Error('Invalid response from server');
        }

        if (response.ok && data.success) {
            // Update nilai original
            hargaInput.setAttribute('data-original-harga', newHarga);
            supplierSelect.setAttribute('data-original-supplier', newSupplierId);

            // Update cache
            updateHargaCache(bahanId, newSupplierId, newHarga);

            // Reset indicator
            hargaInput.classList.remove('is-changed');
            supplierSelect.classList.remove('is-changed');

            // Update tombol
            simpanBtn.innerHTML = '<i class="bi bi-check-lg"></i>';
            simpanBtn.classList.remove('btn-success');
            simpanBtn.classList.add('btn-outline-success');

            // Tampilkan notifikasi sukses
            showToast('success', data.message || 'Perubahan berhasil disimpan!');

            // Reset setelah 2 detik
            setTimeout(() => {
                simpanBtn.innerHTML = originalContent;
                simpanBtn.classList.remove('btn-outline-success');
                simpanBtn.classList.add('btn-success');
                simpanBtn.disabled = true;
            }, 2000);

        } else {
            throw new Error(data.message || `Gagal menyimpan perubahan. Status: ${response.status}`);
        }
    } catch (error) {
        console.error('Error:', error);
        simpanBtn.innerHTML = originalContent;
        simpanBtn.disabled = false;
        showToast('error', 'Gagal menyimpan perubahan: ' + error.message);

        // Debug tambahan
        console.log('Request details:', {
            hargaId,
            bahanId,
            newHarga,
            newSupplierId
        });
    }
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

            if (!nama) {
                e.preventDefault();
                showToast('error', 'Nama supplier tidak boleh kosong');
                this.querySelector('input[name="Nama_Supplier"]')?.focus();
                return false;
            }
            if (!alamat) {
                e.preventDefault();
                showToast('error', 'Alamat supplier tidak boleh kosong');
                this.querySelector('input[name="Alamat_Supplier"]')?.focus();
                return false;
            }
            if (!kontak) {
                e.preventDefault();
                showToast('error', 'Kontak supplier tidak boleh kosong');
                this.querySelector('input[name="Kontak_Supplier"]')?.focus();
                return false;
            }
            return true;
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
                        window.location.reload();
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
                        window.location.reload();
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

// ========== EVENT LISTENERS ==========
document.addEventListener('DOMContentLoaded', function() {
    console.log('Data Bahan dan Produsen script loaded');

    // Initialize semua handler
    initializeFormValidations();
    initializeDeleteHandlers();
    initializeEditHandlers();

    // Initialize harga table saat modal bahan dibuka
    const modalBahan = document.getElementById('modalBahan');
    if (modalBahan) {
        modalBahan.addEventListener('shown.bs.modal', function() {
            setTimeout(() => {
                initializeHargaTable();
            }, 100);
        });
    }

    // Auto-hide toast
    setTimeout(() => {
        document.querySelectorAll('.toast').forEach(toast => {
            const bsToast = new bootstrap.Toast(toast);
            bsToast.hide();
        });
    }, 5000);
});
