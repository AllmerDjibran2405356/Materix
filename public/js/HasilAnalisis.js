import * as THREE from 'https://esm.sh/three@0.160.0';
import * as OBC from 'https://esm.sh/@thatopen/components@2.0.0?deps=three@0.160.0,web-ifc@0.0.56';
import * as OBCF from 'https://esm.sh/@thatopen/components-front@2.0.0?deps=three@0.160.0,web-ifc@0.0.56,@thatopen/components@2.0.0';

// ==========================================
// 1. FUNGSI GLOBAL & STATE MANAGEMENT
// ==========================================

window.MODIFIED_GUIDS = new Set();

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

window.handleSelectJob = async function(guid, namaPekerjaan) {
    if(!guid || guid === '-') return alert("Silakan pilih objek yang valid.");

    // Tandai sebagai berubah
    window.MODIFIED_GUIDS.add(guid);

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

// --- PERBAIKAN UTAMA ADA DI SINI ---
window.handleRemoveJob = async function(guid, index) {
    // ✅ TAMBAHAN PENTING:
    // Menghapus juga dianggap sebagai "Perubahan", jadi harus dicatat
    // agar saat Final Save, controller tahu bahwa komponen ini harus diupdate (dikosongkan).
    window.MODIFIED_GUIDS.add(guid);

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

window.triggerFinalSave = async function() {
    // Pengecekan
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
            window.MODIFIED_GUIDS.clear(); // Reset setelah berhasil
        } else {
            alert("Simpan gagal");
        }
    } catch (error) {
        console.error("Final Save Error:", error);
        alert("Simpan gagal");
    } finally {
        document.body.style.cursor = 'default';
    }
};

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

        const pivotMarker = new THREE.Mesh(new THREE.SphereGeometry(0.15), new THREE.MeshBasicMaterial({ color: 0xff0000, opacity: 0.6, transparent: true }));
        pivotMarker.renderOrder = 999;
        world.scene.three.add(pivotMarker);

        updateStatus("Menyiapkan Loader...");
        const fragmentIfcLoader = components.get(OBC.IfcLoader);
        await fragmentIfcLoader.setup({ wasm: { path: "https://unpkg.com/web-ifc@0.0.56/", absolute: true } });
        fragmentIfcLoader.settings.webIfc.COORDINATE_TO_ORIGIN = true;
        const highlighter = components.get(OBCF.Highlighter);
        highlighter.setup({ world });
        highlighter.zoomToSelection = true;

        if (!window.IFC_URL) throw new Error("URL IFC Missing");
        updateStatus("Mengunduh Model...");
        const res = await fetch(window.IFC_URL);
        const data = new Uint8Array(await res.arrayBuffer());
        const model = await fragmentIfcLoader.load(data);
        world.scene.three.add(model);

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

        async function displayProperties(model, expressID) {
            try {
                // ... (Kode sebelumnya: ambil props, guid, name, type) ...
                const props = await model.getProperties(expressID);
                if (!props) { propContent.innerHTML = "<p>No Data.</p>"; return; }

                const ifcGuid = props.GlobalId?.value;
                const name = props.Name?.value || 'Unnamed';
                const type = props.ObjectType?.value || 'Unknown';
                const displayGuid = ifcGuid || '-';

                let analysisItem = window.ANALYSIS_DATA?.find(item => item.guid === ifcGuid);
                let dbId = '<span style="color:orange;">Checking...</span>';

                renderHTML(name, type, displayGuid, analysisItem, dbId);

                // --- PERBAIKAN DI SINI ---
                if (ifcGuid) {
                    // Kita kirim desain_id agar server mengambil data DB yang benar
                    const url = `${window.API_GET_JOBS}?guid=${ifcGuid}&desain_id=${window.ID_DESAIN}`;

                    fetch(url)
                        .then(r => r.json())
                        .then(res => {
                            const data = (res.status === 'success') ? res.data : [];
                            renderSelectedJobsList(data, ifcGuid);
                        })
                        .catch(err => {
                            renderSelectedJobsList([], ifcGuid);
                        });
                } else {
                    renderSelectedJobsList([], null);
                }
                // -------------------------

                // ... (Sisa kode fetch status DB & renderHTML tetap sama) ...
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

            } catch (e) { console.error(e); }
        }

        function renderHTML(name, type, guid, analysisItem, dbId) {
            let analysisHtml = analysisItem ? `
                <div class="analysis-box" style="position: relative; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                    <div style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 3px solid #bbf7d0;">
                        <div class="analysis-title" style="font-weight: bold; color: #166534; display:flex; align-items:center; gap:5px;">
                            <span>🤖</span> Hasil Analisis
                        </div>
                    </div>
                    <table class="prop-table" style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                        <tr style="border-bottom: 1px solid #dcfce7;"><th style="text-align: left; padding: 5px; color: #555;">Kategori</th><td style="text-align: right; padding: 5px; font-weight:600;">${analysisItem.label_cad || '-'}</td></tr>
                        <tr style="border-bottom: 1px solid #dcfce7;"><th style="text-align: left; padding: 5px; color: #555;">Volume</th><td style="text-align: right; padding: 5px; font-weight: bold; color: #14532d;">${Number(analysisItem.kuantitas.volume_m3).toLocaleString('id-ID')} m³</td></tr>
                    </table>
                </div>` : `<div style="padding:10px;background:#fff7ed;border-radius:6px;margin-bottom:15px;font-size:0.9em;">⚠️ Data analisis tidak tersedia.</div>`;

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
                        <tr style="background-color: #f9f9f9;"><td style="padding:3px 0;"><strong>ID Database:</strong></td><td style="text-align:right;" id="db-id">${dbId}</td></tr>
                        <tr><td style="padding:3px 0;"><strong>Name:</strong></td><td style="text-align:right;">${name}</td></tr>
                        <tr><td style="padding:3px 0;"><strong>Type:</strong></td><td style="text-align:right;">${type}</td></tr>
                        <tr><td style="padding:3px 0;"><strong>GUID:</strong></td><td style="text-align:right; font-family:monospace; font-size:0.9em;">${guid}</td></tr>
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
