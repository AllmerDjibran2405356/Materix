<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PengaturanController; 
use App\Http\Controllers\HomeController;   
use App\Http\Controllers\KalkulasiController;    
use App\Http\Controllers\HargaBahanController;  

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

// --- Rute Login & Logout ---
// HANYA SATU ROUTE GET UNTUK LOGIN
Route::get('/login', [AuthController::class, 'loginCreate'])->name('login.form'); // GUNAKAN 'login' SAJA
Route::post('/login', [AuthController::class, 'loginStore'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Rute Registrasi ---
Route::get('/daftar', [AuthController::class, 'registerCreate'])->name('daftar.form');
Route::post('/daftar', [AuthController::class, 'registerStore'])->name('daftar.submit');

// --- Rute Pengaturan Akun ---
Route::get('/pengaturan', [PengaturanController::class, 'index'])
    ->name('pengaturan')
    ->middleware('auth');
Route::post('/pengaturan/info', [PengaturanController::class, 'updateInfo'])
    ->name('pengaturan.updateInfo')
    ->middleware('auth');

Route::post('/pengaturan/password', [PengaturanController::class, 'updatePassword'])
    ->name('pengaturan.updatePassword')
    ->middleware('auth');

Route::post('/pengaturan/avatar', [PengaturanController::class, 'updateAvatar'])
    ->name('pengaturan.updateAvatar')
    ->middleware('auth');

Route::post('/pengaturan/cek-sandi', [PengaturanController::class, 'cekSandiLama'])
    ->name('pengaturan.cekSandi')
    ->middleware('auth');

// --- Rute Home Page ---
Route::get('/HomePage', [HomeController::class, 'index'])
    ->name('HomePage')
    ->middleware('auth');

// --- Rute Kalkulator ---
Route::get('/kalkulator', [KalkulasiController::class, 'index'])->name('Kalkulasi.index');

// --- Rute Harga Bahan ---
Route::get('/harga-bahan', [HargaBahanController::class, 'index'])->name('Bahan.index');