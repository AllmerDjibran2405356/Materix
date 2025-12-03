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

class DataProyekController extends Controller
{
    public function index($id)
    {
        $project = DesainRumah::findOrFail($id);
        $recaps = RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $id)->get();
        $suppliers = ListSupplier::orderBy('Nama_Supplier')->get();
        $materialPrices = ListHargaBahan::get();

        $hargaMap = [];
        foreach ($materialPrices as $h) {
            $hargaMap[$h->ID_Bahan][$h->ID_Supplier] = $h->Harga_per_Satuan;
        }

        $message = $recaps->isEmpty() ? "empty data" : null;

        return view('Page.DataBahanDanProdusen', compact(
            'recaps',
            'suppliers',
            'materialPrices',
            'hargaMap',
            'message',
            'project'
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
            return redirect()->back()->with('error', 'Gagal menambahkan supplier: '.$e->getMessage());
        }
    }

    public function updateSupplier(Request $request)
    {
        $request->validate([
            'ID_Rekap' => 'required|integer',
            'ID_Supplier' => 'required|integer'
        ]);

        $recap = RekapKebutuhanBahanProyek::find($request->ID_Rekap);
        if (!$recap) return response()->json(['status'=>'error','message'=>'Rekap tidak ditemukan'],404);

        $recap->ID_Supplier = $request->ID_Supplier;

        $hargaRow = ListHargaBahan::where('ID_Bahan',$recap->ID_Bahan)
            ->where('ID_Supplier',$request->ID_Supplier)
            ->orderByDesc('Tanggal_Update_Data')
            ->first();

        $harga = $hargaRow ? (float)$hargaRow->Harga_per_Satuan : 0;

        $recap->Harga_Satuan_Saat_Ini = $harga;
        $recap->Total_Harga = $recap->Volume_Final * $harga;
        $recap->save();

        $supplier = ListSupplier::find($request->ID_Supplier);

        return response()->json([
            'status'=>'success',
            'message'=>'Supplier berhasil diperbarui',
            'supplier_name'=>$supplier ? $supplier->Nama_Supplier : null,
            'harga'=>$harga,
            'total_harga'=>$recap->Total_Harga,
            'ID_Rekap'=>$recap->ID_Rekap
        ]);
    }

    public function simpanHargaBahan(Request $request)
    {
        $request->validate([
            'ID_Bahan'=>'required|exists:list_bahan,ID_Bahan',
            'ID_Supplier'=>'required|exists:list_supplier,ID_Supplier',
            'Harga_per_Satuan'=>'required|numeric|min:1'
        ]);

        $idBahan = $request->ID_Bahan;
        $idSupplier = $request->ID_Supplier;
        $hargaInput = $request->Harga_per_Satuan;

        $bahan = ListBahan::findOrFail($idBahan);
        $idSatuan = $bahan->ID_Satuan_Bahan;

        try {
            $hargaExisting = ListHargaBahan::where('ID_Bahan',$idBahan)
                ->where('ID_Supplier',$idSupplier)
                ->first();

            if ($hargaExisting) {
                $hargaExisting->update([
                    'Harga_per_Satuan'=>$hargaInput,
                    'ID_Satuan'=>$idSatuan,
                    'Tanggal_Update_Data'=>now()
                ]);
                $hargaRecord = $hargaExisting;
            } else {
                $hargaRecord = ListHargaBahan::create([
                    'ID_Bahan'=>$idBahan,
                    'ID_Supplier'=>$idSupplier,
                    'Harga_per_Satuan'=>$hargaInput,
                    'ID_Satuan'=>$idSatuan,
                    'Tanggal_Update_Data'=>now()
                ]);
            }

            $recapsToUpdate = RekapKebutuhanBahanProyek::where('ID_Bahan',$idBahan)
                ->where('ID_Supplier',$idSupplier)
                ->get();

            foreach ($recapsToUpdate as $recap) {
                $recap->Harga_Satuan_Saat_Ini = $hargaInput;
                $recap->Total_Harga = $recap->Volume_Final * $hargaInput;
                $recap->save();
            }

            return response()->json([
                'status'=>'success',
                'message'=>'Harga bahan tersimpan / diperbarui',
                'ID_Harga'=>$hargaRecord->ID_Harga,
                'Harga_per_Satuan'=>$hargaInput
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'=>'error',
                'message'=>'Gagal menyimpan harga: '.$e->getMessage()
            ],500);
        }
    }

    public function updateHarga(Request $request)
    {
        $request->validate([
            'ID_Rekap'=>'required|integer',
            'Harga_Satuan_Saat_Ini'=>'required|numeric|min:0'
        ]);

        $recap = RekapKebutuhanBahanProyek::find($request->ID_Rekap);
        if (!$recap) return response()->json(['status'=>'error','message'=>'Rekap tidak ditemukan'],404);

        $recap->Harga_Satuan_Saat_Ini = $request->Harga_Satuan_Saat_Ini;
        $recap->Total_Harga = $recap->Volume_Final * $request->Harga_Satuan_Saat_Ini;
        $recap->save();

        return response()->json([
            'status'=>'success',
            'message'=>'Harga berhasil diperbarui',
            'total_harga'=>$recap->Total_Harga
        ]);
    }
}
