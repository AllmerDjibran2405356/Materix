<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DesainRumah;
use App\Models\KomponenDesain;
use App\Models\ListPekerjaan;
use App\Models\PekerjaanKomponen;
use Illuminate\Support\Facades\DB;

class HasilAnalisisController extends Controller
{
    // --- 1. VIEW UTAMA ---
    public function view($id)
    {
        $desain = DesainRumah::where('ID_Desain_Rumah', $id)->firstOrFail();
        $works = ListPekerjaan::all();

        // Path JSON (Sesuaikan dengan path server lokal Anda)
        $jsonPath = "C:\\Materix_Engine\\ai_engine_materix\\engine_bim_and_ifc\\data\\processed\\{$desain->Nama_Desain}_ifc_data.json";

        $data = [];
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            $data = json_decode($jsonContent, true); // Decode menjadi array PHP
        }

        $filename = $desain->Nama_Desain . '.ifc';
        $publicPath = public_path('uploads/ifc/' . $filename);
        $ifcUrl = file_exists($publicPath) ? asset('uploads/ifc/' . $filename) : '';

        return view('Page.HasilAnalisis', compact('desain', 'data', 'ifcUrl', 'works'));
    }

    // --- 2. API: CARI KOMPONEN (Sinkronisasi DB) ---
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

    // --- 3. API: GET JOBS (Ambil Pekerjaan Tersimpan) ---
    public function getJobs(Request $request)
    {
        $guid = $request->query('guid');
        $sessionKey = 'pekerjaan_terpilih.' . $guid;

        // Cek Session dulu
        $savedJobs = session()->get($sessionKey);

        // Jika Session kosong, ambil dari Database (Reload/Refresh case)
        if (empty($savedJobs)) {
            $komponen = KomponenDesain::where('Ifc_Guid', $guid)->first();

            if ($komponen) {
                $dbJobs = PekerjaanKomponen::join('list_pekerjaan', 'list_pekerjaan_komponen.ID_Pekerjaan', '=', 'list_pekerjaan.ID_Pekerjaan')
                            ->where('list_pekerjaan_komponen.ID_Komponen', $komponen->ID_Komponen)
                            ->select('list_pekerjaan.Nama_Pekerjaan')
                            ->get();

                if ($dbJobs->isNotEmpty()) {
                    $savedJobs = $dbJobs->map(function($item) {
                        return ['Nama_Pekerjaan' => $item->Nama_Pekerjaan];
                    })->toArray();

                    session()->put($sessionKey, $savedJobs);
                }
            }
        }

        return response()->json(['status' => 'success', 'data' => $savedJobs ?? []]);
    }

    // --- 4. API: SAVE JOB (Tambah ke Session) ---
    public function saveJob(Request $request)
    {
        $guid = $request->input('guid');
        $job = $request->input('job');
        $sessionKey = 'pekerjaan_terpilih.' . $guid;

        $currentJobs = session()->get($sessionKey, []);

        // Cek Duplikasi
        $exists = false;
        foreach ($currentJobs as $existingJob) {
            if ($existingJob['Nama_Pekerjaan'] === $job['Nama_Pekerjaan']) {
                $exists = true; break;
            }
        }

        if (!$exists) {
            $currentJobs[] = $job;
            session()->put($sessionKey, $currentJobs);
        }

        return response()->json(['status' => 'success', 'data' => $currentJobs]);
    }

    // --- 5. API: REMOVE JOB (Hapus dari Session) ---
    public function removeJob(Request $request)
    {
        $guid = $request->input('guid');
        $index = $request->input('index');
        $sessionKey = 'pekerjaan_terpilih.' . $guid;

        $currentJobs = session()->get($sessionKey, []);

        if (isset($currentJobs[$index])) {
            unset($currentJobs[$index]);
        }

        $updatedJobs = array_values($currentJobs); // Re-index array
        session()->put($sessionKey, $updatedJobs);

        return response()->json(['status' => 'success', 'data' => $updatedJobs]);
    }

    // --- 6. API: FINAL SAVE (Simpan ke DB) ---
    public function finalSave(Request $request)
    {
        try {
            $desainId = $request->input('desain_id');
            $guids = $request->input('guids');

            if (!$desainId || empty($guids)) {
                return response()->json(['status' => 'success', 'message' => 'Tidak ada perubahan untuk disimpan.']);
            }

            DB::beginTransaction();

            foreach ($guids as $guid) {
                $komponen = KomponenDesain::where('ID_Desain_Rumah', $desainId)
                                          ->where('Ifc_Guid', $guid)
                                          ->first();

                if (!$komponen) continue;

                $idKomponen = $komponen->ID_Komponen;
                $sessionKey = 'pekerjaan_terpilih.' . $guid;
                $jobsInSession = session()->get($sessionKey, []);

                // A. Hapus data lama
                PekerjaanKomponen::where('ID_Komponen', $idKomponen)->delete();

                // B. Simpan data baru
                if (!empty($jobsInSession)) {
                    foreach ($jobsInSession as $jobData) {
                        $masterPekerjaan = ListPekerjaan::where('Nama_Pekerjaan', $jobData['Nama_Pekerjaan'])->first();

                        if ($masterPekerjaan) {
                            PekerjaanKomponen::create([
                                'ID_Komponen'  => $idKomponen,
                                'ID_Pekerjaan' => $masterPekerjaan->ID_Pekerjaan
                            ]);
                        }
                    }
                }
                // Optional: session()->forget($sessionKey);
            }

            DB::commit();
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
