<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\DesainRumah;

class DetailProyekController extends Controller
{
   public function show(int $id)
{
    $project = DesainRumah::where('ID_Desain_Rumah', $id)
        ->where('id_user', Auth::id())
        ->firstOrFail();

    return view('page.proyek_detail', compact('project'));
}

}

