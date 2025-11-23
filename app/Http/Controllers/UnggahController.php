<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\DesainRumah;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UnggahController extends Controller
{
    public function index(): View
    {
        return view('Page.Unggah');
    }

    /**
     * TAHAP 1: Upload File Fisik (Belum masuk DB)
     */
    public function upload(Request $request)
    {
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
        // Gunakan timestamp agar unik
        $Nama_Desain = time() . '_' . $file->getClientOriginalName();

        // Simpan file ke storage/public
        $dest = public_path('uploads/ifc');

        if (!file_exists($dest)) {
            mkdir($dest, 0777, true);
        }

        $file->move($dest, $Nama_Desain);

        // HAPUS KODE DB DISINI (DesainRumah::create)

        // Simpan data sementara di session untuk dipakai nanti di tahap analyze
        session([
            'uploaded_file' => $Nama_Desain,           // Nama file fisik
            'original_name' => $file->getClientOriginalName(), // Nama asli untuk DB nanti
            'file_path'     => 'uploads/ifc/' . $Nama_Desain // Path untuk DB
        ]);

        return redirect()->back()->with('success', 'File berhasil diunggah. Silakan klik Analisis.');
    }

    /**
     * TAHAP 2: Simpan ke DB & Jalankan Python
     */
    public function analyze(Request $request)
    {
        // Ambil data dari session (hasil dari fungsi upload)
        $fileName = session('uploaded_file');
        $filePath = session('file_path');
        $originalName = session('original_name');

        // 1. Cek apakah ada file yang menunggu di session
        if (!$fileName || !$filePath) {
            return back()->with('error', 'Tidak ada file untuk dianalisis. Silakan upload ulang.');
        }

        // 2. Cek fisik file apakah masih ada
        $fullPath = public_path($filePath);
        if (!file_exists($fullPath)) {
            return back()->with('error', "File fisik hilang: $fullPath");
        }

        // 3. BARU SIMPAN KE DATABASE DISINI
        // Kita gunakan try-catch agar jika python error, kita bisa handle (opsional)
        $desain = DesainRumah::create([
            'id_user'        => Auth::id(),
            'Nama_Desain'    => pathinfo($fileName, PATHINFO_FILENAME), // Atau $originalName
            'Tanggal_Dibuat' => now(),
            'Nama_File'      => $filePath,
        ]);

        // 4. Jalankan Script Python
        $pythonVenv   = 'C:\\Users\\allme\\Documents\\python_projects\\ai_engine_materix\\venv\\Scripts\\python.exe';
        $pythonScript = 'C:\\Users\\allme\\Documents\\python_projects\\ai_engine_materix\\ai_engine_materix\\engine_bim_and_ifc\\main\\parser.py';

        // Pastikan path diapit kutip untuk menangani spasi
        $command = "\"$pythonVenv\" \"$pythonScript\" \"$fullPath\" 2>&1";
        $output = shell_exec($command);

        // 5. Cek Hasil JSON
        $jsonFileName = pathinfo($fileName, PATHINFO_FILENAME) . '_ifc_data.json';
        $jsonFilePath = 'C:\\Users\\allme\\Documents\\python_projects\\ai_engine_materix\\ai_engine_materix\\engine_bim_and_ifc\\data\\processed\\' . $jsonFileName;

        if (!file_exists($jsonFilePath)) {
            // Opsional: Jika gagal analisis, apakah data DB mau dihapus lagi?
            // $desain->delete();
            return back()->with('error', 'Analisis gagal, file JSON output tidak ditemukan. Log: ' . $output);
        }

        // Bersihkan session upload karena sudah masuk DB
        session()->forget(['uploaded_file', 'file_path', 'original_name']);

        // Redirect ke viewer dengan ID baru
        return redirect()->route('viewer', ['id' => $desain->ID_Desain_Rumah]);
    }

    public function remove(Request $request)
    {
        $filename = session('uploaded_file');

        // Hapus file fisik (DB belum ada, jadi aman hanya hapus file)
        if ($filename) {
            $filepath = public_path('uploads/ifc/' . $filename);
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }

        // Bersihkan session
        session()->forget(['uploaded_file', 'file_path', 'original_name']);

        return redirect()->back()->with('success', 'File dibatalkan.');
    }
}
