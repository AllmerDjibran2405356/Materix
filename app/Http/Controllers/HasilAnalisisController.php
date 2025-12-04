<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DesainRumah;
use App\Models\KomponenDesain;
use App\Models\ListPekerjaan;
use App\Models\PekerjaanKomponen;
use App\Models\RekapKebutuhanBahanProyek; // Pastikan 'Models' huruf besar
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HasilAnalisisController extends Controller
{
    /**
     * CONSTRUCTOR: PENGAMAN PINTU MASUK
     * Fungsi ini akan dijalankan pertama kali sebelum fungsi lain.
     * Middleware 'auth' memastikan hanya user login yang bisa lewat.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================================
    // 1. VIEW UTAMA (LOAD DATA DB -> SESSION)
    // =========================================================================
    public function view($id)
    {
        $desain = DesainRumah::where('ID_Desain_Rumah', $id)->firstOrFail();
        $works = ListPekerjaan::all();

        // Setup Session Key Unik
        $sessionKey = 'workspace_desain_' . $id;

        // Ambil komponen milik desain ini
        $allComponents = KomponenDesain::where('ID_Desain_Rumah', $id)->get();

        $sessionData = [];
        foreach ($allComponents as $comp) {
            // JOIN tabel pivot ke master
            $jobs = PekerjaanKomponen::join('list_kode_pekerjaan', 'list_pekerjaan_komponen.ID_Pekerjaan', '=', 'list_kode_pekerjaan.ID_Pekerjaan')
                        ->where('list_pekerjaan_komponen.ID_Komponen', $comp->ID_Komponen)
                        ->select('list_kode_pekerjaan.Nama_Pekerjaan')
                        ->get();

            // Simpan ke array session
            $sessionData[$comp->Ifc_Guid] = $jobs->map(function($item) {
                return ['Nama_Pekerjaan' => $item->Nama_Pekerjaan];
            })->toArray();
        }

        session()->put($sessionKey, $sessionData);

        // Load JSON File
        $jsonPath = base_path("Materix_Engine\\ai_engine_materix\\engine_bim_and_ifc\\data\\processed\\{$desain->Nama_Desain}_ifc_data.json");
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
    // 2. API: CARI KOMPONEN
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
    // 3. GET JOBS (SESSION)
    // =========================================================================
    public function getJobs(Request $request)
    {
        $guid = $request->query('guid');
        $komponen = KomponenDesain::where('Ifc_Guid', $guid)->first();

        if(!$komponen) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $sessionKey = 'workspace_desain_' . $komponen->ID_Desain_Rumah;
        $workspace = session()->get($sessionKey, []);
        $jobs = isset($workspace[$guid]) ? $workspace[$guid] : [];

        return response()->json(['status' => 'success', 'data' => $jobs]);
    }

    // =========================================================================
    // 4. SAVE JOB (SESSION)
    // =========================================================================
    public function saveJob(Request $request)
    {
        $guid = $request->input('guid');
        $job = $request->input('job');

        $komponen = KomponenDesain::where('Ifc_Guid', $guid)->first();
        if(!$komponen) return response()->json(['status' => 'error', 'message' => 'Komponen tidak valid']);

        $sessionKey = 'workspace_desain_' . $komponen->ID_Desain_Rumah;
        $workspace = session()->get($sessionKey, []);

        if (!isset($workspace[$guid])) {
            $workspace[$guid] = [];
        }

        $exists = false;
        foreach ($workspace[$guid] as $existingJob) {
            if ($existingJob['Nama_Pekerjaan'] === $job['Nama_Pekerjaan']) {
                $exists = true; break;
            }
        }

        if (!$exists) {
            $workspace[$guid][] = $job;
            session()->put($sessionKey, $workspace);
        }

        return response()->json(['status' => 'success', 'data' => $workspace[$guid]]);
    }

    // =========================================================================
    // 5. REMOVE JOB (SESSION)
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
            array_splice($workspace[$guid], $index, 1);
            session()->put($sessionKey, $workspace);
        }

        return response()->json(['status' => 'success', 'data' => $workspace[$guid] ?? []]);
    }

    // =========================================================================
    // 6. FINAL SAVE: SINKRONISASI PEKERJAAN + HITUNG RAB (HANYA USER LOGIN)
    // =========================================================================
    public function finalSave(Request $request)
    {
        try {
            $desainId = $request->input('desain_id');

            // Karena sudah pakai middleware auth, Auth::id() PASTI ada isinya.
            $userId = Auth::id();

            $sessionKey = 'workspace_desain_' . $desainId;
            $workspace = session()->get($sessionKey);

            if (!$workspace) {
                return response()->json(['status' => 'success', 'message' => 'Tidak ada data sesi (session expired atau belum ada perubahan).']);
            }

            DB::beginTransaction();

            // -------------------------------------------------------------
            // TAHAP 1: SINKRONISASI PEKERJAAN (Session -> Database)
            // -------------------------------------------------------------
            $masterPekerjaan = ListPekerjaan::pluck('ID_Pekerjaan', 'Nama_Pekerjaan')->toArray();

            foreach ($workspace as $guid => $jobsInSession) {
                $komponen = KomponenDesain::where('ID_Desain_Rumah', $desainId)
                                          ->where('Ifc_Guid', $guid)
                                          ->first();
                if (!$komponen) continue;

                // A. Hapus Lama
                PekerjaanKomponen::where('ID_Komponen', $komponen->ID_Komponen)->delete();

                // B. Simpan Baru
                if (!empty($jobsInSession)) {
                    $insertData = [];
                    foreach ($jobsInSession as $jobData) {
                        $namaPekerjaan = $jobData['Nama_Pekerjaan'];
                        if (isset($masterPekerjaan[$namaPekerjaan])) {
                            $insertData[] = [
                                'ID_Komponen'  => $komponen->ID_Komponen,
                                'ID_Pekerjaan' => $masterPekerjaan[$namaPekerjaan]
                            ];
                        }
                    }
                    if (count($insertData) > 0) {
                        PekerjaanKomponen::insert($insertData);
                    }
                }
            }

            // -------------------------------------------------------------
            // TAHAP 2: HITUNG ULANG RAB & SIMPAN KE TABEL REKAP
            // -------------------------------------------------------------

            // Ambil data fresh dari database
            $KomponenList = KomponenDesain::with([
                'pekerjaanKomponen.pekerjaan.satuan',
                'pekerjaanKomponen.pekerjaan.analisaBahan.bahan.hargaTerbaru',
                'pekerjaanKomponen.pekerjaan.analisaBahan.bahan.satuanUkur'
            ])
            ->where('ID_Desain_Rumah', $desainId)
            ->get();

            $tempRekap = [];

            foreach ($KomponenList as $komponen){
                $P = floatval($komponen->Panjang);
                $L = floatval($komponen->Lebar);
                $T = floatval($komponen->Tinggi);
                $vol_m3 = $P * $L * $T;

                $namaKomponen = strtolower($komponen->Nama_Komponen);
                $isFloor = str_contains($namaKomponen, 'slab') || str_contains($namaKomponen, 'floor') || str_contains($namaKomponen, 'lantai');
                $area_m2 = $isFloor ? ($P * $L) : ($P * $T);
                $panjang_lari = max($P, $L, $T);

                foreach ($komponen->pekerjaanKomponen as $pk) {
                    $masterKerja = $pk->pekerjaan;
                    if (!$masterKerja || !$masterKerja->ID_Satuan) continue;

                    $volumeKerja = 0;
                    switch ($masterKerja->ID_Satuan) {
                        case 1: $volumeKerja = $vol_m3; break;       // m3
                        case 7: $volumeKerja = $area_m2; break;      // m2
                        case 12: $volumeKerja = $panjang_lari; break;// m'
                        case 4: $volumeKerja = 1; break;             // Unit
                        default: $volumeKerja = 0; break;
                    }

                    if ($masterKerja->analisaBahan) {
                        foreach($masterKerja->analisaBahan as $resep) {
                            $qtyButuh = $volumeKerja * $resep->Koefisien;
                            $harga = $resep->bahan->hargaTerbaru->Harga_per_Satuan ?? 0;
                            $satuanNama = $resep->bahan->satuanUkur->Simbol_Satuan ?? 'Unit';
                            $idBahan = $resep->ID_Bahan;

                            if (!isset($tempRekap[$idBahan])) {
                                $tempRekap[$idBahan] = ['qty' => 0, 'harga' => $harga, 'satuan' => $satuanNama];
                            }
                            $tempRekap[$idBahan]['qty'] += $qtyButuh;
                        }
                    }
                }
            }

            // Hapus Rekap Lama milik User ini untuk Desain ini
            RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $desainId)
                               ->where('ID_User', $userId)
                               ->delete();

            $insertRAB = [];
            $now = now();

            foreach ($tempRekap as $idBahan => $data) {
                if ($data['qty'] <= 0) continue;
                $insertRAB[] = [
                    'ID_User'               => $userId,
                    'ID_Desain_Rumah'       => $desainId,
                    'ID_Bahan'              => $idBahan,
                    'Volume_Teoritis'       => $data['qty'],
                    'Volume_Final'          => $data['qty'],
                    'Satuan_Saat_Ini'       => $data['satuan'],
                    'Harga_Satuan_Saat_Ini' => $data['harga'],
                    'Total_Harga'           => $data['qty'] * $data['harga'],
                    'Tanggal_Hitung'        => $now
                ];
            }

            if (count($insertRAB) > 0) {
                RekapKebutuhanBahanProyek::insert($insertRAB);
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Sinkronisasi & Perhitungan RAB Berhasil Disimpan!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
