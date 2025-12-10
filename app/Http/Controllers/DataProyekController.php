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

        $recaps = RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $id)
            ->with(['bahan', 'supplier'])
            ->get();

        $suppliers = ListSupplier::with(['alamat', 'kontak'])
            ->orderBy('Nama_Supplier')
            ->get();

        $bahanIds = $recaps->pluck('ID_Bahan')->unique()->toArray();

        $materialPrices = ListHargaBahan::whereIn('ID_Bahan', $bahanIds)
            ->with(['bahan', 'supplier'])
            ->orderBy('Tanggal_Update_Data', 'desc')
            ->get();

        $bahanList = ListBahan::whereIn('ID_Bahan', $bahanIds)
            ->pluck('Nama_Bahan', 'ID_Bahan')
            ->toArray();

        $message = $recaps->isEmpty() ? "empty data" : null;

        return view('Page.DataBahanDanProdusen', compact(
            'project', 'recaps', 'suppliers', 'materialPrices', 'bahanList', 'message'
        ));
    }

    public function tambahSupplier(Request $request)
    {
        // 1. Validasi Input
        $validator = \Validator::make($request->all(), [
            'Nama_Supplier' => 'required|string|max:255',
            'Alamat_Supplier' => 'required|string|max:255',
            'Kontak_Supplier' => 'required|string|max:50',
        ]);

        // Jika validasi gagal, kembalikan error JSON (agar ditangkap AJAX error)
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 2. Simpan Supplier ke Database
            $supplier = ListSupplier::create(['Nama_Supplier' => $request->Nama_Supplier]);

            SupplierAlamat::create([
                'ID_Supplier' => $supplier->ID_Supplier,
                'Alamat_Supplier' => $request->Alamat_Supplier
            ]);

            SupplierKontak::create([
                'ID_Supplier' => $supplier->ID_Supplier,
                'Kontak_Supplier' => $request->Kontak_Supplier
            ]);

            DB::commit();

            // 3. PENTING: Return JSON Response (Bukan Redirect)
            // Kita kirim balik data yang baru disimpan agar bisa ditampilkan JS
            return response()->json([
                'success' => true,
                'message' => 'Supplier berhasil ditambahkan!',
                'data' => [
                    'ID_Supplier'   => $supplier->ID_Supplier,
                    'Nama_Supplier' => $supplier->Nama_Supplier,
                    'Alamat_Awal'   => $request->Alamat_Supplier,
                    'Kontak_Awal'   => $request->Kontak_Supplier,

                    // TAMBAHAN PENTING: Kirim URL lengkap dari server
                    // Ini memastikan URL cocok dimanapun (Localhost/Hosting)
                    'Url_Hapus_Supplier' => route('supplier.hapus', $supplier->ID_Supplier),
                    'Url_Hapus_Alamat'   => route('supplier.hapusAlamat'), // Base URL saja
                    'Url_Hapus_Kontak'   => route('supplier.hapusKontak'), // Base URL saja
                    'Url_Update'         => route('supplier.update', $supplier->ID_Supplier)
                ]
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            // Return Error JSON
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan supplier: ' . $e->getMessage()
            ], 500);
        }
    }

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
            return redirect()->back()->with('success', 'Alamat tambahan berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

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
            return redirect()->back()->with('success', 'Kontak tambahan berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function updateSupplier(Request $request, $id)
    {
        $request->validate(['Nama_Supplier' => 'required|string|max:255']);
        try {
            $supplier = ListSupplier::findOrFail($id);
            $supplier->update(['Nama_Supplier' => $request->Nama_Supplier]);
            return redirect()->back()->with('success', 'Nama supplier diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // --- AJAX DELETE (Revised for Composite Keys) ---

    public function hapusAlamatSupplier(Request $request)
    {
        try {
            // Kita gunakan ID Supplier + Isi Alamat untuk identifikasi
            $idSupplier = $request->query('id_supplier');
            $isiAlamat = $request->query('alamat');

            if (!$idSupplier || !$isiAlamat) {
                return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap'], 400);
            }

            $deleted = SupplierAlamat::where('ID_Supplier', $idSupplier)
                ->where('Alamat_Supplier', $isiAlamat)
                ->delete();

            if ($deleted) {
                return response()->json(['success' => true, 'message' => 'Alamat berhasil dihapus']);
            } else {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

        } catch (\Exception $e) {
            Log::error('Gagal hapus alamat: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function hapusKontakSupplier(Request $request)
    {
        try {
            $idSupplier = $request->query('id_supplier');
            $isiKontak = $request->query('kontak');

            if (!$idSupplier || !$isiKontak) {
                return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap'], 400);
            }

            $deleted = SupplierKontak::where('ID_Supplier', $idSupplier)
                ->where('Kontak_Supplier', $isiKontak)
                ->delete();

            if ($deleted) {
                return response()->json(['success' => true, 'message' => 'Kontak berhasil dihapus']);
            } else {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

        } catch (\Exception $e) {
            Log::error('Gagal hapus kontak: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function hapusSupplier($id)
    {
        DB::beginTransaction();
        try {
            // 1. Cek apakah data ada
            $supplier = ListSupplier::findOrFail($id);

            // 2. Hapus Data Turunan (Manual Delete untuk memastikan bersih)
            // (Opsional jika di database Anda tidak setting ON DELETE CASCADE)
            SupplierAlamat::where('ID_Supplier', $id)->delete();
            SupplierKontak::where('ID_Supplier', $id)->delete();
            ListHargaBahan::where('ID_Supplier', $id)->delete();
            // Warning: ListHargaBahan dihapus berarti history harga hilang.
            // Jika ingin soft delete, pastikan model menggunakan SoftDeletes.

            // 3. Hapus Supplier Utama
            $supplier->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Supplier berhasil dihapus']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // --- Harga Bahan ---

    public function simpanHargaBahan(Request $request)
    {
        $request->validate([
            'ID_Bahan' => 'required|exists:list_bahan,ID_Bahan',
            'ID_Supplier' => 'required|exists:list_supplier,ID_Supplier',
            'Harga_Per_Satuan' => 'required|numeric|min:1'
        ]);

        $bahan = ListBahan::find($request->ID_Bahan);
        $idSatuan = $bahan ? $bahan->ID_Satuan_Bahan : null;

        DB::beginTransaction();
        try {
            $existing = ListHargaBahan::where('ID_Bahan', $request->ID_Bahan)
                ->where('ID_Supplier', $request->ID_Supplier)
                ->first();

            if ($existing) {
                $existing->update([
                    'Harga_Per_Satuan' => $request->Harga_Per_Satuan,
                    'Tanggal_Update_Data' => now()
                ]);
            } else {
                ListHargaBahan::create([
                    'ID_Bahan' => $request->ID_Bahan,
                    'ID_Supplier' => $request->ID_Supplier,
                    'Harga_Per_Satuan' => $request->Harga_Per_Satuan,
                    'ID_Satuan' => $idSatuan,
                    'Tanggal_Update_Data' => now()
                ]);
            }

            // Update Rekap jika perlu (Opsional)
            $recaps = RekapKebutuhanBahanProyek::where('ID_Bahan', $request->ID_Bahan)
                ->where('ID_Supplier', $request->ID_Supplier)
                ->get();

            foreach($recaps as $recap) {
                $recap->Harga_Satuan_Saat_Ini = $request->Harga_Per_Satuan;
                $recap->Total_Harga = $recap->Volume_Final * $request->Harga_Per_Satuan;
                $recap->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Harga bahan berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan harga: ' . $e->getMessage());
        }
    }

    public function updateHargaInline(Request $request)
    {
        // 1. Validasi: ID_Bahan diperlukan untuk create baru
        $request->validate([
            'ID_Bahan' => 'required|exists:list_bahan,ID_Bahan',
            'ID_Supplier' => 'required|exists:list_supplier,ID_Supplier',
            'Harga_Per_Satuan' => 'required|numeric|min:0'
        ]);

        try {
            // 2. Cari apakah harga untuk kombinasi Bahan & Supplier ini sudah ada?
            $harga = ListHargaBahan::where('ID_Bahan', $request->ID_Bahan)
                ->where('ID_Supplier', $request->ID_Supplier)
                ->first();

            if ($harga) {
                // A. UPDATE: Jika data sudah ada
                $harga->Harga_Per_Satuan = $request->Harga_Per_Satuan;
                $harga->Tanggal_Update_Data = now();
                $harga->save();
            } else {
                // B. CREATE: Jika data belum ada (Input Baru)

                // Ambil ID Satuan dari Master Bahan (Agar data lengkap)
                $bahanMaster = ListBahan::find($request->ID_Bahan);
                $idSatuan = $bahanMaster ? $bahanMaster->ID_Satuan_Bahan : null;

                $harga = ListHargaBahan::create([
                    'ID_Bahan' => $request->ID_Bahan,
                    'ID_Supplier' => $request->ID_Supplier,
                    'Harga_Per_Satuan' => $request->Harga_Per_Satuan,
                    'ID_Satuan' => $idSatuan,
                    'Tanggal_Update_Data' => now()
                ]);
            }

            // 3. Return response JSON
            // Penting: Kembalikan ID_Harga baru/lama agar JavaScript bisa mengupdate atribut tombol
            return response()->json([
                'success' => true,
                'message' => 'Harga berhasil disimpan',
                'data' => [
                    'id' => $harga->ID_Harga, // ID ini akan dipakai JS untuk update tombol simpan
                    'updated_at' => \Carbon\Carbon::parse($harga->Tanggal_Update_Data)->format('d/m/Y H:i')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error update inline: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateSupplierRekap(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'ID_Rekap' => 'required|exists:rekap_kebutuhan_bahan_proyek,ID_Rekap',
            'ID_Supplier' => 'required|exists:list_supplier,ID_Supplier'
        ]);

        try {
            // 2. Ambil Data Rekap
            $rekap = RekapKebutuhanBahanProyek::findOrFail($request->ID_Rekap);

            // 3. Cari Harga Terbaru untuk Bahan ini dari Supplier yang dipilih
            // Kita ambil dari tabel Master Harga (ListHargaBahan)
            $hargaTerbaru = ListHargaBahan::where('ID_Bahan', $rekap->ID_Bahan)
                ->where('ID_Supplier', $request->ID_Supplier)
                ->orderBy('Tanggal_Update_Data', 'desc') // Ambil yang paling baru
                ->first();

            // Jika ada harga, pakai itu. Jika tidak, set 0.
            $nominalHarga = $hargaTerbaru ? $hargaTerbaru->Harga_Per_Satuan : 0;

            // 4. Update Data Rekap di Database
            $rekap->ID_Supplier = $request->ID_Supplier;
            $rekap->Harga_Satuan_Saat_Ini = $nominalHarga;
            $rekap->Total_Harga = $rekap->Volume_Final * $nominalHarga;
            $rekap->save();

            // 5. Return Data Baru untuk Update UI
            return response()->json([
                'success' => true,
                'message' => 'Supplier dan harga berhasil diperbarui',
                'data' => [
                    'harga_satuan' => number_format($nominalHarga, 0, ',', '.'), // Format: 15.000
                    'total_harga'  => number_format($rekap->Total_Harga, 0, ',', '.') // Format: 150.000
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function refreshTable($id)
    {
        // ... (fungsi lama tetap dipertahankan jika perlu)
        try {
            $project = DesainRumah::findOrFail($id);
            $recaps = RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $id)->with(['bahan', 'supplier'])->get();
            $suppliers = ListSupplier::orderBy('Nama_Supplier')->get();
            $html = view('partials.main-table', compact('project', 'recaps', 'suppliers'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
