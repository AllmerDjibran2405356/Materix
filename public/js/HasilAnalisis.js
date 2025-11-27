import * as THREE from 'https://esm.sh/three@0.160.0';
import * as OBC from 'https://esm.sh/@thatopen/components@2.0.0?deps=three@0.160.0,web-ifc@0.0.56';
import * as OBCF from 'https://esm.sh/@thatopen/components-front@2.0.0?deps=three@0.160.0,web-ifc@0.0.56,@thatopen/components@2.0.0';

// ==========================================
// 1. FUNGSI GLOBAL UI & LOGIC
// ==========================================

// Toggle Dropdown List Pekerjaan
window.toggleJobSection = function() {
    const content = document.getElementById('job-section-content');
    const btn = document.getElementById('btn-toggle-job');

    if (content && btn) {
        if (content.style.display === 'none') {
            content.style.display = 'block';
            btn.innerHTML = '−';
            btn.style.backgroundColor = '#b91c1c'; // Merah
        } else {
            content.style.display = 'none';
            btn.innerHTML = '+';
            btn.style.backgroundColor = '#166534'; // Hijau
        }
    }
};

// Fungsi Memilih Pekerjaan (Save to Session)
window.handleSelectJob = async function(guid, namaPekerjaan) {
    if(!guid || guid === '-') return alert("Silakan pilih objek yang valid terlebih dahulu.");

    // Tampilkan loading kecil/feedback (opsional)
    const listContainer = document.getElementById('selected-jobs-list');
    if(listContainer) listContainer.innerHTML = '<div class="loader-spinner" style="width:15px; height:15px; border-width:2px; margin: 0 auto;"></div>';

    try {
        const response = await fetch(window.API_SAVE_JOB, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN // Token keamanan Laravel
            },
            body: JSON.stringify({
                guid: guid,
                job: { Nama_Pekerjaan: namaPekerjaan }
            })
        });

        const result = await response.json();
        if(result.status === 'success') {
            // Render ulang list terpilih berdasarkan data terbaru dari server
            renderSelectedJobsList(result.data, guid);
        }
    } catch (error) {
        console.error("Gagal menyimpan:", error);
        alert("Gagal menyimpan pekerjaan. Cek console.");
    }
};

// Fungsi Hapus Pekerjaan
window.handleRemoveJob = async function(guid, namaPekerjaan) {
    if(!confirm("Hapus pekerjaan ini dari list?")) return;

    try {
        const response = await fetch(window.API_REMOVE_JOB, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
            body: JSON.stringify({
                guid: guid,
                job_name: namaPekerjaan
            })
        });

        const result = await response.json();
        if(result.status === 'success') {
            renderSelectedJobsList(result.data, guid);
        }
    } catch (error) {
        console.error("Gagal menghapus:", error);
    }
};

// Helper Render List Terpilih
function renderSelectedJobsList(jobs, guid) {
    const container = document.getElementById('selected-jobs-list');
    if (!container) return;

    if (!jobs || jobs.length === 0) {
        container.innerHTML = '<div style="font-style:italic; color:#9ca3af; font-size:12px;">Belum ada pekerjaan dipilih.</div>';
        return;
    }

    container.innerHTML = jobs.map(job => `
        <div style="background: #ecfdf5; border: 1px solid #6ee7b7; border-radius: 6px; padding: 8px; margin-bottom: 5px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; color: #065f46; font-weight: 500;">${job.Nama_Pekerjaan}</span>
            <button onclick="window.handleRemoveJob('${guid}', '${job.Nama_Pekerjaan}')"
                    style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 14px; padding: 0 5px; font-weight:bold;"
                    title="Hapus">✕</button>
        </div>
    `).join('');
}


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
        // ... (SETUP ENGINE & LOADER) ...
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
        const pivotMarker = new THREE.Mesh(new THREE.SphereGeometry(0.15, 16, 16), new THREE.MeshBasicMaterial({ color: 0xff0000, depthTest: false, opacity: 0.6, transparent: true }));
        pivotMarker.renderOrder = 999;
        world.scene.three.add(pivotMarker);

        updateStatus("Menyiapkan Loader...");
        const fragmentIfcLoader = components.get(OBC.IfcLoader);
        await fragmentIfcLoader.setup({ wasm: { path: "https://unpkg.com/web-ifc@0.0.56/", absolute: true } });
        fragmentIfcLoader.settings.webIfc.COORDINATE_TO_ORIGIN = true;
        const highlighter = components.get(OBCF.Highlighter);
        highlighter.setup({ world });
        highlighter.zoomToSelection = true;

        if (!window.IFC_URL) throw new Error("URL IFC tidak ditemukan");
        updateStatus("Mengunduh Model...");
        const response = await fetch(window.IFC_URL);
        const buffer = await response.arrayBuffer();
        const data = new Uint8Array(buffer);
        updateStatus("Merender Geometri...");
        const model = await fragmentIfcLoader.load(data);
        world.scene.three.add(model);

        // --- INTERAKSI KLIK ---
        highlighter.events.select.onHighlight.add(async (fragmentIdMap) => {
            if(propPanel) propPanel.classList.add('active');
            if(propContent) propContent.innerHTML = '<div class="loader-spinner" style="width:20px; height:20px; border-width:2px; margin: 20px auto;"></div>';

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

        // ... (KONTROL KAMERA) ...
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
        // 🛠️ LOGIKA UTAMA: DISPLAY PROPERTIES
        // =========================================================
        async function displayProperties(model, expressID) {
            console.group("🔍 [DEBUG] Analisis Klik Objek ID: " + expressID);
            try {
                const props = await model.getProperties(expressID);
                if (!props) {
                    propContent.innerHTML = "<p>Data properti tidak ditemukan.</p>";
                    console.groupEnd(); return;
                }

                const ifcGuid = props.GlobalId ? props.GlobalId.value : null;
                const name = props.Name ? props.Name.value : 'Unnamed';
                const type = props.ObjectType ? props.ObjectType.value : 'Unknown Type';
                const displayGuid = ifcGuid || '-';

                let analysisItem = null;
                if (ifcGuid && window.ANALYSIS_DATA) {
                    analysisItem = window.ANALYSIS_DATA.find(item => item.guid === ifcGuid);
                }

                let dbId = '<span style="color:orange;">⏳ Mencari...</span>';

                // 1. Render Struktur Awal
                // (Bagian Selected Jobs akan dirender kosong dulu)
                renderHTML(expressID, name, type, displayGuid, analysisItem, dbId);

                // 2. FETCH SESSION JOBS (Ambil data pekerjaan yang sudah tersimpan di server)
                if (ifcGuid) {
                    try {
                        const resSession = await fetch(`${window.API_GET_JOBS}?guid=${ifcGuid}`);
                        const sessionData = await resSession.json();
                        if(sessionData.status === 'success') {
                            renderSelectedJobsList(sessionData.data, ifcGuid);
                        }
                    } catch (e) {
                        console.error("Gagal load session jobs", e);
                    }
                }

                // 3. Live Fetch status sinkronisasi Database
                if (window.ID_DESAIN && window.API_SEARCH_URL && analysisItem) {
                    try {
                        const params = new URLSearchParams({
                            nama: name, desain_id: window.ID_DESAIN,
                            label_cad: analysisItem.label_cad || '', guid: analysisItem.guid || ''
                        });
                        const response = await fetch(`${window.API_SEARCH_URL}?${params.toString()}`);
                        const result = await response.json();
                        if (result.status === 'found') dbId = `<span style="color:green; font-weight:bold;">${result.id_komponen}</span>`;
                        else if (result.status === 'not_found') dbId = `<span style="color:grey;">Belum Disinkron</span>`;
                        else dbId = `<span style="color:red;">Error</span>`;
                    } catch (err) { dbId = `<span style="color:red; font-size:0.8em;">Gagal Koneksi</span>`; }

                    // Update DOM ID saja (agar dropdown tidak reset)
                    const dbIdEl = document.getElementById(`db-id-${expressID}`);
                    if(dbIdEl) dbIdEl.innerHTML = dbId;
                } else {
                    if(!analysisItem) dbId = '<span style="color:grey;">-</span>';
                    const dbIdEl = document.getElementById(`db-id-${expressID}`);
                    if(dbIdEl) dbIdEl.innerHTML = dbId;
                }

            } catch (e) {
                console.error("Error Global:", e);
                propContent.innerHTML = `<div style="color:red;">Error: ${e.message}</div>`;
            }
            console.groupEnd();
        }

        // =========================================================
        // 🎨 FUNGSI RENDER HTML
        // =========================================================
        function renderHTML(expressID, name, type, displayGuid, analysisItem, dbId) {
            let html = '';

            // --- 1. Hasil Analisis AI ---
            if (analysisItem) {
                html += `
                    <div class="analysis-box" style="position: relative; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                        <div style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 3px solid #bbf7d0;">
                            <div class="analysis-title" style="font-weight: bold; color: #166534; display:flex; align-items:center; gap:5px;">
                                <span>🤖</span> Hasil Analisis
                            </div>
                        </div>
                        <table class="prop-table" style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                            <tr style="border-bottom: 1px solid #dcfce7;"><th style="text-align: left; padding: 5px; color: #555;">Kategori</th><td style="text-align: right; padding: 5px; font-weight:600;">${analysisItem.label_cad || '-'}</td></tr>
                            <tr style="border-bottom: 1px solid #dcfce7;"><th style="text-align: left; padding: 5px; color: #555;">Volume</th><td style="text-align: right; padding: 5px; font-weight: bold; color: #14532d;">${formatNum(analysisItem.kuantitas.volume_m3)} m³</td></tr>
                        </table>
                    </div>`;
            } else {
                html += `<div style="padding:12px; background:#fff7ed; border:1px solid #ffedd5; color:#9a3412; border-radius:8px; margin-bottom:15px; font-size:0.9em;">⚠️ Data analisis tidak tersedia.</div>`;
            }

            // --- 2. Properti Asli IFC ---
            html += `
                <div class="std-props" style="margin-bottom: 20px;">
                    <h5 style="margin: 0 0 10px 0; font-size: 14px; border-bottom: 2px solid #eee; padding-bottom: 5px;">Properti Asli IFC</h5>
                    <table class="prop-table" style="width:100%; font-size:0.85em; color:#666;">
                        <tr style="background-color: #f9f9f9;"><td style="padding:3px 0;"><strong>ID Database:</strong></td><td style="text-align:right;" id="db-id-${expressID}">${dbId}</td></tr>
                        <tr><td style="padding:3px 0;"><strong>Name:</strong></td><td style="text-align:right;">${name}</td></tr>
                        <tr><td style="padding:3px 0;"><strong>Type:</strong></td><td style="text-align:right;">${type}</td></tr>
                        <tr><td style="padding:3px 0;"><strong>GUID:</strong></td><td style="text-align:right; font-family:monospace; font-size:0.9em;">${displayGuid}</td></tr>
                    </table>
                </div>`;

            // --- 3. PEKERJAAN TERPILIH (CONTAINER) ---
            html += `
                <div style="margin-bottom: 20px;">
                    <h5 style="margin: 0 0 10px 0; font-size: 14px; color: #1f2937; font-weight:700;">✅ Pekerjaan Terpilih</h5>
                    <div id="selected-jobs-list">
                        <div class="loader-spinner" style="width:15px; height:15px; border-width:2px; margin: 0 auto;"></div>
                    </div>
                </div>
            `;

            // --- 4. PILIH PEKERJAAN (Dropdown + List) ---
            const worksList = window.WORKS_DATA || [];

            const generateListHTML = (items) => {
                if(items.length > 0) {
                    return items.map(work => `
                        <div style="padding: 10px; border-bottom: 1px solid #f3f4f6; cursor: pointer; transition: background 0.2s; display:flex; justify-content:space-between; align-items:center;"
                             onmouseover="this.style.backgroundColor='#f9fafb'"
                             onmouseout="this.style.backgroundColor='transparent'"
                             onclick="window.handleSelectJob('${displayGuid}', '${work.Nama_Pekerjaan}')">
                            <div style="font-size: 13px; font-weight: 500; color: #374151; line-height: 1.4;">${work.Nama_Pekerjaan}</div>
                            <span style="font-size:18px; color:#10b981;">+</span>
                        </div>
                    `).join('');
                } else {
                    return '<div style="padding:20px; color:#9ca3af; text-align:center; font-size:13px;">Pekerjaan tidak ditemukan.</div>';
                }
            };

            html += `
                <div class="pilih-pekerjaan-container" style="border-top: 4px solid #f3f4f6; padding-top: 15px; margin-top: 10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <div style="display:flex; align-items:center; gap: 8px;">
                            <h5 style="margin: 0; font-size: 14px; color: #1f2937; font-weight:700;">📋 Tambah Pekerjaan</h5>
                            <span id="job-counter" style="font-size: 11px; background: #e5e7eb; padding: 2px 6px; border-radius: 4px; color: #4b5563;">${worksList.length}</span>
                        </div>
                        <button id="btn-toggle-job" onclick="window.toggleJobSection()"
                                style="width: 30px; height: 30px; border-radius: 50%; background-color: #166534; color: white; border: none; font-size: 20px; font-weight:bold; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            +
                        </button>
                    </div>

                    <div id="job-section-content" style="display: none; animation: fadeIn 0.3s ease-in-out;">
                        <div style="margin-bottom: 10px; position: relative;">
                            <input type="text" id="job-search-input" placeholder="Cari pekerjaan..."
                                   style="width: 100%; padding: 8px 10px 8px 30px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; outline: none; transition: border-color 0.2s; box-sizing: border-box;">
                            <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #9ca3af;">🔍</span>
                        </div>
                        <div id="job-list-container" class="list-wrapper" style="max-height: 35vh; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px; background: #fff; box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);">
                            ${generateListHTML(worksList)}
                        </div>
                        <div style="text-align:center; font-size:10px; color:#ccc; margin-top:5px;">End of list</div>
                    </div>
                </div>
            `;

            // --- FINAL RENDER ---
            const propContent = document.getElementById('properties-content');
            if(propContent) {
                propContent.innerHTML = html;

                // Event Listener Search
                const searchInput = document.getElementById('job-search-input');
                const listContainer = document.getElementById('job-list-container');
                const counterBadge = document.getElementById('job-counter');

                if(searchInput && listContainer) {
                    searchInput.addEventListener('input', function(e) {
                        const searchTerm = e.target.value.toLowerCase();
                        const filteredWorks = worksList.filter(work => work.Nama_Pekerjaan.toLowerCase().includes(searchTerm));
                        listContainer.innerHTML = generateListHTML(filteredWorks);
                        if(counterBadge) counterBadge.innerText = filteredWorks.length;
                    });
                }
            }
        }

        function formatNum(num) {
            if (num === undefined || num === null) return '0';
            return Number(num).toLocaleString('id-ID', { maximumFractionDigits: 3 });
        }

        updateStatus("Selesai!");
        if(loadingOverlay) { loadingOverlay.style.opacity = '0'; setTimeout(() => loadingOverlay.remove(), 500); }
        if(model.bbox) world.camera.controls.fitToBox(model.bbox, true);

    } catch (error) {
        console.error(error);
        if(loadingText) loadingText.innerHTML = `Gagal: ${error.message}`;
    }
}
main();
