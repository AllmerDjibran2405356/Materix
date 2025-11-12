<?php

namespace App\Http\Controllers;

// Import trait untuk fitur otorisasi dan validasi standar Laravel
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
// Import kelas Controller dasar dari framework Laravel, lalu di-alias menjadi BaseController
use Illuminate\Routing\Controller as BaseController; 

class Controller extends BaseController // <-- MEMASTIKAN HOME CONTROLLER MENDAPAT SEMUA FUNGSI BASE CONTROLLER
{
    // Gunakan trait yang sudah di-import
    use AuthorizesRequests, ValidatesRequests;
}