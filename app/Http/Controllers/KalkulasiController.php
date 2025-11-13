<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KalkulasiController extends Controller
{
    public function index()
    {
        return view('Page.Kalkulasi'); // nanti kamu bisa ganti sesuai nama file blade-mu
    }
}
