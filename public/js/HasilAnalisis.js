// --- IMPORT LANGSUNG DARI URL (TANPA MAP) ---
// Ini mem-bypass kebutuhan Import Map di HTML.
// Kita kunci semua versi agar kompatibel.

import * as THREE from 'https://esm.sh/three@0.160.0';
import * as OBC from 'https://esm.sh/@thatopen/components@2.0.0?deps=three@0.160.0,web-ifc@0.0.56';
import * as OBCF from 'https://esm.sh/@thatopen/components-front@2.0.0?deps=three@0.160.0,web-ifc@0.0.56,@thatopen/components@2.0.0';

async function main() {
    const container = document.getElementById('viewer-container');
    const loadingOverlay = document.getElementById('loading-overlay');
    const loadingText = document.getElementById('loading-text');

    if (!container) return;

    // Helper update text
    const updateStatus = (msg) => {
        if(loadingText) loadingText.innerText = msg;
        console.log(`[Viewer] ${msg}`);
    };

    try {
        updateStatus("Menginisialisasi Engine 3D...");

        // 1. SETUP COMPONENTS
        const components = new OBC.Components();
        const worlds = components.get(OBC.Worlds);
        const world = worlds.create();

        // Setup Scene
        world.scene = new OBC.SimpleScene(components);
        world.renderer = new OBCF.PostproductionRenderer(components, container);
        world.camera = new OBC.OrthoPerspectiveCamera(components);

        components.init();

        // Setup Grid & Background
        world.scene.three.background = new THREE.Color(0xffffff);
        const grids = components.get(OBC.Grids);
        grids.create(world);

        // --- TAMBAHAN: PERBAIKAN TEMBOK HITAM (LIGHTING) ---

        // 1. Ambient Light (Cahaya Dasar Merata)
        // Ini memastikan semua sisi benda terang, tidak ada yang hitam pekat.
        const ambientLight = new THREE.AmbientLight(0xffffff, 1.5); // Warna putih, intensitas 1.5
        world.scene.three.add(ambientLight);

        // 2. Directional Light (Cahaya Matahari)
        // Ini memberikan efek bayangan dan kedalaman 3D.
        const directionalLight = new THREE.DirectionalLight(0xffffff, 2); // Intensitas 2
        directionalLight.position.set(10, 20, 10); // Posisi cahaya (x, y, z)
        world.scene.three.add(directionalLight);

        // 3. Hemisphere Light (Pencahayaan Langit vs Tanah)
        // Membuat nuansa lebih natural
        const hemiLight = new THREE.HemisphereLight(0xffffff, 0x444444, 1.5);
        hemiLight.position.set(0, 20, 0);
        world.scene.three.add(hemiLight);

        // 2. SETUP IFC LOADER
        updateStatus("Menyiapkan WASM...");

        const fragmentIfcLoader = components.get(OBC.IfcLoader);

        // PENTING: Kita arahkan WASM ke unpkg.com secara manual
        // Ini mencegah error 404 pada file .wasm
        await fragmentIfcLoader.setup({
            wasm: {
                path: "https://unpkg.com/web-ifc@0.0.56/",
                absolute: true
            }
        });

        // Matikan offset koordinat
        fragmentIfcLoader.settings.webIfc.COORDINATE_TO_ORIGIN = true;

        // 3. DOWNLOAD FILE
        if (!window.IFC_URL) throw new Error("URL IFC tidak ditemukan");

        updateStatus("Mengunduh file IFC...");

        const response = await fetch(window.IFC_URL);
        if (!response.ok) throw new Error(`Gagal download (${response.status})`);

        const buffer = await response.arrayBuffer();
        const data = new Uint8Array(buffer);

        // 4. LOAD KE SCENE
        updateStatus("Memproses Geometri (Ini mungkin butuh waktu)...");

        const model = await fragmentIfcLoader.load(data);
        world.scene.three.add(model);

        // 5. SELESAI
        updateStatus("Selesai!");

        // Hilangkan loading
        if(loadingOverlay) {
            loadingOverlay.style.opacity = '0';
            setTimeout(() => loadingOverlay.remove(), 500);
        }

        // Fit Kamera ke Model
        setTimeout(() => {
            if(model.bbox) {
                world.camera.controls.fitToBox(model.bbox, true);
            }
        }, 100);

    } catch (error) {
        console.error(error);
        if(loadingText) {
            loadingText.style.color = "red";
            loadingText.innerHTML = `
                Gagal Memuat:<br>
                <span style="font-size:12px">${error.message}</span>
            `;
        }
        // Jangan hilangkan overlay agar user baca errornya
    }
}

main();
