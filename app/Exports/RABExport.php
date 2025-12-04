<?php

namespace App\Exports;

use App\Models\RekapKebutuhanBahanProyek;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RABExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function collection()
    {
        return RekapKebutuhanBahanProyek::where('ID_Desain_Rumah', $this->projectId)
            ->with([
                'bahan.kategori',
                'supplier' => function($query) {
                    $query->with(['kontak', 'alamat']);
                }
            ])
            ->orderBy('Tanggal_Hitung', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Bahan',
            'Kategori',
            'Volume',
            'Satuan',
            'Harga Satuan',
            'Total Harga',
            'Supplier',
            'Telepon Supplier',
            'Alamat Supplier'
        ];
    }

    public function map($recap): array
    {
        static $counter = 0;
        $counter++;

        // Ambil kontak supplier dengan aman
        $kontakSupplier = '-';
        if ($recap->supplier && $recap->supplier->kontak) {
            // Cek apakah kontak adalah Collection
            if ($recap->supplier->kontak instanceof \Illuminate\Database\Eloquent\Collection) {
                $kontakSupplier = $recap->supplier->kontak->first()->Kontak_Supplier ?? '-';
            } else {
                $kontakSupplier = $recap->supplier->kontak->Kontak_Supplier ?? '-';
            }
        }

        // Ambil alamat supplier dengan aman
        $alamatSupplier = '-';
        if ($recap->supplier && $recap->supplier->alamat) {
            // Cek apakah alamat adalah Collection
            if ($recap->supplier->alamat instanceof \Illuminate\Database\Eloquent\Collection) {
                $alamatSupplier = $recap->supplier->alamat->first()->Alamat_Supplier ?? '-';
            } else {
                $alamatSupplier = $recap->supplier->alamat->Alamat_Supplier ?? '-';
            }
        }

        return [
            $counter,
            $recap->bahan->Nama_Bahan ?? 'Unknown',
            $recap->bahan->kategori->Nama_Kelompok_Bahan ?? '-',
            $recap->Volume_Final,
            $recap->Satuan_Saat_Ini ?? 'Unit',
            $recap->Harga_Satuan_Saat_Ini,
            $recap->Total_Harga,
            $recap->supplier->Nama_Supplier ?? '-',
            $kontakSupplier,
            $alamatSupplier
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style untuk header
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50']
            ]
        ]);

        // Auto size columns
        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Number format untuk kolom harga
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            // Format angka untuk harga
            $sheet->getStyle('F2:F' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G2:G' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');

            // Format angka untuk volume
            $sheet->getStyle('D2:D' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // Style untuk header row
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'RAB';
    }
}
