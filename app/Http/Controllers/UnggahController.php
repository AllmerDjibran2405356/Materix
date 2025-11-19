<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;

Class UnggahController extends Controller{
    public function upload(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:ifc,IFC|max:51200',
    ]);

    $file = $request->file('file');
    $Nama_Desain = time() . '_' . $file->getClientOriginalName();
    $path = 'uploads/ifc';

    // Simpan file fisik
    $file->move(public_path($path), $Nama_Desain);

    // Simpan ke database
    $ifc = \App\Models\IfcFile::create([
        'nama_desain' => $Nama_Desain,
        'filepath' => $path . '/' . $Nama_Desain,
        'filesize' => $file->getSize(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'File berhasil diunggah & disimpan ke database!',
        'data'    => $ifc
    ]);
}
}