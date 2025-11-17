<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListMaterialKomponen;
use App\Models\ListBahan;
use App\Models\KomponenDesain;
use App\Models\ListSupplier;
use App\Models\KategoriBahan;
use App\Models\SatuanUkur;
use App\Models\DesainRumah;
use PDF;
use Excel;
use App\Exports\MaterialsExport;

class MaterialController extends Controller
{
    public function index($id_desain_rumah) {
        // Get project data untuk judul
        $project = DesainRumah::findOrFail($id_desain_rumah);
        
        $materials = ListMaterialKomponen::with([
            'bahan.kategori', 
            'bahan.satuanUkur',
            'supplier', 
            'komponen'
        ])->where('ID_Desain_Rumah', $id_desain_rumah)->get();
        
        return view('materials.index', compact('materials', 'project'));
    }

    // ✅ TAMBAH MATERIAL
    public function store(Request $request, $id_desain_rumah) {
        $request->validate([
            'bahan_id' => 'required|exists:list_bahan,id',
            'komponen_id' => 'required|exists:komponen_desain,id',
            'supplier_id' => 'required|exists:list_supplier,id',
            'jumlah' => 'required|numeric|min:0'
        ]);

        ListMaterialKomponen::create([
            'ID_Desain_Rumah' => $id_desain_rumah,
            'ID_Bahan' => $request->bahan_id,
            'ID_Komponen' => $request->komponen_id,
            'ID_Supplier' => $request->supplier_id,
            'Jumlah' => $request->jumlah
        ]);

        return response()->json(['success' => true, 'message' => 'Material berhasil ditambahkan']);
    }

    // ✅ KATEGORI - CREATE
    public function storeKategori(Request $request) {
        $request->validate(['nama_kategori' => 'required|string|max:255']);
        
        KategoriBahan::create($request->all());
        return response()->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan']);
    }

    // ✅ KATEGORI - READ (untuk modal lihat)
    public function getKategori() {
        $kategories = KategoriBahan::all();
        return response()->json($kategories);
    }

    // ✅ SATUAN - CREATE  
    public function storeSatuan(Request $request) {
        $request->validate(['nama_satuan' => 'required|string|max:255']);
        
        SatuanUkur::create($request->all());
        return response()->json(['success' => true, 'message' => 'Satuan berhasil ditambahkan']);
    }

    // ✅ SATUAN - READ (untuk modal lihat)
    public function getSatuan() {
        $satuans = SatuanUkur::all();
        return response()->json($satuans);
    }

    // ✅ EXPORT PDF
    public function exportPDF($id_desain_rumah) {
        $project = DesainRumah::findOrFail($id_desain_rumah);
        $materials = ListMaterialKomponen::with([
            'bahan.kategori', 'bahan.satuanUkur', 'supplier', 'komponen'
        ])->where('ID_Desain_Rumah', $id_desain_rumah)->get();
        
        $pdf = PDF::loadView('materials.export.pdf', compact('materials', 'project'));
        return $pdf->download("material-{$project->nama_desain}.pdf");
    }

    // ✅ EXPORT EXCEL
    public function exportExcel($id_desain_rumah) {
        $project = DesainRumah::findOrFail($id_desain_rumah);
        return Excel::download(new MaterialsExport($id_desain_rumah), "material-{$project->nama_desain}.xlsx");
    }

    // ✅ API DATA DROPDOWN
    public function getBahanList() {
        $bahan = ListBahan::with(['kategori', 'satuanUkur'])->get();
        return response()->json($bahan);
    }

    public function getKomponenList() {
        $komponen = KomponenDesain::all();
        return response()->json($komponen);
    }

    public function getSupplierList() {
        $supplier = ListSupplier::all();
        return response()->json($supplier);
    }
}