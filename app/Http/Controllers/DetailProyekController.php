<?php

namespace App\Http\Controllers;

use App\Models\DesainRumah;
use Illuminate\Http\Request;

class ProyekController extends Controller
{
   public function show($id)
{
    $project = DesainRumah::where('ID_Desain_Rumah', $id)
        ->where('id_user', auth()->id()) 
        ->firstOrFail();

    return view('page.proyek-detail', compact('Project'));
}

}

    