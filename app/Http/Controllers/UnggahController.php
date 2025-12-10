<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\DesainRumah;
use App\Models\KomponenDesain;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class UnggahController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================
    // 1. PAGE UPLOAD
    // =========================================================
    public function index(): View
    {
        return view('Page.Unggah');
    }

    // =========================================================
    // 2. UPLOAD FILE IFC
    // =========================================================
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

        $ifcFile = $request->file('file');
        $uploadDir = public_path('uploads/ifc');

        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0777, true);
        }

        $newName = time() . '_' . $ifcFile->getClientOriginalName();
        $ifcFile->move($uploadDir, $newName);

        session([
            'uploaded_file' => $newName,
            'file_path'     => 'uploads/ifc/' . $newName,
            'original_name' => $ifcFile->getClientOriginalName()
        ]);

        return back()->with('success', 'File berhasil diunggah.');
    }

    // =========================================================
    // 3. ANALYZE (SEMUA PYTHON + JSON)
    // =========================================================
    public function analyze(Request $request)
    {
        $fileName = session('uploaded_file');
        $filePath = session('file_path');

        if (!$fileName || !$filePath) {
            return back()->with('error', 'Tidak ada file untuk dianalisis.');
        }

        $fullPath = public_path($filePath);
        if (!file_exists($fullPath)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        try {
            DB::beginTransaction();

            // ======== CALL PYTHON FASTAPI ==========
            $pythonUrl = 'https://d931e775-4846-4537-8226-23568bedb232-00-3etr9atpy4f3d.pike.replit.dev/convert-ifc';

            $response = Http::attach(
                'file',
                fopen($fullPath, 'r'),
                $fileName
            )->post($pythonUrl);

            if (!$response->ok()) {
                throw new \Exception("Python API error: " . $response->body());
            }

            $result = $response->json();

            if (!isset($result['status']) || $result['status'] !== 'success') {
                throw new \Exception("Python gagal: " . json_encode($result));
            }

            $dataKomponen = $result['data'];

            // ======== SAVE JSON TO LARAVEL ==========
            $processedDir = base_path('public/IFCprocessed');

            if (!File::exists($processedDir)) {
                File::makeDirectory($processedDir, 0777, true);
            }

            $namaDesain = pathinfo($fileName, PATHINFO_FILENAME);

            $jsonName = $namaDesain . '_ifc_data.json';
            $jsonPath = $processedDir . '/' . $jsonName;

            File::put($jsonPath, json_encode($dataKomponen, JSON_PRETTY_PRINT));

            // ======== SAVE DESIGN ==========
            $desain = DesainRumah::create([
                'id_user'        => Auth::id(),
                'Nama_Desain'    => pathinfo($fileName, PATHINFO_FILENAME),
                'Tanggal_Dibuat' => now(),
                'Nama_File'      => $filePath,
                'Json_File'      => 'IFCprocessed/' . $jsonName,
            ]);

            // ======== SAVE COMPONENTS ==========
            foreach ($dataKomponen as $item) {
                $qty = $item['kuantitas'] ?? [];

                $idSatuan = 7; // default m2
                $sat = strtolower($item['satuan_utama_hitung'] ?? '');

                if ($sat === 'm3') $idSatuan = 1;
                if ($sat === 'm')  $idSatuan = 8;

                KomponenDesain::create([
                    'ID_Desain_Rumah' => $desain->ID_Desain_Rumah,
                    'Nama_Komponen'   => $item['nama'] ?? 'Tanpa Nama',
                    'Ifc_Guid'        => $item['guid'] ?? '',
                    'Label_Cad'       => $item['label_cad'] ?? '',
                    'ID_Satuan_Ukur'  => $idSatuan,
                    'Panjang'         => $qty['panjang'] ?? 0,
                    'Lebar'           => $qty['tebal'] ?? 0,
                    'Tinggi'          => $qty['tinggi'] ?? 0,
                ]);
            }

            DB::commit();

            session()->forget(['uploaded_file', 'file_path', 'original_name']);

            return redirect()
                ->route('viewer', ['id' => $desain->ID_Desain_Rumah])
                ->with('success', 'Analisis selesai.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // 4. REMOVE FILE
    // =========================================================
    public function remove(Request $request)
    {
        $filename = session('uploaded_file');
        if ($filename) {
            $path = public_path('uploads/ifc/' . $filename);
            if (file_exists($path)) unlink($path);
        }

        session()->forget(['uploaded_file', 'file_path', 'original_name']);

        return back()->with('success', 'File dihapus.');
    }
}
