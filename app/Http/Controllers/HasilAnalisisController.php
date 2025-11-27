<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DesainRumah;
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
}
