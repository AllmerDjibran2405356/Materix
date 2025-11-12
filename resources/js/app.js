document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk toggle password visibility
    function setupPasswordToggle(buttonId, inputId) {
        const toggleButton = document.getElementById(buttonId);
        const passwordInput = document.getElementById(inputId);

        if (!toggleButton || !passwordInput) return;

        toggleButton.addEventListener('mousedown', function() {
            passwordInput.type = 'text';
        });

        toggleButton.addEventListener('mouseup', function() {
            passwordInput.type = 'password';
        });

        toggleButton.addEventListener('mouseleave', function() {
            passwordInput.type = 'password';
        });
    }

    // Setup password toggles
    setupPasswordToggle('toggleSandiLama', 'sandiLama');
    setupPasswordToggle('toggleSandiBaru', 'sandiBaru');
    setupPasswordToggle('toggleUlangiSandiBaru', 'ulangiSandiBaru');

    // Logic untuk tombol "Lanjut"
    const lanjutButton = document.getElementById('btnLanjutSandi');
    
    if (lanjutButton) {
        lanjutButton.addEventListener('click', async function() {
            const sandiLamaInput = document.getElementById('sandiLama');
            const sandiLamaValue = sandiLamaInput.value;
            const errorBox = document.getElementById('sandiLamaError');

            if (errorBox) {
                errorBox.style.display = 'none';
                errorBox.textContent = '';
            }

            try {
                const response = await fetch('/pengaturan/cek-sandi', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ sandi_lama: sandiLamaValue }),
                });

                const data = await response.json();

                if (!data.success) {
                    if (errorBox) {
                        errorBox.textContent = data.message || 'Terjadi kesalahan.';
                        errorBox.style.display = 'block';
                    }
                    return;
                }

                // Sukses - lanjut ke modal sandi baru
                const sandiBaruForm = document.getElementById('formSandiBaru');
                const oldHiddenInput = sandiBaruForm.querySelector('input[name="sandi_lama"]');
                if (oldHiddenInput) oldHiddenInput.remove();

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'sandi_lama';
                hiddenInput.value = sandiLamaValue;
                sandiBaruForm.appendChild(hiddenInput);

                sandiLamaInput.value = '';

                // Tutup modal lama dan buka modal baru
                const modalLama = bootstrap.Modal.getInstance(document.getElementById('cekSandiLamaModal'));
                if (modalLama) modalLama.hide();

                const modalBaru = new bootstrap.Modal(document.getElementById('sandiBaruModal'));
                modalBaru.show();

            } catch (err) {
                console.error('Terjadi kesalahan:', err);
            }
        });
    }
});