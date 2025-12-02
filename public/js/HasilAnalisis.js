import * as THREE from 'https://esm.sh/three@0.160.0';
import * as OBC from 'https://esm.sh/@thatopen/components@2.0.0?deps=three@0.160.0,web-ifc@0.0.56';
import * as OBCF from 'https://esm.sh/@thatopen/components-front@2.0.0?deps=three@0.160.0,web-ifc@0.0.56,@thatopen/components@2.0.0';

// ==========================================
// 1. STATE & API HANDLING
// ==========================================

window.MODIFIED_GUIDS = new Set();

// Toggle Dropdown Daftar Pekerjaan
window.toggleJobSection = function() {
    const content = document.getElementById('job-section-content');
    const btn = document.getElementById('btn-toggle-job');
    if (content && btn) {
        const isClosed = content.style.display === 'none';
        content.style.display = isClosed ? 'block' : 'none';
        btn.innerHTML = isClosed ? '−' : '+';
        btn.style.backgroundColor = isClosed ? '#b91c1c' : '#166534';
    }
};

// Handle User Memilih Pekerjaan Baru
window.handleSelectJob = async function(guid, namaPekerjaan) {
    if(!guid || guid === '-') return alert("Silakan pilih objek yang valid.");

    window.MODIFIED_GUIDS.add(guid); // Tandai sebagai 'perlu disimpan'

    const listContainer = document.getElementById('selected-jobs-list');
    if(listContainer) listContainer.innerHTML = '<div class="loader-spinner" style="width:15px; height:15px; border-width:2px; margin:0 auto;"></div>';

    try {
        const response = await fetch(window.API_SAVE_JOB, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
            body: JSON.stringify({ guid: guid, job: { Nama_Pekerjaan: namaPekerjaan } })
        });
        const result = await response.json();
        const data = result.status === 'success' ? result.data : [];
        renderSelectedJobsList(data, guid);
    } catch (error) {
        console.error("Save error:", error);
        alert("Gagal menambahkan pekerjaan.");
        renderSelectedJobsList([], guid);
    }
};

// Handle Hapus Pekerjaan
window.handleRemoveJob = async function(guid, index) {
    window.MODIFIED_GUIDS.add(guid); // Menghapus juga dianggap 'perubahan'

    try {
        const response = await fetch(window.API_REMOVE_JOB, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
            body: JSON.stringify({ guid: guid, index: index })
        });
        const result = await response.json();
        const data = result.status === 'success' ? result.data : [];
        renderSelectedJobsList(data, guid);
    } catch (error) {
        console.error("Delete error:", error);
    }
};

// Handle Final Save (Tombol Floppy Disk)
window.triggerFinalSave = async function() {
    if (window.MODIFIED_GUIDS.size === 0) return alert("Simpan Berhasil (Tidak ada perubahan baru).");

    if(!confirm("Konfirmasi simpan perubahan ke database?")) return;

    document.body.style.cursor = 'wait';
    try {
        const payload = {
            desain_id: window.ID_DESAIN,
            guids: Array.from(window.MODIFIED_GUIDS)
        };

        const response = await fetch(window.API_FINAL_SAVE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
            body: JSON.stringify(payload)
        });

        const result = await response.json();
        if(result.status === 'success') {
            alert("Simpan berhasil");
            window.MODIFIED_GUIDS.clear();
        } else {
            alert("Simpan gagal: " + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error("Final Save Error:", error);
        alert("Simpan gagal (Server Error)");
    } finally {
        document.body.style.cursor = 'default';
    }
};

// Helper Render List Pekerjaan Kecil
function renderSelectedJobsList(jobs, guid) {
    const container = document.getElementById('selected-jobs-list');
    if (!container) return;

    const safeJobs = Array.isArray(jobs) ? jobs : [];

    if (safeJobs.length === 0) {
        container.innerHTML = '<div style="font-style:italic; color:#9ca3af; font-size:12px; text-align:center;">Belum ada pekerjaan dipilih.</div>';
        return;
    }

    container.innerHTML = safeJobs.map((job, index) => `
        <div style="background:#ecfdf5; border:1px solid #6ee7b7; border-radius:6px; padding:8px; margin-bottom:5px; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:13px; color:#065f46; font-weight:500;">${job.Nama_Pekerjaan}</span>
            <button onclick="window.handleRemoveJob('${guid}', ${index})"
                    style="background:none; border:none; color:#ef4444; cursor:pointer; font-weight:bold; font-size:14px; padding:0 5px;"
                    title="Hapus">✕</button>
        </div>
    `).join('');
}

// ==========================================
// 2. MAIN FUNCTION (VIEWER 3D)
// ==========================================
async function main() {
    const container = document.getElementById('viewer-container');
    const loadingOverlay = document.getElementById('loading-overlay');
    const loadingText = document.getElementById('loading-text');
    const propPanel = document.getElementById('properties-panel');
    const propContent = document.getElementById('properties-content');

    if (!container) return;
    const updateStatus = (msg) => { if(loadingText) loadingText.innerText = msg; };

    try {
        // --- Setup Three.js & OBC ---
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
        world.scene.three.add(new THREE.AmbientLight(0xffffff, 0.6));
        const dirLight = new THREE.DirectionalLight(0xffffff, 1.5);
        dirLight.position.set(20, 50, 20);
        world.scene.three.add(dirLight);

        // Marker Merah (Pivot)
        const pivotMarker = new THREE.Mesh(new THREE.SphereGeometry(0.15), new THREE.MeshBasicMaterial({ color: 0xff0000, opacity: 0.6, transparent: true }));
        pivotMarker.renderOrder = 999;
        world.scene.three.add(pivotMarker);

        // --- Setup IFC Loader ---
        updateStatus("Menyiapkan Loader...");
        const fragmentIfcLoader = components.get(OBC.IfcLoader);
        await fragmentIfcLoader.setup({ wasm: { path: "https://unpkg.com/web-ifc@0.0.56/", absolute: true } });
        fragmentIfcLoader.settings.webIfc.COORDINATE_TO_ORIGIN = true;

        // --- Setup Highlighter ---
        const highlighter = components.get(OBCF.Highlighter);
        highlighter.setup({ world });
        highlighter.zoomToSelection = true;

        if (!window.IFC_URL) throw new Error("URL IFC Missing");

        // --- Load Model ---
        updateStatus("Mengunduh Model...");
        const res = await fetch(window.IFC_URL);
        const data = new Uint8Array(await res.arrayBuffer());
        const model = await fragmentIfcLoader.load(data);
        world.scene.three.add(model);

        // --- Event Listener: Klik Objek ---
        highlighter.events.select.onHighlight.add(async (fragmentIdMap) => {
            if(propPanel) propPanel.classList.add('active');
            if(container) container.classList.add('panel-open');
            if(propContent) propContent.innerHTML = '<div class="loader-spinner" style="margin:20px auto;"></div>';

            let expressID = null;
            for (const fragID in fragmentIdMap) {
                const ids = fragmentIdMap[fragID];
                if (ids.size > 0) { expressID = [...ids][0]; break; }
            }

            if (expressID) await displayProperties(model, expressID);
            else {
                if(propPanel) propPanel.classList.remove('active');
                if(container) container.classList.remove('panel-open');
            }
        });

        // --- Tampilkan Properti ---
        async function displayProperties(model, expressID) {
            try {
                const props = await model.getProperties(expressID);
                if (!props) { propContent.innerHTML = "<p>No Data.</p>"; return; }

                const ifcGuid = props.GlobalId?.value;
                const name = props.Name?.value || 'Unnamed';
                const type = props.ObjectType?.value || 'Unknown';
                const displayGuid = ifcGuid || '-';

                // Cari data di JSON yang dikirim dari Laravel
                let analysisItem = window.ANALYSIS_DATA?.find(item => item.guid === ifcGuid);
                let dbId = '<span style="color:orange;">Checking...</span>';

                // Render HTML Universal
                renderHTML(name, type, displayGuid, analysisItem, dbId);

                // Load Data Session (Pekerjaan yang sedang diedit)
                if (ifcGuid) {
                    fetch(`${window.API_GET_JOBS}?guid=${ifcGuid}`)
                        .then(r => r.json())
                        .then(res => {
                            const data = (res.status === 'success') ? res.data : [];
                            renderSelectedJobsList(data, ifcGuid);
                        })
                        .catch(err => {
                            console.error("Gagal load session jobs", err);
                            renderSelectedJobsList([], ifcGuid);
                        });
                } else {
                    renderSelectedJobsList([], null);
                }

                // Cek ID Database (Sinkronisasi)
                if (window.ID_DESAIN && analysisItem) {
                    const params = new URLSearchParams({ nama: name, desain_id: window.ID_DESAIN, label_cad: analysisItem.label_cad, guid: analysisItem.guid });
                    fetch(`${window.API_SEARCH_URL}?${params}`)
                        .then(r => r.json())
                        .then(res => {
                            dbId = res.status === 'found' ? `<span style="color:green;font-weight:bold;">${res.id_komponen}</span>` : `<span style="color:grey;">Belum Disinkron</span>`;
                            const el = document.getElementById(`db-id`);
                            if(el) el.innerHTML = dbId;
                        })
                        .catch(() => {
                            const el = document.getElementById(`db-id`);
                            if(el) el.innerHTML = '<span style="color:red; font-size:0.8em">Gagal</span>';
                        });
                } else {
                    const el = document.getElementById(`db-id`);
                    if(el) el.innerHTML = '<span style="color:grey;">-</span>';
                }

            } catch (e) {
                console.error(e);
                propContent.innerHTML = `<div style="padding:20px; color:red;">Error: ${e.message}</div>`;
            }
        }

        // --- RENDER HTML DINAMIS (UNIVERSAL) ---
        function renderHTML(name, type, guid, analysisItem, dbId) {

            // Format angka
            const fmt = (val) => {
                if (typeof val === 'number') return val.toLocaleString('id-ID', { maximumFractionDigits: 3 });
                return val || '-';
            };

            // Format Nama Key (misal: 'berat_jenis' -> 'Berat Jenis')
            const formatKey = (key) => {
                return key
                    .replace(/_/g, ' ')
                    .replace(/\b\w/g, c => c.toUpperCase());
            };

            let analysisHtml = '';

            if (analysisItem) {
                const qty = analysisItem.kuantitas || {};
                let dynamicRows = '';

                // LOGIKA LOOPING UNIVERSAL
                if (Object.keys(qty).length > 0) {
                    Object.entries(qty).forEach(([key, value]) => {
                        const isVolume = key.toLowerCase().includes('volume');
                        const isArea = key.toLowerCase().includes('area');

                        let rowStyle = '';
                        let valStyle = '';

                        if (isVolume) {
                            rowStyle = 'background-color: #d1fae5; border-top: 1px solid #6ee7b7;';
                            valStyle = 'font-weight: bold; color: #064e3b; font-size:1.1em;';
                        } else if (isArea) {
                            rowStyle = 'background-color: #f0fdfa;';
                            valStyle = 'font-weight: bold; color: #047857;';
                        }

                        dynamicRows += `
                            <tr style="border-bottom: 1px solid #f0fdf4; ${rowStyle}">
                                <th style="text-align: left; padding: 5px 4px; color: #555; font-weight:normal;">${formatKey(key)}</th>
                                <td style="text-align: right; padding: 5px 4px; ${valStyle}">${fmt(value)}</td>
                            </tr>
                        `;
                    });
                } else {
                    dynamicRows = `<tr><td colspan="2" style="text-align:center; color:#999; font-style:italic; padding:10px;">Tidak ada data dimensi detail.</td></tr>`;
                }

                analysisHtml = `
                <div class="analysis-box" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                    <div style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 3px solid #bbf7d0;">
                        <div class="analysis-title" style="font-weight: bold; color: #166534; display:flex; align-items:center; gap:5px;">
                            <span>🤖</span> Data Analisis
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <h6 style="margin: 0 0 8px 0; font-size: 11px; color: #15803d; font-weight: 700; text-transform: uppercase;">Info Item</h6>
                        <table class="prop-table" style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                            <tr><th style="text-align:left; color:#666;">Tipe IFC</th><td style="text-align:right; font-weight:600;">${analysisItem.tipe_ifc || '-'}</td></tr>
                            <tr><th style="text-align:left; color:#666;">Label CAD</th><td style="text-align:right; font-family:monospace; background:#e0f2fe; border-radius:3px; padding:0 4px;">${analysisItem.label_cad || '-'}</td></tr>
                            <tr><th style="text-align:left; color:#666;">Satuan</th><td style="text-align:right;">${analysisItem.satuan_utama_hitung || '-'}</td></tr>
                        </table>
                    </div>

                    <div>
                        <h6 style="margin: 0 0 8px 0; font-size: 11px; color: #15803d; font-weight: 700; text-transform: uppercase; border-top: 1px dashed #86efac; padding-top: 10px;">Detail Kuantitas</h6>
                        <table class="prop-table" style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                            ${dynamicRows}
                        </table>
                    </div>
                </div>`;
            } else {
                analysisHtml = `
                <div style="padding:12px; background:#fff7ed; border:1px solid #ffedd5; border-radius:6px; margin-bottom:15px; color:#c2410c; display:flex; gap:10px; align-items:start;">
                    <span style="font-size:1.2em">⚠️</span>
                    <div>
                        <div style="font-weight:bold; font-size:0.9em;">Data Tidak Ditemukan</div>
                        <div style="font-size:0.8em; opacity:0.8;">Objek ada di 3D, tapi tidak ada di JSON Database.</div>
                    </div>
                </div>`;
            }

            const worksList = window.WORKS_DATA || [];
            const listItems = worksList.map(work => `
                <div onclick="window.handleSelectJob('${guid}', '${work.Nama_Pekerjaan}')" class="dropdown-item" style="padding: 10px; border-bottom: 1px solid #f3f4f6; cursor: pointer; display: flex; justify-content: space-between; font-size: 13px; color: #374151;">
                    <span>${work.Nama_Pekerjaan}</span><span style="color:#10b981;font-weight:bold;">+</span>
                </div>`).join('');

            const html = `
                ${analysisHtml}
                <div class="std-props" style="margin-bottom: 20px;">
                    <h5 style="margin: 0 0 10px 0; font-size: 14px; border-bottom: 2px solid #eee; padding-bottom: 5px;">Properti Asli IFC</h5>
                    <table class="prop-table" style="width:100%; font-size:0.85em; color:#666;">
                        <tr style="background-color: #f9f9f9;"><td style="padding:3px 0;"><strong>ID DB:</strong></td><td style="text-align:right;" id="db-id">${dbId}</td></tr>
                        <tr><td style="padding:3px 0;"><strong>Name:</strong></td><td style="text-align:right;">${name}</td></tr>
                        <tr><td style="padding:3px 0;"><strong>Type:</strong></td><td style="text-align:right;">${type}</td></tr>
                        <tr><td style="padding:3px 0;"><strong>GUID:</strong></td><td style="text-align:right; font-family:monospace; font-size:0.85em; word-break:break-all;">${guid}</td></tr>
                    </table>
                </div>

                <div style="margin-bottom:20px;">
                    <h5 style="color:#1f2937;font-weight:700;margin-bottom:10px;font-size:14px;">✅ Pekerjaan Terpilih</h5>
                    <div id="selected-jobs-list">
                        <div class="loader-spinner" style="width:15px;height:15px;border-width:2px;margin:0 auto;"></div>
                    </div>
                </div>

                <div class="pilih-pekerjaan-container" style="border-top:4px solid #f3f4f6; padding-top:15px; margin-top:10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <h5 style="margin:0; font-weight:700; font-size:14px; color:#1f2937;">📋 Tambah Pekerjaan</h5>
                            <span id="job-counter" style="font-size:11px; background:#e5e7eb; padding:2px 6px; border-radius:4px; color:#4b5563;">${worksList.length}</span>
                        </div>
                        <button id="btn-toggle-job" onclick="window.toggleJobSection()" style="width:30px; height:30px; border-radius:50%; background:#166534; color:white; border:none; font-weight:bold; cursor:pointer; font-size:20px;">+</button>
                    </div>

                    <div id="job-section-content" style="display:none; animation:fadeIn 0.3s ease-in-out;">
                        <div style="margin-bottom: 10px; position: relative;">
                            <input type="text" id="job-search-input" placeholder="Cari..." style="width: 100%; padding: 8px 10px 8px 30px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; font-size:13px;">
                            <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #9ca3af;">🔍</span>
                        </div>
                        <div id="job-list-container" class="list-wrapper" style="max-height:30vh; overflow-y:auto; border:1px solid #e5e7eb; border-radius:6px;">${listItems}</div>
                    </div>
                </div>`;

            if(propContent) {
                propContent.innerHTML = html;
                const search = document.getElementById('job-search-input');
                const list = document.getElementById('job-list-container');
                const badge = document.getElementById('job-counter');

                if(search && list) {
                    search.addEventListener('input', (e) => {
                        const term = e.target.value.toLowerCase();
                        const filtered = worksList.filter(w => w.Nama_Pekerjaan.toLowerCase().includes(term));
                        list.innerHTML = filtered.map(work => `
                            <div onclick="window.handleSelectJob('${guid}', '${work.Nama_Pekerjaan}')" class="dropdown-item" style="padding: 10px; border-bottom: 1px solid #f3f4f6; cursor: pointer; display: flex; justify-content: space-between; font-size: 13px; color: #374151;">
                                <span>${work.Nama_Pekerjaan}</span><span style="color:#10b981;font-weight:bold;">+</span>
                            </div>`).join('');
                        if(badge) badge.innerText = filtered.length;
                    });
                }
            }
        }

        // --- Controls Keyboard (WASD) ---
        const keyStates = { w: false, a: false, s: false, d: false, shift: false };
        document.addEventListener('keydown', (e) => { if(keyStates.hasOwnProperty(e.key.toLowerCase())) keyStates[e.key.toLowerCase()] = true; if(e.key === 'Shift') keyStates.shift = true; });
        document.addEventListener('keyup', (e) => { if(keyStates.hasOwnProperty(e.key.toLowerCase())) keyStates[e.key.toLowerCase()] = false; if(e.key === 'Shift') keyStates.shift = false; });

        function animate() {
            const speed = keyStates.shift ? 1.5 : 0.5;
            if(keyStates.w) world.camera.controls.forward(speed, true);
            if(keyStates.s) world.camera.controls.forward(-speed, true);
            if(keyStates.a) world.camera.controls.truck(-speed, 0, true);
            if(keyStates.d) world.camera.controls.truck(speed, 0, true);
            pivotMarker.position.copy(world.camera.controls.getTarget(new THREE.Vector3()));
            requestAnimationFrame(animate);
        }
        animate();

        updateStatus("Selesai!");
        if(loadingOverlay) { loadingOverlay.style.opacity = '0'; setTimeout(() => loadingOverlay.remove(), 500); }
        if(model.bbox) world.camera.controls.fitToBox(model.bbox, true);

    } catch (e) { console.error(e); updateStatus("Error: " + e.message); }
}
main();
