<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    // tampilkan halaman landing
    public function index()
    {
        // view resources/views/landing.blade.php
        return view('Page.landing');
    }
}
