<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DesainRumah;
use App\Models\KomponenDesain;
use App\Models\ListPekerjaan;
use App\Models\PekerjaanKomponen;
use App\Models\RekapKebutuhanBahanProyek;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class HasilAnalisisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================================
    // 1. VIEW UTAMA (LOAD DATA DB -> SESSION)
    // =========================================================================
    public function view($id)
    {
        $desain = DesainRumah::findOrFail($id);
        $works = ListPekerjaan::all();

        $sessionKey = 'workspace_desain_' . $id;
        $allComponents = KomponenDesain::where('ID_Desain_Rumah', $id)->get();

        $sessionData = [];
        foreach ($allComponents as $comp) {
            $jobs = PekerjaanKomponen::join(
                        'list_kode_pekerjaan',
                        'list_pekerjaan_komponen.ID_Pekerjaan',
                        '=',
                        'list_kode_pekerjaan.ID_Pekerjaan'
                    )
                    ->where('list_pekerjaan_komponen.ID_Komponen', $comp->ID_Komponen)
                    ->select('list_kode_pekerjaan.Nama_Pekerjaan')
                    ->get();

            $sessionData[$comp->Ifc_Guid] = $jobs
                ->map(fn($item) => ['Nama_Pekerjaan' => $item->Nama_Pekerjaan])
                ->toArray();
        }

        session()->put($sessionKey, $sessionData);

        // ===================================================
        // 🔥 UPDATED: JSON diambil dari base_path('public/IFCprocessed')
        // ===================================================
        $jsonFileName = $desain->Nama_Desain . '_ifc_data.json';
        $jsonPath = base_path('public/IFCprocessed/' . $jsonFileName);

        $data = [];
        if (File::exists($jsonPath)) {
            $jsonContent = File::get($jsonPath);
            $data = json_decode($jsonContent, true);
        }

        // IFC URL
        $ifcFileName = $desain->Nama_Desain . '.ifc';
        $publicPath = public_path('uploads/ifc/' . $ifcFileName);
        $ifcUrl = File::exists($publicPath) ? asset('uploads/ifc/' . $ifcFileName) : '';

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
        $jobs = $workspace[$guid] ?? [];

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

        $exists = collect($workspace[$guid])->contains(fn($j) => $j['Nama_Pekerjaan'] === $job['Nama_Pekerjaan']);

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

        if (isset($workspace[$guid][$index])) {
            array_splice($workspace[$guid], $index, 1);
            session()->put($sessionKey, $workspace);
        }

        return response()->json(['status' => 'success', 'data' => $workspace[$guid] ?? []]);
    }

    // =========================================================================
    // 6. FINAL SAVE + RAB
    // =========================================================================
    public function finalSave(Request $request)
    {
        try {
            $desainId = $request->input('desain_id');
            $userId   = Auth::id();

            $sessionKey = 'workspace_desain_' . $desainId;
            $workspace  = session()->get($sessionKey);

            if (!$workspace) {
                return response()->json(['status' => 'success', 'message' => 'Tidak ada data sesi.']);
            }

            DB::beginTransaction();

            $masterPekerjaan = ListPekerjaan::pluck('ID_Pekerjaan', 'Nama_Pekerjaan')->toArray();

            foreach ($workspace as $guid => $jobsInSession) {

                $komponen = KomponenDesain::where('ID_Desain_Rumah', $desainId)
                                          ->where('Ifc_Guid', $guid)->first();

                if (!$komponen) continue;

                PekerjaanKomponen::where('ID_Komponen', $komponen->ID_Komponen)->delete();

                if (!empty($jobsInSession)) {
                    $insertData = [];

                    foreach ($jobsInSession as $jobData) {
                        $nama = $jobData['Nama_Pekerjaan'];
                        if (isset($masterPekerjaan[$nama])) {
                            $insertData[] = [
                                'ID_Komponen'  => $komponen->ID_Komponen,
                                'ID_Pekerjaan' => $masterPekerjaan[$nama]
                            ];
                        }
                    }

                    if (!empty($insertData)) {
                        PekerjaanKomponen::insert($insertData);
                    }
                }
            }

            // =============== HITUNG RAB ===============
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

                $isFloor = str_contains($namaKomponen, 'slab')
                        || str_contains($namaKomponen, 'floor')
                        || str_contains($namaKomponen, 'lantai');

                $area_m2 = $isFloor ? ($P * $L) : ($P * $T);
                $panjang_lari = max($P, $L, $T);

                foreach ($komponen->pekerjaanKomponen as $pk) {

                    $masterKerja = $pk->pekerjaan;
                    if (!$masterKerja || !$masterKerja->ID_Satuan) continue;

                    $volumeKerja = match($masterKerja->ID_Satuan) {
                        1  => $vol_m3,
                        7  => $area_m2,
                        12 => $panjang_lari,
                        4  => 1,
                        default => 0,
                    };

                    if ($masterKerja->analisaBahan) {
                        foreach($masterKerja->analisaBahan as $resep) {

                            $qtyButuh = $volumeKerja * $resep->Koefisien;

                            $harga      = $resep->bahan->hargaTerbaru->Harga_per_Satuan ?? 0;
                            $satuanNama = $resep->bahan->satuanUkur->Simbol_Satuan ?? 'Unit';
                            $idBahan    = $resep->ID_Bahan;

                            if (!isset($tempRekap[$idBahan])) {
                                $tempRekap[$idBahan] = [
                                    'qty'    => 0,
                                    'harga'  => $harga,
                                    'satuan' => $satuanNama
                                ];
                            }

                            $tempRekap[$idBahan]['qty'] += $qtyButuh;
                        }
                    }
                }
            }

            // DELETE OLD
            RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $desainId)
                ->where('ID_User', $userId)
                ->delete();

            // INSERT NEW
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

            if (!empty($insertRAB)) {
                RekapKebutuhanBahanProyek::insert($insertRAB);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Sinkronisasi & Perhitungan RAB Berhasil!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
