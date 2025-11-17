
document.addEventListener("DOMContentLoaded", () => {

    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('fileInput');
    const triggerBtn = document.getElementById('triggerInput');

    // tombol buat buka dialog file
    triggerBtn.addEventListener('click', () => {
        fileInput.click();
    });

    // Drag di area tertentu
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.opacity = '0.85';
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.style.opacity = '1';
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.opacity = '1';

        const file = e.dataTransfer.files[0];
        uploadFile(file);
    });

    // Upload lewat pilih file manual
    fileInput.addEventListener('change', (e) => {
        uploadFile(e.target.files[0]);
    });

    async function uploadFile(file) {
        if (!file) return;

        // Validasi IFC
        if (!file.name.toLowerCase().endsWith(".ifc")) {
            alert("File harus berformat .ifc");
            return;
        }

        const formData = new FormData();
        formData.append("file", file);

        let response = await fetch("{{ route('unggah.upload') }}", {
            method:"POST",
            headers: { 
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        let result = await response.json();
        alert(result.message);
    }

});
