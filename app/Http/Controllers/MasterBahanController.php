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
        // 1. Ambil Query Pencarian
        $keyword = $request->input('q');

        // 2. Ambil Data Bahan (dengan Pagination & Search)
        $bahans = ListBahan::with(['satuan']) // Eager Load relasi satuan
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('Nama_Bahan', 'like', "%{$keyword}%");
            })
            ->orderBy('Nama_Bahan', 'asc')
            ->paginate(20); // Tampilkan 20 data per halaman

        // 3. Ambil Data Pendukung
        $suppliers = ListSupplier::orderBy('Nama_Supplier')->get();
        $satuans = ListSatuanUkur::orderBy('Nama_Satuan')->get();

        // 4. Ambil Semua Data Harga (Untuk dimapping ke Dropdown Supplier)
        // Kita ambil semua harga untuk bahan yang sedang tampil di halaman ini saja
        $bahanIds = $bahans->pluck('ID_Bahan')->toArray();
        $allPrices = ListHargaBahan::whereIn('ID_Bahan', $bahanIds)->get();

        return view('Page.MasterBahan', compact('bahans', 'suppliers', 'satuans', 'allPrices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Nama_Bahan' => 'required|string|max:255|unique:list_bahan,Nama_Bahan',
            'ID_Satuan_Bahan' => 'required|exists:list_satuan_ukur,ID_Satuan_Ukur',
        ]);

        try {
            ListBahan::create([
                'Nama_Bahan' => $request->Nama_Bahan,
                'ID_Satuan_Bahan' => $request->ID_Satuan_Bahan,
                // 'ID_Kategori' => $request->ID_Kategori (Nanti jika sudah ada)
            ]);

            return response()->json(['success' => true, 'message' => 'Bahan baru berhasil ditambahkan!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Nama_Bahan' => 'required|string|max:255',
            'ID_Satuan_Bahan' => 'required|exists:list_satuan_ukur,ID_Satuan_Ukur',
        ]);

        try {
            $bahan = ListBahan::findOrFail($id);
            $bahan->update([
                'Nama_Bahan' => $request->Nama_Bahan,
                'ID_Satuan_Bahan' => $request->ID_Satuan_Bahan
            ]);

            return response()->json(['success' => true, 'message' => 'Data bahan diperbarui!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $bahan = ListBahan::findOrFail($id);
            $bahan->delete();
            return response()->json(['success' => true, 'message' => 'Bahan berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }
}
