import * as THREE from 'https://esm.sh/three@0.160.0';
import * as OBC from 'https://esm.sh/@thatopen/components@2.0.0?deps=three@0.160.0,web-ifc@0.0.56';
import * as OBCF from 'https://esm.sh/@thatopen/components-front@2.0.0?deps=three@0.160.0,web-ifc@0.0.56,@thatopen/components@2.0.0';

async function main() {
    const container = document.getElementById('viewer-container');
    const loadingOverlay = document.getElementById('loading-overlay');
    const loadingText = document.getElementById('loading-text');
    const propPanel = document.getElementById('properties-panel');
    const propContent = document.getElementById('properties-content');

    if (!container) return;

    const updateStatus = (msg) => {
        if(loadingText) loadingText.innerText = msg;
        console.log(`[Viewer] ${msg}`);
    };

    try {
        // --- 1. SETUP ENGINE ---
        updateStatus("Inisialisasi Engine 3D...");
        const components = new OBC.Components();
        const worlds = components.get(OBC.Worlds);
        const world = worlds.create();

        world.scene = new OBC.SimpleScene(components);
        world.renderer = new OBCF.PostproductionRenderer(components, container);
        world.camera = new OBC.OrthoPerspectiveCamera(components);

        components.init();

        world.scene.three.background = new THREE.Color(0xf9fafb);
        components.get(OBC.Grids).create(world);

        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        world.scene.three.add(ambientLight);
        const dirLight = new THREE.DirectionalLight(0xffffff, 1.5);
        dirLight.position.set(20, 50, 20);
        world.scene.three.add(dirLight);

        // ==========================================
        //  🔴 FITUR BARU: 3D PIVOT MARKER
        // ==========================================
        // Kita buat bola merah kecil sebagai penanda visual pivot
        const pivotMarker = new THREE.Mesh(
            new THREE.SphereGeometry(0.15, 16, 16), // Ukuran bola (radius 0.15 meter)
            new THREE.MeshBasicMaterial({
                color: 0xff0000,   // Warna Merah
                depthTest: false,  // PENTING: Agar bola terlihat walau tertutup tembok (X-Ray view)
                opacity: 0.6,      // Agak transparan
                transparent: true
            })
        );
        // Agar bola ini selalu dirender paling atas (tidak tertutup objek lain)
        pivotMarker.renderOrder = 999;
        world.scene.three.add(pivotMarker);

        // --- 2. IFC LOADER ---
        updateStatus("Menyiapkan Loader...");
        const fragmentIfcLoader = components.get(OBC.IfcLoader);
        await fragmentIfcLoader.setup({
            wasm: { path: "https://unpkg.com/web-ifc@0.0.56/", absolute: true }
        });
        fragmentIfcLoader.settings.webIfc.COORDINATE_TO_ORIGIN = true;

        // --- 3. INTERAKSI & HIGHLIGHTER ---
        const highlighter = components.get(OBCF.Highlighter);
        highlighter.setup({ world });
        highlighter.zoomToSelection = true;

        // --- 4. LOAD IFC ---
        if (!window.IFC_URL) throw new Error("URL IFC tidak ditemukan");
        updateStatus("Mengunduh Model...");

        const response = await fetch(window.IFC_URL);
        if (!response.ok) throw new Error(`Gagal unduh (${response.status})`);

        const buffer = await response.arrayBuffer();
        const data = new Uint8Array(buffer);

        updateStatus("Merender Geometri...");
        const model = await fragmentIfcLoader.load(data);
        world.scene.three.add(model);

        // --- 5. LOGIKA SELEKSI ---
        highlighter.events.select.onHighlight.add(async (fragmentIdMap) => {
            if(propPanel) propPanel.classList.add('active');
            if(propContent) propContent.innerHTML = '<div class="loader-spinner" style="width:20px; height:20px; border-width:2px; margin: 0 auto;"></div>';

            let expressID = null;
            for (const fragID in fragmentIdMap) {
                const ids = fragmentIdMap[fragID];
                if (ids.size > 0) {
                    expressID = [...ids][0];
                    break;
                }
            }
            if (expressID) await displayProperties(model, expressID);
            else if(propPanel) propPanel.classList.remove('active');
        });

        // --- 6. KONTROL KAMERA (WASD + PIVOT UPDATE) ---
        const keyStates = { w: false, a: false, s: false, d: false, shift: false };

        document.addEventListener('keydown', (e) => {
            const key = e.key.toLowerCase();
            if (keyStates.hasOwnProperty(key)) keyStates[key] = true;
            if (e.key === 'Shift') keyStates.shift = true;
        });
        document.addEventListener('keyup', (e) => {
            const key = e.key.toLowerCase();
            if (keyStates.hasOwnProperty(key)) keyStates[key] = false;
            if (e.key === 'Shift') keyStates.shift = false;
        });

        // Vector sementara untuk menyimpan posisi target kamera
        const targetVector = new THREE.Vector3();

        function animateCamera() {
            const controls = world.camera.controls;
            const baseSpeed = 0.5;
            const speed = keyStates.shift ? baseSpeed * 3 : baseSpeed;

            // Logika Gerak WASD
            if (keyStates.w) controls.forward(speed, true);
            if (keyStates.s) controls.forward(-speed, true);
            if (keyStates.a) controls.truck(-speed, 0, true);
            if (keyStates.d) controls.truck(speed, 0, true);

            // LOGIKA UPDATE PIVOT MARKER
            // Ambil posisi target kamera saat ini, dan pindahkan bola merah ke sana
            controls.getTarget(targetVector);
            pivotMarker.position.copy(targetVector);

            requestAnimationFrame(animateCamera);
        }
        animateCamera();

        // --- HELPER DISPLAY PROPERTIES (SAMA SEPERTI SEBELUMNYA) ---
        async function displayProperties(model, expressID) {
            try {
                const props = await model.getProperties(expressID);
                if (!props) { propContent.innerHTML = "<p>Data kosong.</p>"; return; }

                const ifcGuid = props.GlobalId ? props.GlobalId.value : null;
                let analysisItem = null;
                if (ifcGuid && window.ANALYSIS_DATA) {
                    analysisItem = window.ANALYSIS_DATA.find(item => item.guid === ifcGuid);
                }

                let html = '';
                if (analysisItem) {
                    html += `<div class="analysis-box">Data Analisis</div>`; // (Isi dipersingkat agar muat, pakai kode sebelumnya)
                     // Gunakan kode HTML tabel yang lengkap dari jawaban sebelumnya di sini
                     html += `
                        <div class="analysis-box">
                            <div class="analysis-title">✅ Hasil Analisis</div>
                            <table class="prop-table">
                                <tr><th>Label</th><td>${analysisItem.label_cad}</td></tr>
                                <tr><th>Panjang</th><td>${formatNum(analysisItem.kuantitas.panjang)} m</td></tr>
                                <tr><th>Lebar</th><td>${formatNum(analysisItem.kuantitas.tebal)} m</td></tr>
                                <tr><th>Tinggi</th><td>${formatNum(analysisItem.kuantitas.tinggi)} m</td></tr>
                                <tr><th>Volume</th><td><b>${formatNum(analysisItem.kuantitas.volume_m3)} m³</b></td></tr>
                            </table>
                        </div>`;
                } else {
                    html += `<div style="padding:10px; background:#fff7ed; color:#9a3412;">⚠️ Tidak ada data analisis.</div>`;
                }

                // Tambahkan tabel properti IFC standar di sini...
                // (Pakai kode sebelumnya)

                propContent.innerHTML = html;
            } catch (e) { console.error(e); }
        }

        function formatNum(num) { return num ? Number(num).toLocaleString('id-ID') : '0'; }

        // --- FINALISASI ---
        updateStatus("Selesai!");
        if(loadingOverlay) {
            loadingOverlay.style.opacity = '0';
            setTimeout(() => loadingOverlay.remove(), 500);
        }
        if(model.bbox) world.camera.controls.fitToBox(model.bbox, true);

    } catch (error) {
        console.error(error);
        if(loadingText) loadingText.innerHTML = `Gagal: ${error.message}`;
    }
}

main();
