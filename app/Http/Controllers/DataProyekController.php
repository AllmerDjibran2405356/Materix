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
        $suppliers = ListSupplier::orderBy('Nama_Supplier')->get();

        // Ambil semua bahan yang ada di rekap
        $bahanIds = $recaps->pluck('ID_Bahan')->unique()->toArray();

        // Ambil harga bahan terbaru untuk setiap bahan dan supplier
        $materialPrices = ListHargaBahan::whereIn('ID_Bahan', $bahanIds)
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

            SupplierAlamat::create([
                'ID_Supplier' => $supplier->ID_Supplier,
                'Alamat_Supplier' => $request->Alamat_Supplier
            ]);

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

    public function updateSupplierRekap(Request $request)
    {
        $request->validate([
            'ID_Rekap' => 'required|integer|exists:rekap_kebutuhan_bahan_proyek,ID_Rekap',
            'ID_Supplier' => 'required|integer|exists:list_supplier,ID_Supplier'
        ]);

        $recap = RekapKebutuhanBahanProyek::find($request->ID_Rekap);

        if (!$recap) {
            return redirect()->back()->with('error', 'Rekap tidak ditemukan');
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

        return redirect()->back()->with('success', 'Supplier berhasil diperbarui!');
    }

    public function simpanHargaBahan(Request $request)
    {
        // Validasi yang lebih fleksibel untuk harga
        $request->validate([
            'ID_Bahan' => 'required|exists:list_bahan,ID_Bahan',
            'ID_Supplier' => 'required|exists:list_supplier,ID_Supplier',
            'Harga_Per_Satuan' => 'required|numeric|min:1'
        ]);

        $idBahan = $request->ID_Bahan;
        $idSupplier = $request->ID_Supplier;
        $hargaInput = $request->Harga_Per_Satuan;

        // Konversi ke integer untuk menghindari masalah desimal
        $hargaInput = (int)$hargaInput;

        // Cek apakah bahan ada
        $bahan = ListBahan::findOrFail($idBahan);
        $idSatuan = $bahan->ID_Satuan_Bahan;

        DB::beginTransaction();
        try {
            // Cek apakah sudah ada harga untuk kombinasi bahan-supplier ini
            $hargaExisting = ListHargaBahan::where('ID_Bahan', $idBahan)
                ->where('ID_Supplier', $idSupplier)
                ->first();

            if ($hargaExisting) {
                // Update harga yang sudah ada
                $hargaExisting->update([
                    'Harga_Per_Satuan' => $hargaInput,
                    'ID_Satuan' => $idSatuan,
                    'Tanggal_Update_Data' => now()
                ]);
                $message = 'Harga berhasil diperbarui!';
            } else {
                // Buat harga baru
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
}
