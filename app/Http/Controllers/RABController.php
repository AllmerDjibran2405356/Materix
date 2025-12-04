<?php

namespace App\Http\Controllers;

use App\Models\DesainRumah;
use App\Models\RekapKebutuhanBahanProyek;
use App\Models\ListHargaBahan;
use App\Models\ListSupplier;
use App\Models\ListBahan;
use App\Models\SupplierKontak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\RABExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class RABController extends Controller
{
    public function index($id)
    {
        $project = DesainRumah::findOrFail($id);

        // Ambil data rekap dengan relasi yang benar
        $recaps = RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $id)
            ->with([
                'bahan.kategori',
                'supplier.kontak'
            ])
            ->orderBy('Tanggal_Hitung', 'desc')
            ->get();

        // Load supplier dengan alamat dan kontak
        $suppliers = ListSupplier::with(['kontak', 'alamat'])
            ->orderBy('Nama_Supplier')
            ->get();

        // Ambil semua bahan yang ada di rekap
        $bahanIds = $recaps->pluck('ID_Bahan')->unique()->toArray();

        // Ambil harga bahan terbaru untuk setiap bahan
        $materialPrices = ListHargaBahan::whereIn('ID_Bahan', $bahanIds)
            ->with(['bahan', 'supplier.kontak', 'satuan'])
            ->orderBy('Tanggal_Update_Data', 'desc')
            ->get();

        // Group harga bahan berdasarkan ID_Bahan
        $groupedPrices = $materialPrices->groupBy('ID_Bahan');

        // Ambil data bahan untuk dropdown
        $bahanList = ListBahan::whereIn('ID_Bahan', $bahanIds)
            ->with('satuan')
            ->get()
            ->mapWithKeys(function ($bahan) {
                return [$bahan->ID_Bahan => $bahan->Nama_Bahan . ' (' . ($bahan->satuan->Nama_Satuan ?? '-') . ')'];
            })
            ->toArray();

        // Hitung total
        $grandTotal = $recaps->sum('Total_Harga');
        $totalItems = $recaps->count();

        // Hitung unique suppliers dari rekap
        $uniqueSuppliers = $recaps->whereNotNull('ID_Supplier')->pluck('ID_Supplier')->unique()->count();

        $message = $recaps->isEmpty() ? "Belum ada data rekap untuk proyek ini." : null;

        return view('Page.LihatLaporanAtauRAB', compact(
            'project',
            'recaps',
            'suppliers',
            'groupedPrices', // Menggunakan groupedPrices bukan materialPrices
            'bahanList',
            'message',
            'grandTotal',
            'totalItems',
            'uniqueSuppliers'
        ));
    }

    public function exportExcel($id)
    {
        $project = DesainRumah::findOrFail($id);
        $fileName = 'RAB_' . str_replace(' ', '_', $project->Nama_Desain) . '_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new RABExport($id), $fileName);
    }

    public function exportPDF($id)
    {
        $project = DesainRumah::findOrFail($id);

        $recaps = RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $id)
            ->with([
                'bahan.kategori',
                'supplier.kontak'
            ])
            ->orderBy('Tanggal_Hitung', 'desc')
            ->get();

        $grandTotal = $recaps->sum('Total_Harga');
        $totalItems = $recaps->count();
        $uniqueSuppliers = $recaps->whereNotNull('ID_Supplier')->pluck('ID_Supplier')->unique()->count();

        $data = [
            'project' => $project,
            'recaps' => $recaps,
            'grandTotal' => $grandTotal,
            'totalItems' => $totalItems,
            'uniqueSuppliers' => $uniqueSuppliers,
            'exportDate' => now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('exports.rab-pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        $fileName = 'RAB_' . str_replace(' ', '_', $project->Nama_Desain) . '_' . date('Y-m-d') . '.pdf';

        return $pdf->download($fileName);
    }

    public function getRecapData($id)
    {
        try {
            $recaps = RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $id)
                ->with([
                    'bahan.kategori',
                    'supplier.kontak'
                ])
                ->orderBy('Tanggal_Hitung', 'desc')
                ->get();

            $data = $recaps->map(function ($recap, $index) {
                // Mengakses kontak supplier dengan benar
                $kontakSupplier = '-';
                if ($recap->supplier && $recap->supplier->kontak) {
                    // Jika kontak adalah collection, ambil yang pertama
                    if ($recap->supplier->kontak instanceof \Illuminate\Database\Eloquent\Collection) {
                        $kontakSupplier = $recap->supplier->kontak->first()->Kontak_Supplier ?? '-';
                    } else {
                        $kontakSupplier = $recap->supplier->kontak->Kontak_Supplier ?? '-';
                    }
                }

                $namaKategori = $recap->bahan->kategori->Nama_Kelompok_Bahan ?? '-';

                return [
                    'no' => $index + 1,
                    'nama_bahan' => $recap->bahan->Nama_Bahan ?? 'Unknown',
                    'kategori' => $namaKategori,
                    'volume_final' => number_format($recap->Volume_Final, 2, ',', '.'),
                    'satuan' => $recap->Satuan_Saat_Ini ?? 'Unit',
                    'harga_satuan' => 'Rp ' . number_format($recap->Harga_Satuan_Saat_Ini, 0, ',', '.'),
                    'total_harga' => 'Rp ' . number_format($recap->Total_Harga, 0, ',', '.'),
                    'supplier' => $recap->supplier->Nama_Supplier ?? 'Supplier tidak ditemukan',
                    'telepon' => $kontakSupplier,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => $recaps->count(),
                'grandTotal' => 'Rp ' . number_format($recaps->sum('Total_Harga'), 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting recap data: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method untuk update data rekap
    public function updateRecap(Request $request, $id)
    {
        try {
            $request->validate([
                'ID_Bahan' => 'required|exists:list_bahan,ID_Bahan',
                'Volume_Final' => 'required|numeric|min:0',
                'Satuan_Saat_Ini' => 'nullable|string|max:20',
                'Harga_Satuan_Saat_Ini' => 'required|numeric|min:0',
                'ID_Supplier' => 'nullable|exists:list_supplier,ID_Supplier'
            ]);

            $rekap = RekapKebutuhanBahanProyek::where('ID_Rekap', $id)->firstOrFail();

            // Hitung total harga baru
            $totalHarga = $request->Volume_Final * $request->Harga_Satuan_Saat_Ini;

            $rekap->update([
                'Volume_Final' => $request->Volume_Final,
                'Satuan_Saat_Ini' => $request->Satuan_Saat_Ini,
                'Harga_Satuan_Saat_Ini' => $request->Harga_Satuan_Saat_Ini,
                'Total_Harga' => $totalHarga,
                'ID_Supplier' => $request->ID_Supplier
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data rekap berhasil diperbarui',
                'data' => $rekap
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating recap: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }
}
