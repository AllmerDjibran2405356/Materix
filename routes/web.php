<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KalkulasiController;
use App\Http\Controllers\HargaBahanController;
use App\Http\Controllers\HasilAnalisisController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\UnggahController;
use App\Http\Controllers\DetailProyekController;

Route::get('/', [LandingController::class, 'index'])->name('landing');

// --- Rute Login & Logout ---
Route::get('/login', [AuthController::class, 'loginCreate'])->name('login.form');
Route::post('/login', [AuthController::class, 'loginStore'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Rute Registrasi ---
Route::get('/daftar', [AuthController::class, 'registerCreate'])->name('daftar.form');
Route::post('/daftar', [AuthController::class, 'registerStore'])->name('daftar.submit');

// --- Rute Pengaturan Akun ---
Route::middleware('auth')->group(function () {
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan');
    Route::post('/pengaturan/info', [PengaturanController::class, 'updateInfo'])->name('pengaturan.updateInfo');
    Route::post('/pengaturan/password', [PengaturanController::class, 'updatePassword'])->name('pengaturan.updatePassword');
    Route::post('/pengaturan/avatar', [PengaturanController::class, 'updateAvatar'])->name('pengaturan.updateAvatar');
    Route::post('/pengaturan/cek-sandi', [PengaturanController::class, 'cekSandiLama'])->name('pengaturan.cekSandi');

    // --- Rute Home Page ---
    Route::get('/HomePage', [HomeController::class, 'index'])->name('HomePage');
    Route::get('/kalkulasi/{id}', [KalkulasiController::class, 'index'])->name('Kalkulasi.show');
});

// Rute Kalkulator
Route::get('/kalkulator', [KalkulasiController::class, 'index'])->name('Kalkulasi.index');

// --- Rute Harga Bahan ---
Route::get('/harga-bahan', [HargaBahanController::class, 'index'])->name('Bahan.index');

// --- MATERIAL ROUTES ---
Route::prefix('projects')->group(function () {
    Route::get('/{id}/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::post('/{id}/materials', [MaterialController::class, 'store'])->name('materials.store');

    // Export
    Route::get('/{id}/materials/export-pdf', [MaterialController::class, 'exportPDF'])->name('materials.export.pdf');
    Route::get('/{id}/materials/export-excel', [MaterialController::class, 'exportExcel'])->name('materials.export.excel');

    // Kategori & Satuan
    Route::post('/kategori/store', [MaterialController::class, 'storeKategori'])->name('materials.kategori.store');
    Route::get('/kategori/list', [MaterialController::class, 'getKategori'])->name('materials.kategori.list');
    Route::post('/satuan/store', [MaterialController::class, 'storeSatuan'])->name('materials.satuan.store');
    Route::get('/satuan/list', [MaterialController::class, 'getSatuan'])->name('materials.satuan.list');
});

// API DATA
Route::prefix('api')->group(function () {
    Route::get('/bahan-list', [MaterialController::class, 'getBahanList'])->name('api.bahan-list');
    Route::get('/komponen-list', [MaterialController::class, 'getKomponenList'])->name('api.komponen-list');
    Route::get('/supplier-list', [MaterialController::class, 'getSupplierList'])->name('api.supplier-list');
});

// Rute Unggah File
Route::get('/unggah', [UnggahController::class, 'index'])->name('Unggah.index');
Route::post('/unggah', [UnggahController::class, 'upload'])->name('Unggah.upload');
Route::post('/analyze', [UnggahController::class, 'analyze'])->name('Unggah.analyze');
Route::post('/analisis', [UnggahController::class, 'showJson'])->name('Unggah.showJson');
Route::post('/unggah/remove', [UnggahController::class, 'remove'])->name('Unggah.remove');

// Upload Gambar
Route::get('/unggah-gambar', [UnggahController::class, 'unggahGambarForm'])->name('Unggah.gambar.form');
Route::post('/unggah-desain', [UnggahController::class, 'upload'])->name('unggah.upload');

// Rute Detail Proyek
Route::get('/proyek/{ID_Desain_Rumah}', [DetailProyekController::class, 'show'])->name('detail_proyek.show');
// Hasil Analisis
Route::get('/viewer/{id}', [HasilAnalisisController::class, 'view'])->name('viewer');
