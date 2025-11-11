<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PengaturanController;


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


// --- Rute Login & Logout (Sekarang menunjuk ke AuthController) ---
Route::get('/login', [AuthController::class, 'loginCreate'])->name('login.form');
Route::post('/login', [AuthController::class, 'loginStore'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Rute Registrasi (Sudah benar menunjuk ke AuthController) ---
// Catatan: Pastikan nama method di AuthController adalah 'registerCreate' dan 'registerStore'
Route::get('/daftar', [AuthController::class, 'registerCreate'])->name('daftar.form');
Route::post('/daftar', [AuthController::class, 'registerStore'])->name('daftar.submit');

Route::get('/pengaturan', [PengaturanController::class, 'index'])
    ->name('pengaturan')
    ->middleware('auth'); // <-- 'auth' berarti HANYA user login yang bisa akses

Route::post('/pengaturan/info', [PengaturanController::class, 'updateInfo'])
    ->name('pengaturan.updateInfo')
    ->middleware('auth');

Route::post('/pengaturan/password', [PengaturanController::class, 'updatePassword'])
    ->name('pengaturan.updatePassword')
    ->middleware('auth');

Route::post('/pengaturan/cek-sandi', [PengaturanController::class, 'cekSandiLama'])
    ->name('pengaturan.cekSandi')
    ->middleware('auth');

  
