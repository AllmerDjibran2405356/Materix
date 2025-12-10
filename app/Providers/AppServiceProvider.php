<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // <--- 1. JANGAN LUPA TAMBAHKAN BARIS INI

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Paginator::useBootstrapFive(); // <--- 2. TAMBAHKAN BARIS INI
    }
}
