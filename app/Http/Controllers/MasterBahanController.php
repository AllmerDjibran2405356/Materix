<?php

namespace App\Http\Controllers;

use App\Models\ListBahan;
use App\Models\ListSatuanUkur;
use App\Models\ListSupplier;
use App\Models\ListHargaBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterBahanController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('q');

        // 1. Ambil Data Bahan
        $bahans = ListBahan::with(['satuan'])
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('Nama_Bahan', 'like', "%{$keyword}%");
            })
            ->orderBy('Nama_Bahan', 'asc')
            ->paginate(20);

        // 2. Data Pendukung untuk Modal
        $suppliers = ListSupplier::orderBy('Nama_Supplier')->get();
        $satuans = ListSatuanUkur::orderBy('Nama_Satuan')->get();

        // 3. Ambil Harga untuk Bahan yang sedang tampil saja (Optimasi Query)
        // Kita mapping nanti di View agar JS bisa membacanya
        $bahanIds = $bahans->pluck('ID_Bahan')->toArray();
        $allPrices = ListHargaBahan::whereIn('ID_Bahan', $bahanIds)->get();

        return view('Page.MasterBahan', compact('bahans', 'suppliers', 'satuans', 'allPrices'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input Utama
        $request->validate([
            'Nama_Bahan' => 'required|string|max:255|unique:list_bahan,Nama_Bahan',
            'ID_Satuan_Bahan' => 'required|exists:list_satuan_ukur,ID_Satuan_Ukur',
            // Validasi array prices (opsional, tapi disarankan)
            'prices' => 'nullable|array',
            'prices.*.supplier_id' => 'required_with:prices|exists:list_supplier,ID_Supplier',
            'prices.*.harga' => 'required_with:prices',
        ]);

        // Gunakan DB Transaction agar jika simpan harga gagal, simpan bahan juga dibatalkan
        DB::beginTransaction();

        try {
            // 2. Simpan Data Bahan Utama
            $bahan = ListBahan::create([
                'Nama_Bahan' => $request->Nama_Bahan,
                'ID_Satuan_Bahan' => $request->ID_Satuan_Bahan,
            ]);

            // 3. Simpan Data Harga (Looping)
            if ($request->has('prices')) {
                foreach ($request->prices as $priceData) {
                    // Bersihkan format harga (misal dari "15.000" jadi "15000")
                    $cleanHarga = (int) str_replace(['Rp', '.', ','], '', $priceData['harga']);

                    if ($cleanHarga > 0) {
                        ListHargaBahan::create([
                            'ID_Bahan' => $bahan->ID_Bahan, // Ambil ID dari bahan yang baru dibuat
                            'ID_Supplier' => $priceData['supplier_id'],
                            'Harga' => $cleanHarga,
                            'Tanggal_Update' => now()
                        ]);
                    }
                }
            }

            DB::commit(); // Simpan permanen jika tidak ada error
            return response()->json(['success' => true, 'message' => 'Bahan dan harga berhasil disimpan!']);

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua perubahan jika error
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Nama_Bahan' => 'required|string|max:255', // Hapus unique strict agar bisa update diri sendiri
            'ID_Satuan_Bahan' => 'required|exists:list_satuan_ukur,ID_Satuan_Ukur',
        ]);

        DB::beginTransaction();

        try {
            $bahan = ListBahan::findOrFail($id);

            // 1. Update Data Bahan Utama
            $bahan->update([
                'Nama_Bahan' => $request->Nama_Bahan,
                'ID_Satuan_Bahan' => $request->ID_Satuan_Bahan
            ]);

            // 2. Update Data Harga (Sync Logic)
            // Strategi: Hapus harga lama untuk bahan ini, lalu insert ulang yang baru.
            // Ini cara paling aman untuk menghindari duplikasi atau data "ghosting".

            // Hapus semua harga lama milik bahan ini
            ListHargaBahan::where('ID_Bahan', $id)->delete();

            // Insert ulang harga baru dari form
            if ($request->has('prices')) {
                foreach ($request->prices as $priceData) {
                    $cleanHarga = (int) str_replace(['Rp', '.', ','], '', $priceData['harga']);

                    if ($cleanHarga > 0) {
                        ListHargaBahan::create([
                            'ID_Bahan' => $id,
                            'ID_Supplier' => $priceData['supplier_id'],
                            'Harga' => $cleanHarga,
                            'Tanggal_Update' => now()
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data bahan dan harga diperbarui!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Karena kita pakai Foreign Key di database (biasanya),
            // menghapus Bahan akan gagal jika masih ada Harga yang terhubung
            // KECUALI diset 'ON DELETE CASCADE' di migration.

            // Untuk aman, kita hapus harganya dulu manual:
            ListHargaBahan::where('ID_Bahan', $id)->delete();

            $bahan = ListBahan::findOrFail($id);
            $bahan->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Bahan berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }
}
