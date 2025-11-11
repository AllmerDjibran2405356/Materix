<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\HomeController;


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

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/masuk', function () {
    return view('Page.masuk');
})->name('masuk');

//Route::get('/daftar', function () {
  //  return view('Page.daftar');
//})->name('daftar');

// --- Rute Registrasi ---
Route::get('/daftar', [AuthController::class, 'create'])->name('daftar.form');
Route::post('/daftar', [AuthController::class, 'store'])->name('daftar.submit');

// --- Rute Pengaturan ini nanti di buka pas aku udah bikin yang pengaturan ---
//Route::get('/pengaturan', function () {
//    return view('UI_Pengaturan.pengaturan');
//})->name('pengaturan');

Route::get('/', [HomeController::class, 'index'])->name('home');


