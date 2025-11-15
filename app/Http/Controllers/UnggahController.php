<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;

Class UnggahController extends Controller{
    public function index(){
        return view('unggah.index');
    }

    public function upload(Request $request){
        $request->validate([
            'file' => 'required|extensions:ifc',
        ]);

        $file = $request->file('file');
        $namaFile = time().'_'.$file->getClientOriginalName();
        $file ->move(public_path('uploads/file'), $namaFile);

        return response()->json([
            'success' => true,
            'message' => 'File berhasil diunggah!',
            'filename' => $namaFile
        ]);
    }
}