<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\DesainRumah;
use App\Models\KomponenDesain;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnggahController extends Controller
{
    public function index(): View
    {
        return view('Page.Unggah');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', function ($attribute, $value, $fail) {
                $allowed = ['ifc'];
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, $allowed)) {
                    $fail('The file must be type: .ifc');
                }
            }],
        ]);

        $file = $request->file('file');
        $Nama_Desain = time() . '_' . $file->getClientOriginalName();
        $dest = public_path('uploads/ifc');

        if (!file_exists($dest)) {
            mkdir($dest, 0777, true);
        }

        $file->move($dest, $Nama_Desain);

        session([
            'uploaded_file' => $Nama_Desain,
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => 'uploads/ifc/' . $Nama_Desain
        ]);

        return redirect()->back()->with('success', 'File berhasil diunggah. Silakan klik Analisis.');
    }

    public function analyze(Request $request)
    {
        $fileName = session('uploaded_file');
        $filePath = session('file_path');

        if (!$fileName || !$filePath) {
            return back()->with('error', 'Tidak ada file session. Silakan upload ulang.');
        }

        $fullPath = public_path($filePath);
        if (!file_exists($fullPath)) {
            return back()->with('error', "File fisik ifc hilang.");
        }

        try {
            DB::beginTransaction();
            $desain = DesainRumah::create([
                'id_user'      => Auth::id(),
                'Nama_Desain'  => pathinfo($fileName, PATHINFO_FILENAME),
                'Tanggal_Dibuat'=> now(),
                'Nama_File'    => $filePath,
            ]);

            $pythonVenv   = 'C:\\Materix_Engine\\venv\\Scripts\\python.exe';
            $pythonScript = 'C:\\Materix_Engine\\ai_engine_materix\\engine_bim_and_ifc\\main\\parser.py';

            $command = "\"$pythonVenv\" \"$pythonScript\" \"$fullPath\" 2>&1";
            shell_exec($command);

            $jsonFileName = pathinfo($fileName, PATHINFO_FILENAME) . '_ifc_data.json';
            $jsonFilePath = 'C:\\Materix_Engine\\ai_engine_materix\\engine_bim_and_ifc\\data\\processed\\' . $jsonFileName;

            if (!file_exists($jsonFilePath)) {
                throw new \Exception("Gagal generate JSON.");
            }

            $jsonContent = file_get_contents($jsonFilePath);
            $dataKomponen = json_decode($jsonContent, true);

            foreach ($dataKomponen as $item) {
                $qty = $item['kuantitas'] ?? [];

                $idSatuan = 7;
                $textSatuan = $item['satuan_utama_hitung'] ?? '';

                if ($textSatuan == 'm3') {
                    $idSatuan = 1;
                } elseif ($textSatuan == 'm') {
                    $idSatuan = 8;
                }

                $idRule = 1;

                KomponenDesain::create([
                    'ID_Desain_Rumah' => $desain->ID_Desain_Rumah,
                    'Nama_Komponen'   => $item['nama'] ?? 'Tanpa Nama',
                    'Ifc_Guid'        => $item['guid'],
                    'Label_Cad'       => $item['label_cad'],
                    'ID_Satuan_Ukur'  => $idSatuan,
                    'ID_Rule'         => $idRule,
                    'Panjang'         => $qty['panjang'] ?? 0,
                    'Lebar'           => $qty['tebal'] ?? 0,
                    'Tinggi'          => $qty['tinggi'] ?? 0
                ]);
            }

            DB::commit();

            session()->forget(['uploaded_file', 'file_path', 'original_name']);

            return redirect()->route('viewer', ['id' => $desain->ID_Desain_Rumah])
                             ->with('success', 'Analisis Selesai!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function remove(Request $request)
    {
        $filename = session('uploaded_file');
        if ($filename) {
            $filepath = public_path('uploads/ifc/' . $filename);
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        session()->forget(['uploaded_file', 'file_path', 'original_name']);
        return redirect()->back()->with('success', 'File dibatalkan.');
    }
}
