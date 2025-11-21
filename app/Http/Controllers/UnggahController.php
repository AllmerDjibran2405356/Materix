<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\DesainRumah;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UnggahController extends Controller
{
    /**
     * Halaman Upload
     */
    public function index(): View
    {
        return view('Page.Unggah');
    }

    /**
     * Proses Upload File IFC
     */
    public function upload(Request $request)
    {
        // Validasi file
        $request->validate([
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $allowed = ['ifc'];
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, $allowed)) {
                        $fail('The file must be type: .ifc');
                    }
                }
            ],
        ]);

        $file = $request->file('file');
        $Nama_Desain = time() . '_' . $file->getClientOriginalName();

        // Simpan file ke storage/public
        $filepath = Storage::disk('public')->putFileAs(
            'uploads/ifc',
            $file,
            $Nama_Desain
        );

        // Simpan metadata ke database
        $desain = DesainRumah::create([
            'id_user'        => Auth::id(),
            'Nama_Desain'    => pathinfo($Nama_Desain, PATHINFO_FILENAME),
            'Tanggal_Dibuat' => now(),
            'Nama_File'      => $filepath,
        ]);

        // Simpan filename di session
        session([
            'uploaded_file' => $Nama_Desain,
            'desain_id'     => $desain->ID_Desain_Rumah
        ]);

        return redirect()->back()->with('success', 'File berhasil diunggah!');
    }

    /**
     * Analisis File IFC menggunakan Python
     */
    public function analyze(Request $request)
    {
        $desain_id = session('desain_id');

        if (!$desain_id) {
            return back()->with('error', 'Tidak ada desain untuk dianalisis.');
        }

        // Ambil data desain langsung dari database
        $desain = DesainRumah::find($desain_id);
        if (!$desain) {
            return back()->with('error', 'Desain tidak ditemukan di database.');
        }

        // Path file IFC
        $fullPath = storage_path('app/public/' . $desain->Nama_File);

        if (!file_exists($fullPath)) {
            return back()->with('error', "File IFC tidak ditemukan: $fullPath");
        }

        $pythonVenv   = 'C:\\Users\\allme\\Documents\\python_projects\\ai_engine_materix\\venv\\Scripts\\python.exe';
        $pythonScript = 'C:\\Users\\allme\\Documents\\python_projects\\ai_engine_materix\\ai_engine_materix\\engine_bim_and_ifc\\main\\parser.py';

        $command = "\"$pythonVenv\" \"$pythonScript\" \"$fullPath\" 2>&1";

        $output = shell_exec($command);

        // Lokasi JSON hasil parsing
        $jsonFileName = pathinfo($desain->Nama_File, PATHINFO_FILENAME) . '_ifc_data.json';
        $jsonFilePath = 'C:\\Users\\allme\\Documents\\python_projects\\ai_engine_materix\\ai_engine_materix\\engine_bim_and_ifc\\data\\processed\\' . $jsonFileName;

        if (!file_exists($jsonFilePath)) {
            return back()->with('error', 'Analisis gagal, file JSON tidak ditemukan.');
        }

        return redirect()->route('viewer', $desain_id);
    }

}
