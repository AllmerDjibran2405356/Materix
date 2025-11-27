import * as THREE from 'https://esm.sh/three@0.160.0';
import * as OBC from 'https://esm.sh/@thatopen/components@2.0.0?deps=three@0.160.0,web-ifc@0.0.56';
import * as OBCF from 'https://esm.sh/@thatopen/components-front@2.0.0?deps=three@0.160.0,web-ifc@0.0.56,@thatopen/components@2.0.0';

// ==========================================
// 1. FUNGSI GLOBAL
// ==========================================
window.toggleAnalysisMenu = function(id) {
    const menu = document.getElementById(`menu-${id}`);
    const btn = document.getElementById(`btn-${id}`);
    if (menu) {
        const isVisible = menu.style.display === 'block';
        menu.style.display = isVisible ? 'none' : 'block';
        menu.style.opacity = isVisible ? '0' : '1';
        menu.style.transform = isVisible ? 'translateY(10px)' : 'translateY(0)';
        if (btn) btn.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(45deg)';
    }
};

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
        // --- SETUP ENGINE ---
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

        // --- PIVOT MARKER ---
        const pivotMarker = new THREE.Mesh(
            new THREE.SphereGeometry(0.15, 16, 16),
            new THREE.MeshBasicMaterial({ color: 0xff0000, depthTest: false, opacity: 0.6, transparent: true })
        );
        pivotMarker.renderOrder = 999;
        world.scene.three.add(pivotMarker);

        // --- IFC LOADER ---
        updateStatus("Menyiapkan Loader...");
        const fragmentIfcLoader = components.get(OBC.IfcLoader);
        await fragmentIfcLoader.setup({
            wasm: { path: "https://unpkg.com/web-ifc@0.0.56/", absolute: true }
        });
        fragmentIfcLoader.settings.webIfc.COORDINATE_TO_ORIGIN = true;

        // --- HIGHLIGHTER ---
        const highlighter = components.get(OBCF.Highlighter);
        highlighter.setup({ world });
        highlighter.zoomToSelection = true;

        // --- LOAD MODEL ---
        if (!window.IFC_URL) throw new Error("URL IFC tidak ditemukan");
        updateStatus("Mengunduh Model...");

        // DEBUG: Cek Data JSON Global
        console.log("📊 [DEBUG] Total Data JSON:", window.ANALYSIS_DATA ? window.ANALYSIS_DATA.length : 'KOSONG');

        const response = await fetch(window.IFC_URL);
        if (!response.ok) throw new Error(`Gagal unduh (${response.status})`);
        const buffer = await response.arrayBuffer();
        const data = new Uint8Array(buffer);

        updateStatus("Merender Geometri...");
        const model = await fragmentIfcLoader.load(data);
        world.scene.three.add(model);

        // --- INTERAKSI KLIK ---
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

        // --- KONTROL KAMERA ---
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

        const targetVector = new THREE.Vector3();
        function animateCamera() {
            const controls = world.camera.controls;
            const baseSpeed = 0.5;
            const speed = keyStates.shift ? baseSpeed * 3 : baseSpeed;
            if (keyStates.w) controls.forward(speed, true);
            if (keyStates.s) controls.forward(-speed, true);
            if (keyStates.a) controls.truck(-speed, 0, true);
            if (keyStates.d) controls.truck(speed, 0, true);
            controls.getTarget(targetVector);
            pivotMarker.position.copy(targetVector);
            requestAnimationFrame(animateCamera);
        }
        animateCamera();

        // =========================================================
        // 🛠️ FUNGSI DISPLAY PROPERTIES (WITH DEBUGGING)
        // =========================================================
        async function displayProperties(model, expressID) {
            console.group("🔍 [DEBUG] Analisis Klik Objek ID: " + expressID);
            try {
                // 1. Ambil properti standar IFC
                const props = await model.getProperties(expressID);
                if (!props) {
                    console.error("❌ Properti IFC tidak ditemukan di model 3D");
                    propContent.innerHTML = "<p>Data properti tidak ditemukan.</p>";
                    console.groupEnd();
                    return;
                }

                const ifcGuid = props.GlobalId ? props.GlobalId.value : null;
                const name = props.Name ? props.Name.value : 'Unnamed';
                const type = props.ObjectType ? props.ObjectType.value : 'Unknown Type';
                const displayGuid = ifcGuid || '-';

                console.log("📄 Info IFC Asli:", { Name: name, GUID: ifcGuid, Type: type });

                // 2. Cari Data JSON Lokal
                let analysisItem = null;
                if (ifcGuid && window.ANALYSIS_DATA) {
                    analysisItem = window.ANALYSIS_DATA.find(item => item.guid === ifcGuid);

                    if(analysisItem) {
                        console.log("✅ Data JSON Lokal DITEMUKAN:", analysisItem);
                    } else {
                        console.warn("⚠️ Data JSON Lokal TIDAK DITEMUKAN untuk GUID ini.");
                    }
                } else {
                    console.error("❌ Data Window.ANALYSIS_DATA Kosong atau GUID null.");
                }

                // 3. Placeholder Loading
                let dbId = '<span style="color:orange;">⏳ Mencari...</span>';

                // Render Awal
                renderHTML(expressID, name, type, displayGuid, analysisItem, dbId);

                // 4. LIVE FETCH KE DATABASE
                if (window.ID_DESAIN && window.API_SEARCH_URL && analysisItem) {
                    try {
                        // --- DEBUG: CEK PARAMETER YANG AKAN DIKIRIM ---
                        const payload = {
                            desain_id: window.ID_DESAIN,
                            nama: name,
                            label_cad: analysisItem.label_cad, // <-- CEK DISINI DI CONSOLE
                            guid: analysisItem.guid
                        };

                        console.log("🚀 [FETCH] Mengirim Request ke Server:", payload);

                        // Cek apakah label_cad undefined/null
                        if(!analysisItem.label_cad) {
                            console.error("⛔ [FATAL] 'label_cad' di JSON bernilai UNDEFINED atau NULL!");
                            // Kita kirim string kosong biar controller tidak error 'missing parameter'
                            // Tapi controller akan membalas 'not found'
                        }

                        // Gunakan URL URLSearchParams
                        const params = new URLSearchParams({
                            nama: name,
                            desain_id: window.ID_DESAIN,
                            label_cad: analysisItem.label_cad || '', // Fallback string kosong agar tidak error JS
                            guid: analysisItem.guid || ''
                        });

                        const fetchUrl = `${window.API_SEARCH_URL}?${params.toString()}`;
                        console.log("🔗 URL Fetch Full:", fetchUrl);

                        const response = await fetch(fetchUrl);

                        // Cek status HTTP
                        if (!response.ok) {
                            throw new Error(`Server Error: ${response.status} ${response.statusText}`);
                        }

                        const result = await response.json();
                        console.log("📥 [RESPONSE] Terima Data dari Server:", result);

                        if (result.status === 'found') {
                            dbId = `<span style="color:green; font-weight:bold;">${result.id_komponen}</span>`;
                        } else if (result.status === 'not_found') {
                            dbId = `<span style="color:grey;">Belum Disinkron</span>`;
                        } else {
                            dbId = `<span style="color:red;">${result.message}</span>`;
                        }
                    } catch (err) {
                        console.error("❌ Gagal fetch DB:", err);
                        dbId = `<span style="color:red; font-size:0.8em;">Gagal: ${err.message}</span>`;
                    }

                    // Render Ulang dengan hasil akhir
                    renderHTML(expressID, name, type, displayGuid, analysisItem, dbId);
                } else {
                    console.warn("⚠️ Fetch dibatalkan. Alasan: ", {
                        hasDesainID: !!window.ID_DESAIN,
                        hasApiUrl: !!window.API_SEARCH_URL,
                        hasAnalysisItem: !!analysisItem
                    });

                    if(!analysisItem) {
                        dbId = '<span style="color:red;">JSON Tidak Ada</span>';
                    } else {
                        dbId = '<span style="color:red;">Config URL Missing</span>';
                    }
                    renderHTML(expressID, name, type, displayGuid, analysisItem, dbId);
                }

            } catch (e) {
                console.error("Error Global:", e);
                propContent.innerHTML = `<div style="color:red;">Error: ${e.message}</div>`;
            }
            console.groupEnd();
        }

        // Fungsi Helper untuk Render HTML
        function renderHTML(expressID, name, type, displayGuid, analysisItem, dbId) {
            let html = '';

            // Bagian Analysis Box
            if (analysisItem) {
                html += `
                    <div class="analysis-box" style="position: relative; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 15px; padding-bottom: 60px; margin-bottom: 15px;">
                        <div class="analysis-title" style="font-weight: bold; color: #166534; margin-bottom: 10px; display:flex; align-items:center; gap:5px;">
                            <span>🤖</span> Hasil Analisis AI
                        </div>
                        <table class="prop-table" style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                            <tr style="border-bottom: 1px solid #dcfce7;"><th style="text-align: left; padding: 5px; color: #555;">Kategori</th><td style="text-align: right; padding: 5px; font-weight:600;">${analysisItem.label_cad || '-'}</td></tr>
                            <tr style="border-bottom: 1px solid #dcfce7;"><th style="text-align: left; padding: 5px; color: #555;">Volume</th><td style="text-align: right; padding: 5px; font-weight: bold; color: #14532d;">${formatNum(analysisItem.kuantitas.volume_m3)} m³</td></tr>
                        </table>

                         <div class="fab-wrapper" style="position: absolute; bottom: 15px; right: 15px; z-index: 10;">
                            <ul id="menu-${expressID}" style="display: none; list-style: none; margin: 0; padding: 0; position: absolute; bottom: 50px; right: 0; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.15); border-radius: 8px; min-width: 140px; overflow: hidden; border: 1px solid #eee; transition: all 0.2s ease; opacity: 0; transform: translateY(10px);">
                                <li><a href="#" style="display:block; padding:10px 15px; text-decoration:none; color:#333; border-bottom:1px solid #f0f0f0; font-size:14px;">✏️ Edit Data</a></li>
                            </ul>
                            <button id="btn-${expressID}" onclick="window.toggleAnalysisMenu('${expressID}')" style="width: 40px; height: 40px; border-radius: 50%; background-color: #166534; color: white; border: none; font-size: 24px; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; transition: transform 0.3s ease;">+</button>
                        </div>
                    </div>`;
            } else {
                html += `<div style="padding:12px; background:#fff7ed; border:1px solid #ffedd5; color:#9a3412; border-radius:8px; margin-bottom:15px; font-size:0.9em;">⚠️ Data analisis tidak tersedia.</div>`;
            }

            // Bagian Properti Asli
            html += `
                <div class="std-props">
                    <h5 style="margin: 0 0 10px 0; font-size: 14px; border-bottom: 2px solid #eee; padding-bottom: 5px;">Properti Asli IFC</h5>
                    <table class="prop-table" style="width:100%; font-size:0.85em; color:#666;">
                        <tr style="background-color: #f9f9f9;"><td style="padding:3px 0;"><strong>ID Database:</strong></td><td style="text-align:right;">${dbId}</td></tr>
                        <tr><td style="padding:3px 0;"><strong>Name:</strong></td><td style="text-align:right;">${name}</td></tr>
                        <tr><td style="padding:3px 0;"><strong>Type:</strong></td><td style="text-align:right;">${type}</td></tr>
                        <tr><td style="padding:3px 0;"><strong>GUID:</strong></td><td style="text-align:right; font-family:monospace; font-size:0.9em;">${displayGuid}</td></tr>
                    </table>
                </div>`;

            const propContent = document.getElementById('properties-content');
            if(propContent) propContent.innerHTML = html;
        }

        function formatNum(num) {
            if (num === undefined || num === null) return '0';
            return Number(num).toLocaleString('id-ID', { maximumFractionDigits: 3 });
        }

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
