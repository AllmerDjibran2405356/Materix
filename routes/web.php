<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// --- Rute Registrasi ---
Route::get('/', [RegisterController::class, 'create'])->name('register.form');
Route::post('/', [RegisterController::class, 'store'])->name('register.submit');

// --- Rute Pengaturan ---
//Route::get('/pengaturan', function () {
//    return view('UI_Pengaturan.pengaturan');
//})->name('pengaturan');