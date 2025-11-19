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
            'file' => 'required|file|extensions:ifc', // max 50MB
        ]);

        $file = $request->file('file');

        if (!$file) {
            return back()->withErrors('Upload gagal! File tidak ditemukan.');
        }

        // Nama file unik
        $Nama_Desain = time() . '_' . $file->getClientOriginalName();

        // Simpan menggunakan filesystem (public storage)
        $filepath = Storage::disk('public')->putFileAs(
            'uploads/ifc',
            $file,
            $Nama_Desain
        );

        // Simpan metadata ke database
        DesainRumah::create([
            'id_user'        => Auth::user()->id, // pastikan sudah login
            'Nama_Desain'    => pathinfo($Nama_Desain, PATHINFO_FILENAME),
            'Tanggal_Dibuat' => now(),
            'Nama_File'      => $filepath,
        ]);

        return redirect()->back()->with('success', 'File berhasil diunggah!');
    }
}
