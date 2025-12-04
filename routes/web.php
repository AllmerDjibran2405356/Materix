<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DaftarProyekController;
use App\Http\Controllers\DataBahanDanProdusenController;
use App\Http\Controllers\DataProyekController;
use App\Http\Controllers\DataRABController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KalkulasiController;
use App\Http\Controllers\HargaBahanController;
use App\Http\Controllers\HasilAnalisisController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\UnggahController;
use App\Http\Controllers\DetailProyekController;
use App\Http\Controllers\KelolaLaporanProyekController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/login', [AuthController::class, 'loginCreate'])->name('login.form');
Route::post('/login', [AuthController::class, 'loginStore'])->name('login.submit');
Route::get('/daftar', [AuthController::class, 'registerCreate'])->name('daftar.form');
Route::post('/daftar', [AuthController::class, 'registerStore'])->name('daftar.submit');

// IFC File Download Route
Route::get('/download-ifc/{filename}', [HasilAnalisisController::class, 'downloadIfc'])->name('ifc.download');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Account Settings
    Route::prefix('pengaturan')->group(function () {
        Route::get('/', [PengaturanController::class, 'index'])->name('pengaturan');
        Route::post('/info', [PengaturanController::class, 'updateInfo'])->name('pengaturan.updateInfo');
        Route::post('/password', [PengaturanController::class, 'updatePassword'])->name('pengaturan.updatePassword');
        Route::post('/avatar', [PengaturanController::class, 'updateAvatar'])->name('pengaturan.updateAvatar');
        Route::post('/cek-sandi', [PengaturanController::class, 'cekSandiLama'])->name('pengaturan.cekSandi');
    });

    // Dashboard
    Route::get('/HomePage', [HomeController::class, 'index'])->name('HomePage');

    // Projects
    Route::get('/daftarProyek', [DaftarProyekController::class, 'index'])->name('DaftarProyek.index');
    Route::get('/daftarProyek/{id}', [DaftarProyekController::class, 'show'])->name('DaftarProyek.show');

    // Material Prices
    Route::get('/harga-bahan', [HargaBahanController::class, 'index'])->name('Bahan.index');

    // Upload
    Route::prefix('unggah')->group(function () {
        Route::get('/', [UnggahController::class, 'index'])->name('Unggah.index');
        Route::post('/', [UnggahController::class, 'upload'])->name('Unggah.upload');
        Route::post('/analyze', [UnggahController::class, 'analyze'])->name('Unggah.analyze');
        Route::post('/analisis', [UnggahController::class, 'showJson'])->name('Unggah.showJson');
        Route::post('/remove', [UnggahController::class, 'remove'])->name('Unggah.remove');
        Route::get('/gambar', [UnggahController::class, 'unggahGambarForm'])->name('Unggah.gambar.form');
    });

    // 3D Viewer
    Route::get('/hasil-analisis/{id}', [HasilAnalisisController::class, 'view'])->name('hasil.analisis.view');
    Route::get('/viewer/{id}', [HasilAnalisisController::class, 'view'])->name('viewer');

    // Project Detail
    Route::get('/proyek/{ID_Desain_Rumah}', [DetailProyekController::class, 'show'])->name('detailProyek.show');

    // Project Data
    Route::get('/data-proyek/{id}', [DataProyekController::class, 'index'])->name('dataProyek.index');

    // ========== TAMBAHAN ROUTE UNTUK FITUR BARU ==========
    // Supplier Management
    Route::prefix('supplier')->group(function () {
        Route::post('/tambah', [DataProyekController::class, 'tambahSupplier'])->name('supplier.tambah');
        Route::post('/tambah-alamat', [DataProyekController::class, 'tambahAlamatSupplier'])->name('supplier.tambahAlamat');
        Route::post('/tambah-kontak', [DataProyekController::class, 'tambahKontakSupplier'])->name('supplier.tambahKontak');
        Route::delete('/alamat/{id}', [DataProyekController::class, 'hapusAlamatSupplier'])->name('supplier.hapusAlamat');
        Route::delete('/kontak/{id}', [DataProyekController::class, 'hapusKontakSupplier'])->name('supplier.hapusKontak');
        Route::get('/{id}/edit', [DataProyekController::class, 'editSupplier'])->name('supplier.edit');
        Route::put('/{id}', [DataProyekController::class, 'updateSupplier'])->name('supplier.update');
    });

    // Harga Bahan Management
    Route::prefix('harga-bahan')->group(function () {
        Route::post('/simpan', [DataProyekController::class, 'simpanHargaBahan'])->name('bahan.simpanHarga');
        Route::get('/{id}/edit', [DataProyekController::class, 'editHargaBahan'])->name('harga.edit');
        Route::put('/{id}', [DataProyekController::class, 'updateHargaBahan'])->name('harga.update'); // <-- INI YANG PENTING
    });

    // Rekap Management
    Route::post('/rekap/update-supplier', [DataProyekController::class, 'updateSupplierRekap'])->name('rekap.updateSupplier');
    // ========== END TAMBAHAN ROUTE ==========

    // Existing Routes...
    Route::get('/rekap/get-harga-bahan', [DataProyekController::class, 'getHargaBahan'])->name('rekap.get-harga-bahan');
    Route::post('/rekap/update-harga', [DataProyekController::class, 'updateHarga'])->name('rekap.updateHarga');
    Route::post('/bahan/simpan-harga', [DataProyekController::class, 'simpanHargaBahan'])->name('bahan.simpanHarga');

    // Materials
    Route::prefix('projects')->group(function () {
        Route::get('/{id}/materials', [MaterialController::class, 'index'])->name('materials.index');
        Route::post('/{id}/materials', [MaterialController::class, 'store'])->name('materials.store');
        Route::get('/{id}/materials/export-pdf', [MaterialController::class, 'exportPDF'])->name('materials.export.pdf');
        Route::get('/{id}/materials/export-excel', [MaterialController::class, 'exportExcel'])->name('materials.export.excel');
        Route::post('/kategori/store', [MaterialController::class, 'storeKategori'])->name('materials.kategori.store');
        Route::get('/kategori/list', [MaterialController::class, 'getKategori'])->name('materials.kategori.list');
        Route::post('/satuan/store', [MaterialController::class, 'storeSatuan'])->name('materials.satuan.store');
        Route::get('/satuan/list', [MaterialController::class, 'getSatuan'])->name('materials.satuan.list');
    });
});

// API Routes (Authenticated)
Route::prefix('api')->middleware('auth')->group(function () {
    // API untuk mendapatkan harga berdasarkan bahan dan supplier
    Route::get('/harga/{bahanId}/{supplierId}', function($bahanId, $supplierId) {
        $harga = \App\Models\ListHargaBahan::where('ID_Bahan', $bahanId)
            ->where('ID_Supplier', $supplierId)
            ->orderBy('Tanggal_Update_Data', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'harga' => $harga ? $harga->Harga_Per_Satuan : null
        ]);
    });

    // API untuk mendapatkan semua harga (cache)
    Route::get('/harga-cache', function() {
        $hargaData = \App\Models\ListHargaBahan::select('ID_Bahan', 'ID_Supplier', 'Harga_Per_Satuan')
            ->orderBy('Tanggal_Update_Data', 'desc')
            ->get()
            ->reduce(function($carry, $item) {
                $key = $item->ID_Bahan . '-' . $item->ID_Supplier;
                if (!isset($carry[$key])) {
                    $carry[$key] = $item->Harga_Per_Satuan;
                }
                return $carry;
            }, []);

        return response()->json($hargaData);
    });

    Route::get('/bahan-all', function() {
        $bahan = \App\Models\ListBahan::orderBy('Nama_Bahan')->get(['ID_Bahan', 'Nama_Bahan']);
        return response()->json($bahan);
    })->name('api.bahan-all');

    Route::get('/supplier-all', function() {
        $supplier = \App\Models\ListSupplier::orderBy('Nama_Supplier')->get(['ID_Supplier', 'Nama_Supplier']);
        return response()->json($supplier);
    })->name('api.supplier-all');

    // Material APIs
    Route::get('/bahan-list', [MaterialController::class, 'getBahanList'])->name('api.bahan-list');
    Route::get('/komponen-list', [MaterialController::class, 'getKomponenList'])->name('api.komponen-list');
    Route::get('/supplier-list', [MaterialController::class, 'getSupplierList'])->name('api.supplier-list');
    Route::post('/bahan/store', [MaterialController::class, 'storeBahan'])->name('api.bahan-store');
    Route::post('/supplier/store', [MaterialController::class, 'storeSupplier'])->name('api.supplier-store');

    // Hasil Analisis APIs
    Route::get('/cari-komponen', [HasilAnalisisController::class, 'cariKomponen'])->name('api.cari_komponen');
    Route::get('/get-jobs', [HasilAnalisisController::class, 'getJobs'])->name('api.get_jobs');
    Route::post('/save-job', [HasilAnalisisController::class, 'saveJob'])->name('api.save_job');
    Route::post('/remove-job', [HasilAnalisisController::class, 'removeJob'])->name('api.remove_job');
    Route::post('/final-save', [HasilAnalisisController::class, 'finalSave'])->name('api.final_save');

    // New IFC File APIs
    Route::get('/list-ifc-files', [HasilAnalisisController::class, 'listIfcFiles'])->name('api.list_ifc_files');

    // Debug API
    Route::get('/debug-info/{id}', [HasilAnalisisController::class, 'debugInfo'])->name('api.debug.info');
});

// Fallback Route
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
