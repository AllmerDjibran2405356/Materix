<?php 

namespace App\Http\Controllers;

use illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DesainRumah;

class HomeController extends Controller
{
    public function index()
    {

        $user = Auth::user();
        $project = DesainRumah::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
            
        $massage = $project->isEmpty()
        ? "Belum ada proyek desain rumah yang kamu unggah😅 
           Yuk mulai proyek pertamamu!" : null;

        return view('Page.HomePage', compact('user', 'project', 'massage'));
    }
}
