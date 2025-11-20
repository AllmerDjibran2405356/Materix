<?php

namespace App\Http\Controllers;

use App\Models\DesainRumah;
use Illuminate\Http\Request;

class ProyekController extends Controller
{
    public function show($ID_Desain_Rumah)
    {
        $proyek = DesainRumah::findOrFail($ID_Desain_Rumah);

        return view('page.detail-proyek', compact('proyek'));
    }
}

    