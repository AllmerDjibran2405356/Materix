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
        $project = DesainRumah::where('id_user', $user->id)
            ->orderBy('Tanggal_dibuat', 'desc')
            ->take(4)
            ->get();
            
        $massage = $project->isEmpty()
        ? "Belum ada proyek desain rumah yang kamu unggah😅 
           Yuk mulai proyek pertamamu!" : null;

        return view('Page.HomePage', compact('user', 'project', 'massage'));
    }
}
