<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DesainRumah;

class HomeController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

        $user = Auth::user();
        $projects = DesainRumah::where('id_user', $user->id)
            ->orderBy('Tanggal_dibuat', 'desc')
            ->take(4)
            ->get();

        $message = $projects->isEmpty()
        ? "Belum ada proyek desain rumah yang kamu unggah😅
           Yuk mulai proyek pertamamu!" : null;

        return view('Page.HomePage', compact('user', 'projects', 'message'));
    }

    public function show($id)
    {
        $project = DesainRumah::findOrFail($id);

        return view('Kalkulasi.show', compact('project'));
    }
}
