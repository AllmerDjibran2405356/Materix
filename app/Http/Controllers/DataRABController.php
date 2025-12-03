<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DesainRumah;
use App\Models\User;
use App\Models\RekapKebutuhanBahanProyek;

class DataRABController extends Controller
{
    public function index($id)
    {
        $recaps = RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $id)
        ->get();

        $message = $recaps->isEmpty() ? "empty data" : null;

        return view('Page.RekapKebutuhanBahanProyek', compact('rekap', 'message'));
    }
}
