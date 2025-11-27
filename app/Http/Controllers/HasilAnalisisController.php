<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DesainRumah;
use App\Models\KomponenDesain;
use App\Models\ListPekerjaan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HasilAnalisisController extends Controller
{
    public function view($id)
    {
        $desain = DesainRumah::where('ID_Desain_Rumah', $id)->firstOrFail();
        $works = ListPekerjaan::all();

        // 1. Ambil Data JSON
        // Gunakan path yang dinamis agar aman saat deploy
        // Jika di windows local:
        $jsonPath = "C:\\Materix_Engine\\ai_engine_materix\\engine_bim_and_ifc\\data\\processed\\{$desain->Nama_Desain}_ifc_data.json";

        $data = [];
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            $data = json_decode($jsonContent, true);
        }

        // 2. Ambil File IFC
        $filename = $desain->Nama_Desain . '.ifc';
        $publicPath = public_path('uploads/ifc/' . $filename);

        $ifcUrl = '';
        if (file_exists($publicPath)) {
            $ifcUrl = asset('uploads/ifc/' . $filename);
        }

        return view('Page.HasilAnalisis', compact('desain', 'data', 'ifcUrl', 'works'));
    }

    public function cariKomponen(Request $request)
    {
        try {
            $desainId = $request->query('desain_id');
            $nama     = $request->query('nama');
            $labelCad = $request->query('label_cad');
            $guid     = $request->query('guid');

            if (!$desainId || !$nama || !$labelCad || !$guid) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Parameter tidak lengkap'
                ]);
            }

            $komponen = KomponenDesain::where('ID_Desain_Rumah', $desainId)
                        ->where('Nama_Komponen', $nama)
                        ->where('Label_Cad', $labelCad)
                        ->where('Ifc_Guid', $guid)
                        ->first();

            if ($komponen) {
                return response()->json([
                    'status'      => 'found',
                    'id_komponen' => $komponen->ID_Komponen,
                    'data'        => $komponen
                ]);
            } else {
                return response()->json([
                    'status'  => 'not_found',
                    'message' => 'Data tidak ditemukan'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // LOGIKA BARU: SESSION MANAGEMENT PEKERJAAN
    // ==========================================

    public function getJobs(Request $request)
    {
        $guid = $request->query('guid');
        // Ambil data dari session berdasarkan GUID objek
        $savedJobs = session()->get('pekerjaan_terpilih.' . $guid, []);

        return response()->json([
            'status' => 'success',
            'data' => $savedJobs
        ]);
    }

    public function saveJob(Request $request)
    {
        $guid = $request->input('guid');
        $job = $request->input('job'); // Array: {Nama_Pekerjaan: "..."}

        $sessionKey = 'pekerjaan_terpilih.' . $guid;
        $currentJobs = session()->get($sessionKey, []);

        // Cek Duplikasi
        $exists = false;
        foreach ($currentJobs as $existingJob) {
            if ($existingJob['Nama_Pekerjaan'] === $job['Nama_Pekerjaan']) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $currentJobs[] = $job;
            session()->put($sessionKey, $currentJobs);
        }

        return response()->json([
            'status' => 'success',
            'data' => $currentJobs
        ]);
    }

    public function removeJob(Request $request)
    {
        $guid = $request->input('guid');
        $jobName = $request->input('job_name');
        $sessionKey = 'pekerjaan_terpilih.' . $guid;

        $currentJobs = session()->get($sessionKey, []);

        // Hapus item array berdasarkan nama
        $updatedJobs = array_filter($currentJobs, function($job) use ($jobName) {
            return $job['Nama_Pekerjaan'] !== $jobName;
        });

        // Re-index array agar urut (0, 1, 2...)
        $updatedJobs = array_values($updatedJobs);

        session()->put($sessionKey, $updatedJobs);

        return response()->json([
            'status' => 'success',
            'data' => $updatedJobs
        ]);
    }
}
