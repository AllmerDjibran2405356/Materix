<?php

namespace App\Http\Controllers;

use App\Models\DesainRumah;

class DetailProyekController extends Controller
{
   public function show(int $id)
{
    $project = DesainRumah::where('ID_Desain_Rumah', $id)
        ->where('id_user', auth()->id())
        ->firstOrFail();

    return view('page.proyek_detail', compact('Project'));
}

}

    