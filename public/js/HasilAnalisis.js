import * as THREE from 'https://esm.sh/three@0.160.0';
import * as OBC from 'https://esm.sh/@thatopen/components@2.0.0?deps=three@0.160.0,web-ifc@0.0.56';
import * as OBCF from 'https://esm.sh/@thatopen/components-front@2.0.0?deps=three@0.160.0,web-ifc@0.0.56,@thatopen/components@2.0.0';

async function main() {
    const container = document.getElementById('viewer-container');
    const loadingOverlay = document.getElementById('loading-overlay');
    const loadingText = document.getElementById('loading-text');

    if (!container) return;

    const updateStatus = (msg) => {
        if(loadingText) loadingText.innerText = msg;
        console.log(`[Viewer] ${msg}`);
    };

    try {
        updateStatus("Menginisialisasi Engine 3D...");
        const components = new OBC.Components();
        const worlds = components.get(OBC.Worlds);
        const world = worlds.create();

        world.scene = new OBC.SimpleScene(components);
        world.renderer = new OBCF.PostproductionRenderer(components, container);
        world.camera = new OBC.OrthoPerspectiveCamera(components);

        components.init();

        //menambahkan cahaya
        world.scene.three.background = new THREE.Color(0xffffff);
        const grids = components.get(OBC.Grids);
        grids.create(world);

        const ambientLight = new THREE.AmbientLight(0xffffff, 1.5);
        world.scene.three.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 2);
        directionalLight.position.set(10, 20, 10);
        world.scene.three.add(directionalLight);

        const hemiLight = new THREE.HemisphereLight(0xffffff, 0x444444, 1.5);
        hemiLight.position.set(0, 20, 0);
        world.scene.three.add(hemiLight);

        //menyiapkan ifc viewer
        updateStatus("Menyiapkan WASM...");

        const fragmentIfcLoader = components.get(OBC.IfcLoader);

        await fragmentIfcLoader.setup({
            wasm: {
                path: "https://unpkg.com/web-ifc@0.0.56/",
                absolute: true
            }
        });

        fragmentIfcLoader.settings.webIfc.COORDINATE_TO_ORIGIN = true;

        if (!window.IFC_URL) throw new Error("URL IFC tidak ditemukan");

        //mengambil data file ifc
        updateStatus("Mengunduh file IFC...");

        const response = await fetch(window.IFC_URL);
        if (!response.ok) throw new Error(`Gagal download (${response.status})`);

        const buffer = await response.arrayBuffer();
        const data = new Uint8Array(buffer);

        //memproses model 3d
        updateStatus("Memproses Geometri (Ini mungkin butuh waktu)...");

        const model = await fragmentIfcLoader.load(data);
        world.scene.three.add(model);

        updateStatus("Selesai!");

        //menunjukkan error code
        if(loadingOverlay) {
            loadingOverlay.style.opacity = '0';
            setTimeout(() => loadingOverlay.remove(), 500);
        }

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
    }
}

main();
