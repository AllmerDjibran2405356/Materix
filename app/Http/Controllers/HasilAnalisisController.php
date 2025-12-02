<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DesainRumah;
use App\Models\KomponenDesain;
use App\Models\ListPekerjaan;     // Model ke tabel list_kode_pekerjaan
use App\Models\PekerjaanKomponen; // Model ke tabel list_pekerjaan_komponen
use Illuminate\Support\Facades\DB;

class HasilAnalisisController extends Controller
{
    // =========================================================================
    // 1. VIEW UTAMA (LOAD DATA DB -> SESSION)
    // =========================================================================
    public function view($id)
    {
        $desain = DesainRumah::where('ID_Desain_Rumah', $id)->firstOrFail();

        // Ambil master list pekerjaan untuk dropdown di frontend
        $works = ListPekerjaan::all();

        // 1. Setup Session Key Unik per Desain
        $sessionKey = 'workspace_desain_' . $id;

        // 2. Ambil SEMUA komponen milik desain ini
        $allComponents = KomponenDesain::where('ID_Desain_Rumah', $id)->get();

        $sessionData = [];

        foreach ($allComponents as $comp) {
            // JOIN tabel pivot (list_pekerjaan_komponen) ke master (list_kode_pekerjaan)
            // Sesuai model yang Anda berikan
            $jobs = PekerjaanKomponen::join('list_kode_pekerjaan', 'list_pekerjaan_komponen.ID_Pekerjaan', '=', 'list_kode_pekerjaan.ID_Pekerjaan')
                        ->where('list_pekerjaan_komponen.ID_Komponen', $comp->ID_Komponen)
                        ->select('list_kode_pekerjaan.Nama_Pekerjaan')
                        ->get();

            // Simpan ke array session dengan Key = GUID
            $sessionData[$comp->Ifc_Guid] = $jobs->map(function($item) {
                return ['Nama_Pekerjaan' => $item->Nama_Pekerjaan];
            })->toArray();
        }

        // 3. Masukkan ke Session (Ini jadi "Master Data Sementara")
        session()->put($sessionKey, $sessionData);

        // --- Load JSON File (Tidak berubah) ---
        $jsonPath = "C:\\Materix_Engine\\ai_engine_materix\\engine_bim_and_ifc\\data\\processed\\{$desain->Nama_Desain}_ifc_data.json";
        $data = [];
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            $data = json_decode($jsonContent, true);
        }

        $filename = $desain->Nama_Desain . '.ifc';
        $publicPath = public_path('uploads/ifc/' . $filename);
        $ifcUrl = file_exists($publicPath) ? asset('uploads/ifc/' . $filename) : '';

        return view('Page.HasilAnalisis', compact('desain', 'data', 'ifcUrl', 'works'));
    }

    // =========================================================================
    // 2. API: CARI KOMPONEN (Read Only - Sinkronisasi Data 3D)
    // =========================================================================
    public function cariKomponen(Request $request)
    {
        try {
            $desainId = $request->query('desain_id');
            $nama     = $request->query('nama');
            $labelCad = $request->query('label_cad');
            $guid     = $request->query('guid');

            if (!$desainId || !$nama || !$labelCad || !$guid) {
                return response()->json(['status' => 'error', 'message' => 'Parameter tidak lengkap']);
            }

            $komponen = KomponenDesain::where('ID_Desain_Rumah', $desainId)
                        ->where('Nama_Komponen', $nama)
                        ->where('Label_Cad', $labelCad)
                        ->where('Ifc_Guid', $guid)
                        ->first();

            if ($komponen) {
                return response()->json(['status' => 'found', 'id_komponen' => $komponen->ID_Komponen, 'data' => $komponen]);
            } else {
                return response()->json(['status' => 'not_found']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 3. GET JOBS (BACA DARI SESSION)
    // =========================================================================
    public function getJobs(Request $request)
    {
        $guid = $request->query('guid');

        // Cari komponen di DB hanya untuk mendapatkan ID Desain (untuk tahu nama session key)
        $komponen = KomponenDesain::where('Ifc_Guid', $guid)->first();

        if(!$komponen) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $sessionKey = 'workspace_desain_' . $komponen->ID_Desain_Rumah;
        $workspace = session()->get($sessionKey, []);

        // Ambil data dari array session
        $jobs = isset($workspace[$guid]) ? $workspace[$guid] : [];

        return response()->json(['status' => 'success', 'data' => $jobs]);
    }

    // =========================================================================
    // 4. SAVE JOB (TULIS KE SESSION - TANPA DB)
    // =========================================================================
    public function saveJob(Request $request)
    {
        $guid = $request->input('guid');
        $job = $request->input('job'); // Berisi { 'Nama_Pekerjaan': '...' }

        $komponen = KomponenDesain::where('Ifc_Guid', $guid)->first();
        if(!$komponen) return response()->json(['status' => 'error', 'message' => 'Komponen tidak valid']);

        $sessionKey = 'workspace_desain_' . $komponen->ID_Desain_Rumah;
        $workspace = session()->get($sessionKey, []);

        if (!isset($workspace[$guid])) {
            $workspace[$guid] = [];
        }

        // Cek Duplikasi di Session agar tidak double
        $exists = false;
        foreach ($workspace[$guid] as $existingJob) {
            if ($existingJob['Nama_Pekerjaan'] === $job['Nama_Pekerjaan']) {
                $exists = true; break;
            }
        }

        if (!$exists) {
            $workspace[$guid][] = $job;
            session()->put($sessionKey, $workspace); // Update Session Global
        }

        return response()->json(['status' => 'success', 'data' => $workspace[$guid]]);
    }

    // =========================================================================
    // 5. REMOVE JOB (HAPUS DARI SESSION - TANPA DB)
    // =========================================================================
    public function removeJob(Request $request)
    {
        $guid = $request->input('guid');
        $index = $request->input('index');

        $komponen = KomponenDesain::where('Ifc_Guid', $guid)->first();
        if(!$komponen) return response()->json(['status' => 'error']);

        $sessionKey = 'workspace_desain_' . $komponen->ID_Desain_Rumah;
        $workspace = session()->get($sessionKey, []);

        if (isset($workspace[$guid]) && isset($workspace[$guid][$index])) {
            // Hapus item dari array session berdasarkan index
            array_splice($workspace[$guid], $index, 1);
            session()->put($sessionKey, $workspace); // Simpan perubahan ke session
        }

        return response()->json(['status' => 'success', 'data' => $workspace[$guid] ?? []]);
    }

    // =========================================================================
    // 6. FINAL SAVE (SINKRONISASI TOTAL: SESSION -> DATABASE)
    // =========================================================================
    public function finalSave(Request $request)
    {
        try {
            $desainId = $request->input('desain_id');

            $sessionKey = 'workspace_desain_' . $desainId;
            $workspace = session()->get($sessionKey);

            if (!$workspace) {
                return response()->json(['status' => 'success', 'message' => 'Tidak ada data sesi (session expired atau belum ada perubahan).']);
            }

            DB::beginTransaction();

            // 1. Ambil Mapping Master Pekerjaan (Nama -> ID) dari tabel 'list_kode_pekerjaan'
            // Asumsi kolom nama pekerjaan adalah 'Nama_Pekerjaan'. Sesuaikan jika beda.
            $masterPekerjaan = ListPekerjaan::pluck('ID_Pekerjaan', 'Nama_Pekerjaan')->toArray();

            // 2. Loop semua GUID yang ada di Session
            foreach ($workspace as $guid => $jobsInSession) {

                // Cari ID Komponen berdasarkan GUID
                $komponen = KomponenDesain::where('ID_Desain_Rumah', $desainId)
                                          ->where('Ifc_Guid', $guid)
                                          ->first();

                if (!$komponen) continue;

                // --- SINKRONISASI ---

                // A. Hapus SEMUA data lama di DB untuk komponen ini
                // Model PekerjaanKomponen mengarah ke tabel 'list_pekerjaan_komponen'
                PekerjaanKomponen::where('ID_Komponen', $komponen->ID_Komponen)->delete();

                // B. Masukkan SEMUA data baru dari Session ke DB
                if (!empty($jobsInSession)) {
                    $insertData = [];
                    foreach ($jobsInSession as $jobData) {
                        $namaPekerjaan = $jobData['Nama_Pekerjaan'];

                        // Validasi: Pastikan nama pekerjaan ada di tabel Master
                        if (isset($masterPekerjaan[$namaPekerjaan])) {
                            $insertData[] = [
                                'ID_Komponen'  => $komponen->ID_Komponen,
                                'ID_Pekerjaan' => $masterPekerjaan[$namaPekerjaan]
                                // timestamps tidak perlu karena di model PekerjaanKomponen $timestamps = false
                            ];
                        }
                    }

                    if (count($insertData) > 0) {
                        PekerjaanKomponen::insert($insertData);
                    }
                }
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Sinkronisasi berhasil!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
