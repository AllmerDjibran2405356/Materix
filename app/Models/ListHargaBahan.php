<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListHargaBahan extends Model
{
    use HasFactory;

    // 1. Nama Tabel
    protected $table = 'list_harga_bahan';

    // 2. Primary Key (PENTING: Wajib diisi karena bukan 'id')
    protected $primaryKey = 'ID_Harga';

    // 3. Matikan Timestamps Default (created_at, updated_at)
    // Karena tabel Anda menggunakan 'Tanggal_Update_Data' manual
    public $timestamps = false;

    protected $fillable = [
        'ID_Supplier',
        'ID_Bahan',
        'ID_Satuan',
        'Harga_per_Satuan',
        'Tanggal_Update_Data'
    ];

    // ==========================================
    // DEFINISI RELASI (Agar bisa dipanggil 'with')
    // ==========================================

    // Relasi ke Model Bahan (Milik Bahan Apa?)
    public function bahan() {
        return $this->belongsTo(ListBahan::class, 'ID_Bahan', 'ID_Bahan');
    }

    // Relasi ke Model Supplier (Siapa Penjualnya?)
    // Pastikan Anda sudah membuat Model ListSupplier juga
    public function supplier() {
        return $this->belongsTo(ListSupplier::class, 'ID_Supplier', 'ID_Supplier');
    }

    // Relasi ke Satuan Ukur (Harga per apa? per kg/sak?)
    public function satuan() {
        return $this->belongsTo(ListSatuanUkur::class, 'ID_Satuan', 'ID_Satuan_Ukur');
    }
}
