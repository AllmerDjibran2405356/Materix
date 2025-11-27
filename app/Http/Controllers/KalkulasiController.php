<?php

namespace App\Http\Controllers;

use App\Models\DesainRumah;
use Illuminate\Http\Request;

class KalkulasiController extends Controller
{
    public function index($id)
    {
        $project = DesainRumah::findOrFail($id);
        return view('Page.Kalkulasi', compact('project'));
    }
}

?>
