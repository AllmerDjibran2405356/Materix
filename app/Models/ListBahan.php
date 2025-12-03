<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListBahan extends Model
{
    use HasFactory;
    protected $table = 'list_bahan';
    protected $primaryKey = 'ID_Bahan';

    protected $fillable = [
        'Nama_Bahan', 'ID_Kategori', 'ID_Satuan_Bahan'
    ];

    // Relasi ke Satuan (Opsional, untuk tampilkan 'kg', 'sak', dll)
    public function satuanUkur() {
        return $this->belongsTo(ListSatuanUkur::class, 'ID_Satuan_Bahan', 'ID_Satuan_Ukur');
    }

    // Relasi ke History Harga (Banyak Harga)
    public function hargaBahan() {
        return $this->hasMany(ListHargaBahan::class, 'ID_Bahan', 'ID_Bahan');
    }

    // --- TAMBAHAN PENTING ---
    // Helper untuk mengambil SATU harga terbaru saja
    public function hargaTerbaru() {
        return $this->hasOne(ListHargaBahan::class, 'ID_Bahan', 'ID_Bahan')
                    ->latest('ID_Harga'); // Mengambil data terakhir yang diinput
    }
}
