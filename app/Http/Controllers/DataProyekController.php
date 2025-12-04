<?php

namespace App\Http\Controllers;

use App\Models\DesainRumah;
use App\Models\RekapKebutuhanBahanProyek;
use App\Models\ListHargaBahan;
use App\Models\ListSupplier;
use App\Models\SupplierAlamat;
use App\Models\SupplierKontak;
use App\Models\ListBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataProyekController extends Controller
{
    public function index($id)
    {
        $project = DesainRumah::findOrFail($id);
        $recaps = RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $id)->get();

        // Load supplier dengan alamat dan kontak (ONE-TO-MANY)
        $suppliers = ListSupplier::with(['alamat', 'kontak'])->orderBy('Nama_Supplier')->get();

        // Ambil semua bahan yang ada di rekap
        $bahanIds = $recaps->pluck('ID_Bahan')->unique()->toArray();

        // Ambil harga bahan terbaru untuk setiap bahan dan supplier
        $materialPrices = ListHargaBahan::whereIn('ID_Bahan', $bahanIds)
            ->with(['bahan', 'supplier'])
            ->orderBy('Tanggal_Update_Data', 'desc')
            ->get();

        // Ambil data bahan untuk dropdown
        $bahanList = ListBahan::whereIn('ID_Bahan', $bahanIds)
            ->pluck('Nama_Bahan', 'ID_Bahan')
            ->toArray();

        $message = $recaps->isEmpty() ? "empty data" : null;

        return view('Page.DataBahanDanProdusen', compact(
            'project',
            'recaps',
            'suppliers',
            'materialPrices',
            'bahanList',
            'message'
        ));
    }

    public function tambahSupplier(Request $request)
    {
        $request->validate([
            'Nama_Supplier' => 'required|string|max:255',
            'Alamat_Supplier' => 'required|string|max:255',
            'Kontak_Supplier' => 'required|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $supplier = ListSupplier::create([
                'Nama_Supplier' => $request->Nama_Supplier
            ]);

            // Tambahkan alamat pertama
            SupplierAlamat::create([
                'ID_Supplier' => $supplier->ID_Supplier,
                'Alamat_Supplier' => $request->Alamat_Supplier
            ]);

            // Tambahkan kontak pertama
            SupplierKontak::create([
                'ID_Supplier' => $supplier->ID_Supplier,
                'Kontak_Supplier' => $request->Kontak_Supplier
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Supplier berhasil ditambahkan!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error tambah supplier: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan supplier: ' . $e->getMessage());
        }
    }

    // TAMBAH ALAMAT BARU (bisa banyak)
    public function tambahAlamatSupplier(Request $request)
    {
        $request->validate([
            'ID_Supplier' => 'required|exists:list_supplier,ID_Supplier',
            'Alamat_Supplier' => 'required|string|max:255',
        ]);

        try {
            SupplierAlamat::create([
                'ID_Supplier' => $request->ID_Supplier,
                'Alamat_Supplier' => $request->Alamat_Supplier
            ]);

            return redirect()->back()->with('success', 'Alamat supplier berhasil ditambahkan!');
        } catch (\Throwable $e) {
            Log::error('Error tambah alamat supplier: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan alamat: ' . $e->getMessage());
        }
    }

    // TAMBAH KONTAK BARU (bisa banyak)
    public function tambahKontakSupplier(Request $request)
    {
        $request->validate([
            'ID_Supplier' => 'required|exists:list_supplier,ID_Supplier',
            'Kontak_Supplier' => 'required|string|max:50',
        ]);

        try {
            SupplierKontak::create([
                'ID_Supplier' => $request->ID_Supplier,
                'Kontak_Supplier' => $request->Kontak_Supplier
            ]);

            return redirect()->back()->with('success', 'Kontak supplier berhasil ditambahkan!');
        } catch (\Throwable $e) {
            Log::error('Error tambah kontak supplier: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan kontak: ' . $e->getMessage());
        }
    }

    // HAPUS ALAMAT
    public function hapusAlamatSupplier($id)
    {
        try {
            $alamat = SupplierAlamat::findOrFail($id);
            $alamat->delete();

            return redirect()->back()->with('success', 'Alamat berhasil dihapus!');
        } catch (\Throwable $e) {
            Log::error('Error hapus alamat supplier: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus alamat: ' . $e->getMessage());
        }
    }

    // HAPUS KONTAK
    public function hapusKontakSupplier($id)
    {
        try {
            $kontak = SupplierKontak::findOrFail($id);
            $kontak->delete();

            return redirect()->back()->with('success', 'Kontak berhasil dihapus!');
        } catch (\Throwable $e) {
            Log::error('Error hapus kontak supplier: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus kontak: ' . $e->getMessage());
        }
    }

    // DataProyekController.php
    public function updateSupplierRekap(Request $request)
    {
        $request->validate([
            'ID_Rekap' => 'required|integer|exists:rekap_kebutuhan_bahan_proyek,ID_Rekap',
            'ID_Supplier' => 'required|integer|exists:list_supplier,ID_Supplier'
        ]);

        DB::beginTransaction();
        try {
            $recap = RekapKebutuhanBahanProyek::find($request->ID_Rekap);

            if (!$recap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rekap tidak ditemukan'
                ], 404);
            }

            // Cari harga terbaru untuk bahan dan supplier ini
            $hargaRow = ListHargaBahan::where('ID_Bahan', $recap->ID_Bahan)
                ->where('ID_Supplier', $request->ID_Supplier)
                ->orderByDesc('Tanggal_Update_Data')
                ->first();

            $harga = $hargaRow ? (float)$hargaRow->Harga_Per_Satuan : 0;

            // Update rekap
            $recap->ID_Supplier = $request->ID_Supplier;
            $recap->Harga_Satuan_Saat_Ini = $harga;
            $recap->Total_Harga = $recap->Volume_Final * $harga;
            $recap->save();

            DB::commit();

            // Kembalikan JSON response untuk AJAX
            return response()->json([
                'success' => true,
                'message' => 'Supplier berhasil diperbarui!',
                'data' => [
                    'harga_satuan' => $harga,
                    'total_harga' => $recap->Total_Harga
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update supplier rekap: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    public function simpanHargaBahan(Request $request)
    {
        $request->validate([
            'ID_Bahan' => 'required|exists:list_bahan,ID_Bahan',
            'ID_Supplier' => 'required|exists:list_supplier,ID_Supplier',
            'Harga_Per_Satuan' => 'required|numeric|min:1'
        ]);

        $idBahan = $request->ID_Bahan;
        $idSupplier = $request->ID_Supplier;
        $hargaInput = (int)$request->Harga_Per_Satuan;

        $bahan = ListBahan::findOrFail($idBahan);
        $idSatuan = $bahan->ID_Satuan_Bahan;

        DB::beginTransaction();
        try {
            $hargaExisting = ListHargaBahan::where('ID_Bahan', $idBahan)
                ->where('ID_Supplier', $idSupplier)
                ->first();

            if ($hargaExisting) {
                $hargaExisting->update([
                    'Harga_Per_Satuan' => $hargaInput,
                    'ID_Satuan' => $idSatuan,
                    'Tanggal_Update_Data' => now()
                ]);
                $message = 'Harga berhasil diperbarui!';
            } else {
                ListHargaBahan::create([
                    'ID_Bahan' => $idBahan,
                    'ID_Supplier' => $idSupplier,
                    'Harga_Per_Satuan' => $hargaInput,
                    'ID_Satuan' => $idSatuan,
                    'Tanggal_Update_Data' => now()
                ]);
                $message = 'Harga berhasil disimpan!';
            }

            // Update semua rekap yang menggunakan bahan ini dengan supplier ini
            $recapsToUpdate = RekapKebutuhanBahanProyek::where('ID_Bahan', $idBahan)
                ->where('ID_Supplier', $idSupplier)
                ->get();

            foreach ($recapsToUpdate as $recap) {
                $recap->Harga_Satuan_Saat_Ini = $hargaInput;
                $recap->Total_Harga = $recap->Volume_Final * $hargaInput;
                $recap->save();
            }

            DB::commit();
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error simpan harga: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan harga: ' . $e->getMessage());
        }
    }

    // EDIT HARGA - GET DATA
    public function editHargaBahan($id)
    {
        try {
            $harga = ListHargaBahan::with(['bahan', 'supplier'])->findOrFail($id);

            // Ambil semua bahan untuk dropdown
            $semuaBahan = ListBahan::orderBy('Nama_Bahan')->get(['ID_Bahan', 'Nama_Bahan']);
            // Ambil semua supplier untuk dropdown
            $semuaSupplier = ListSupplier::orderBy('Nama_Supplier')->get(['ID_Supplier', 'Nama_Supplier']);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $harga->ID_Harga,
                    'id_bahan' => $harga->ID_Bahan,
                    'nama_bahan' => $harga->bahan->Nama_Bahan ?? 'Unknown',
                    'id_supplier' => $harga->ID_Supplier,
                    'nama_supplier' => $harga->supplier->Nama_Supplier ?? 'Unknown',
                    'harga' => $harga->Harga_Per_Satuan,
                    'tanggal' => $harga->Tanggal_Update_Data
                ],
                'dropdowns' => [
                    'bahan' => $semuaBahan,
                    'supplier' => $semuaSupplier
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error editHargaBahan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data harga'
            ], 500);
        }
    }

    // UPDATE HARGA - PUT
    public function updateHargaBahan(Request $request, $id)
    {
        // Debug: Log request data
        \Log::info('Update harga request:', [
            'id' => $id,
            'data' => $request->all()
        ]);

        $request->validate([
            'ID_Bahan' => 'required|exists:list_bahan,ID_Bahan',
            'ID_Supplier' => 'required|exists:list_supplier,ID_Supplier',
            'Harga_Per_Satuan' => 'required|numeric|min:1'
        ]);

        $harga = ListHargaBahan::findOrFail($id);
        $idBahanLama = $harga->ID_Bahan;
        $idSupplierLama = $harga->ID_Supplier;

        $idBahanBaru = $request->ID_Bahan;
        $idSupplierBaru = $request->ID_Supplier;
        $hargaInput = (int)$request->Harga_Per_Satuan;

        DB::beginTransaction();
        try {
            // Update harga
            $harga->update([
                'ID_Bahan' => $idBahanBaru,
                'ID_Supplier' => $idSupplierBaru,
                'Harga_Per_Satuan' => $hargaInput,
                'Tanggal_Update_Data' => now()
            ]);

            // Jika bahan atau supplier berubah, update rekap yang lama
            if ($idBahanLama != $idBahanBaru || $idSupplierLama != $idSupplierBaru) {
                // Update rekap yang menggunakan kombinasi lama
                $recapsLama = RekapKebutuhanBahanProyek::where('ID_Bahan', $idBahanLama)
                    ->where('ID_Supplier', $idSupplierLama)
                    ->get();

                foreach ($recapsLama as $recap) {
                    $recap->Harga_Satuan_Saat_Ini = 0; // Reset karena kombinasi berubah
                    $recap->Total_Harga = 0;
                    $recap->save();
                }
            }

            // Update rekap yang menggunakan kombinasi baru
            $recapsBaru = RekapKebutuhanBahanProyek::where('ID_Bahan', $idBahanBaru)
                ->where('ID_Supplier', $idSupplierBaru)
                ->get();

            foreach ($recapsBaru as $recap) {
                $recap->Harga_Satuan_Saat_Ini = $hargaInput;
                $recap->Total_Harga = $recap->Volume_Final * $hargaInput;
                $recap->save();
            }

            DB::commit();

            // Kembalikan response JSON untuk AJAX
            return response()->json([
                'success' => true,
                'message' => 'Harga berhasil diperbarui!',
                'data' => [
                    'id' => $harga->ID_Harga,
                    'harga' => $hargaInput
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error update harga: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui harga: ' . $e->getMessage()
            ], 500);
        }
    }

    // EDIT SUPPLIER - GET DATA
    public function editSupplier($id)
    {
        $supplier = ListSupplier::with(['alamat', 'kontak'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $supplier->ID_Supplier,
                'nama' => $supplier->Nama_Supplier,
                'alamat' => $supplier->alamat,
                'kontak' => $supplier->kontak
            ]
        ]);
    }

    // UPDATE SUPPLIER - PUT
    public function updateSupplier(Request $request, $id)
    {
        $request->validate([
            'Nama_Supplier' => 'required|string|max:255'
        ]);

        try {
            $supplier = ListSupplier::findOrFail($id);
            $supplier->update([
                'Nama_Supplier' => $request->Nama_Supplier
            ]);

            return redirect()->back()->with('success', 'Supplier berhasil diperbarui!');
        } catch (\Throwable $e) {
            Log::error('Error update supplier: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui supplier: ' . $e->getMessage());
        }
    }

    public function refreshTable($id)
    {
        try {
            $project = DesainRumah::findOrFail($id);
            $recaps = RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $id)
                ->with(['bahan', 'supplier'])
                ->orderBy('ID_Rekap')
                ->get();

            $suppliers = ListSupplier::orderBy('Nama_Supplier')->get();

            // HANYA render tbody bagian saja
            $html = view('partials.main-table', compact('project', 'recaps', 'suppliers'))->render();

            // SELALU kembalikan JSON untuk AJAX requests
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $recaps->count(),
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error refreshing table: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage(),
                'html' => '<tr><td colspan="7" class="text-center text-danger">Error: ' . e($e->getMessage()) . '</td></tr>'
            ], 500);
        }
    }
}
