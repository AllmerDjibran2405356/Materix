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
        $fileName  = session('uploaded_file');
        $desain_id = session('desain_id');

        if (!$fileName) {
            return back()->with('error', 'Tidak ada file untuk dianalisis.');
        }

        // Path asli file IFC (Laravel storage)
        $fullPath = storage_path('app/public/uploads/ifc/' . $fileName);

        // Perbaiki slash path Windows
        $fullPath = str_replace('/', '\\', $fullPath);

        if (!file_exists($fullPath)) {
            return back()->with('error', "File IFC tidak ditemukan di server: $fullPath");
        }

        // Path Python environment & script
        $pythonVenv  = 'C:\\Users\\allme\\Documents\\python_projects\\ai_engine_materix\\venv\\Scripts\\python.exe';
        $pythonScript = 'C:\\Users\\allme\\Documents\\python_projects\\ai_engine_materix\\ai_engine_materix\\engine_bim_and_ifc\\main\\parser.py';

        // Buat command lengkap
        $command = "\"$pythonVenv\" \"$pythonScript\" \"$fullPath\" 2>&1";

        // Jalankan Python
        $output = shell_exec($command);

        // File JSON hasil parsing
        $jsonFileName = pathinfo($fileName, PATHINFO_FILENAME) . '_ifc_data.json';
        $jsonFilePath = 'C:\\Users\\allme\\Documents\\python_projects\\ai_engine_materix\\ai_engine_materix\\engine_bim_and_ifc\\data\\processed\\' . $jsonFileName;

        $jsonData = [];
        if (file_exists($jsonFilePath)) {
            $jsonData = json_decode(file_get_contents($jsonFilePath), true);
        }

        // Log debug
        $logFile = storage_path('logs/python_exec.log');
        file_put_contents($logFile, "CMD: $command\n", FILE_APPEND);
        file_put_contents($logFile, "OUTPUT:\n$output\n\n", FILE_APPEND);

        return view('Page.HasilAnalisis', [
            'output'    => $output ?: '[EMPTY OUTPUT]',
            'jsonData'  => $jsonData,
            'desain_id' => $desain_id
        ]);
    }
}
