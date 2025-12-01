<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DesainRumah;
use App\Models\User;

class DaftarProyekController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = Auth::id();

        $projects = DesainRumah::where('id_user', $userId)
            ->orderBy('Tanggal_dibuat', 'desc')
            ->get();

        $message = $projects->isEmpty()
        ? "Belum ada proyek desain rumah yang kamu unggah😅
           Yuk mulai proyek pertamamu!" : null;

        return view('Page.DaftarProyek', compact('user', 'projects', 'message'));
    }

    public function show($id)
    {
        $projects = DesainRumah::findOrFail($id);

        return view('Kalkulasi.show', compact('projects'));
    }
}
