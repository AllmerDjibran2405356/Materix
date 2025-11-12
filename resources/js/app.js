import './bootstrap';
// ... (Kode setupPasswordToggle kamu yang lama ada di atas) ...

document.addEventListener('DOMContentLoaded', function() {
    // ... (Pemanggilan setupPasswordToggle kamu ada di sini) ...
    setupPasswordToggle('toggleSandiLama', 'sandiLama');
    setupPasswordToggle('toggleSandiBaru', 'sandiBaru');
    setupPasswordToggle('toggleUlangiSandiBaru', 'ulangiSandiBaru');


    // ▼▼▼ HAPUS LOGIKA LAMA 'btnLanjutSandi' DAN GANTI DENGAN YANG INI ▼▼▼

   const lanjutButton = document.getElementById('btnLanjutSandi');
    
    // Ambil elemen modal Bootstrap (ini aman di luar)
    const cekSandiModalEl = document.getElementById('cekSandiLamaModal');
    const cekSandiModal = bootstrap.Modal.getInstance(cekSandiModalEl) || new bootstrap.Modal(cekSandiModalEl);
    
    const sandiBaruModalEl = document.getElementById('sandiBaruModal');
    const sandiBaruModal = bootstrap.Modal.getInstance(sandiBaruModalEl) || new bootstrap.Modal(sandiBaruModalEl);

    // Ambil elemen error (ini aman di luar)
    const sandiLamaError = document.getElementById('sandiLamaError');

    // 
    // const csrfToken = ... <-- BARIS INI KITA HAPUS DARI SINI
    // 

    if (lanjutButton) {
        lanjutButton.addEventListener('click', async function() {

            // ▼▼▼ KITA PINDAHKAN PENCARIAN TOKEN KE SINI ▼▼▼
            // Sekarang ini hanya berjalan SAAT DIKLIK, pasti aman.
            const csrfToken = document.querySelector('#formUbahAkun input[name="_token"]').value;

            // 1. Ambil nilai sandi lama
            const sandiLamaInput = document.getElementById('sandiLama');
            const sandiLamaValue = sandiLamaInput.value;

            // 2. Sembunyikan error lama & tampilkan status loading
            sandiLamaError.style.display = 'none';
            sandiLamaError.textContent = '';
            lanjutButton.disabled = true;
            lanjutButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengecek...';

            try {
                // 3. Kirim data ke server untuk dicek via AJAX
                const response = await fetch("/pengaturan/cek-sandi", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        sandi_lama: sandiLamaValue
                    })
                });

                // 4. Proses balasan
                const data = await response.json();

                if (response.ok && data.success) {
                    // 5. JIKA BENAR: Pindahkan data sandi lama
                    const sandiBaruForm = document.getElementById('formSandiBaru');
                    
                    const oldHiddenInput = sandiBaruForm.querySelector('input[name="sandi_lama"]');
                    if (oldHiddenInput) {
                        oldHiddenInput.remove();
                    }
                    
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'sandi_lama';
                    hiddenInput.value = sandiLamaValue;
                    sandiBaruForm.appendChild(hiddenInput);

                    sandiLamaInput.value = '';
                    
                    cekSandiModal.hide();
                    sandiBaruModal.show();

                } else {
                    // 6. JIKA SALAH (dari server): Tampilkan pesan error
                    sandiLamaError.textContent = data.message || 'Terjadi kesalahan.';
                    sandiLamaError.style.display = 'block';
                }

            } catch (error) {
                // 7. JIKA GAGAL TOTAL (network error, dll)
                sandiLamaError.textContent = 'Tidak dapat terhubung ke server. Coba lagi.';
                sandiLamaError.style.display = 'block';
            } finally {
                // 8. Kembalikan tombol ke kondisi normal
                lanjutButton.disabled = false;
                lanjutButton.innerHTML = 'Lanjut';
            }
        });
    }
});