<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;

Class UnggahController extends Controller{
    public function index(){
        return view('page.unggah');
    }

    public function upload(Request $request){
        $request->validate([
            'file' => 'required|mimes:ifc|max:51200',
        ]);

        $file = $request->file('file');
        $namaFile = time().'_'.$file->getClientOriginalName();
        $file ->move(public_path('uploads/ifc'), $namaFile);

        return response()->json([
            'success' => true,
            'message' => 'File berhasil diunggah!',
            'filename' => $namaFile
        ]);
    }
}