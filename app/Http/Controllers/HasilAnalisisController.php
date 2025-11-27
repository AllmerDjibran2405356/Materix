<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DesainRumah;
use App\Models\KomponenDesain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HasilAnalisisController extends Controller
{
    public function view($id)
    {
        $desain = DesainRumah::where('ID_Desain_Rumah', $id)->firstOrFail();

        // 1. Ambil Data JSON (Hasil Analisis Python)
        // Pastikan path ini benar di server/local Anda
        $jsonPath = "C:\\Materix_Engine\\ai_engine_materix\\engine_bim_and_ifc\\data\\processed\\{$desain->Nama_Desain}_ifc_data.json";

        $data = [];
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            $data = json_decode($jsonContent, true);
        }

        // 2. Ambil File IFC (Model 3D)
        $filename = $desain->Nama_Desain . '.ifc';
        $publicPath = public_path('uploads/ifc/' . $filename);

        $ifcUrl = '';
        if (file_exists($publicPath)) {
            $ifcUrl = asset('uploads/ifc/' . $filename);
        }

        // Kirim $data (JSON) dan $ifcUrl ke View
        return view('Page.HasilAnalisis', compact('desain', 'data', 'ifcUrl'));
    }

    // Di HasilAnalisisController.php
    public function cariKomponen(Request $request)
    {
        try {
            // 1. Ambil Parameter
            $desainId = $request->query('desain_id');
            $nama     = $request->query('nama');
            $labelCad = $request->query('label_cad');
            $guid     = $request->query('guid');

            // Validasi: Pastikan semua data dikirim oleh Frontend
            // Karena ini Strict AND, jika salah satu kosong, query kemungkinan besar gagal/tidak valid
            if (!$desainId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'parameter desainid kosong'
                ]);
            }else if (!$nama) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'paremeter nama kosong'
                ]);
            }else if (!$labelCad) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'parameter labelcad kosong'
                ]);
            }else if (!$guid) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'parameter guid kosong'
                ]);
            }

            // 2. Query STRICT AND
            // Semua ->where() dirangkai, artinya semua syarat WAJIB terpenuhi.
            $komponen = KomponenDesain::where('ID_Desain_Rumah', $desainId)
                        ->where('Nama_Komponen', $nama)
                        ->where('Label_Cad', $labelCad)
                        ->where('Ifc_Guid', $guid)
                        ->first();

            // 3. Return Hasil
            if ($komponen) {
                return response()->json([
                    'status'      => 'found',
                    'id_komponen' => $komponen->ID_Komponen,
                    'data'        => $komponen
                ]);
            } else {
                return response()->json([
                    'status'  => 'not_found',
                    'message' => 'Data tidak ditemukan (Cek kesesuaian Nama/Label/GUID)'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
