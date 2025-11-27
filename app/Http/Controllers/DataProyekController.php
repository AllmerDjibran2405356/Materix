<?php

namespace App\Http\Controllers;

use App\Models\DesainRumah;
use Illuminate\Http\Request;

class DataProyekController extends controller
{
    public function index()
    {
        return view('Page.DataBahanDanProdusen');
    }
}

?>
