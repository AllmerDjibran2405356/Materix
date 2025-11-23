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
        $jsonPath = "C:\\Users\\allme\\Documents\\python_projects\\ai_engine_materix\\ai_engine_materix\\engine_bim_and_ifc\\data\\processed\\{$desain->Nama_Desain}_ifc_data.json";

        $data = [];
        if (file_exists($jsonPath)) {
            $data = json_decode(file_get_contents($jsonPath), true);
        }

        // 2. IFC File Logic
        // File fisik ada di: storage/app/public/uploads/ifc/NamaFile.ifc
        // Pastikan Anda sudah menjalankan: php artisan storage:link
        $filename = $desain->Nama_Desain . '.ifc';
        $publicPath = public_path('uploads/ifc/' . $filename);

        $ifcUrl = '';

        if (file_exists($publicPath)) {
            // Menghasilkan URL publik: http://domain/storage/uploads/ifc/NamaFile.ifc
            $ifcUrl = asset('uploads/ifc/' . $filename);
        }

        return view('Page.HasilAnalisis', compact('desain', 'data', 'ifcUrl'));
    }
}
