<?php

namespace App\Http\Controllers;

use App\Models\DesainRumah;
use App\Models\RekapKebutuhanBahanProyek;
use App\Models\ListHargaBahan;
use App\Models\ListSupplier;
use App\Models\SupplierAlamat;
use App\Models\SupplierKontak;
use Illuminate\Http\Request;

class KalkulasiController extends Controller
{
    public function index($id)
    {
        $project = DesainRumah::findOrFail($id);
        $recaps = RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $id)
        ->get();

        $suppliers = ListSupplier::orderBy('Nama_Supplier')
        ->get();

        $message = $recaps->isEmpty() ? "empty data" : null;

        return view('Page.DataBahanDanProdusen', compact('recaps', 'suppliers', 'message'));
    }
}

?>
