<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListBahan extends Model
{
    use HasFactory;

    protected $table = 'list_bahan';
    protected $primaryKey = 'ID_Bahan';
    public $timestamps = true;

    protected $fillable = [
        'Nama_Bahan',
        'ID_Satuan_Bahan',
        'ID_Kategori'
    ];

    // Relasi ke satuan ukur (benar)
    public function satuanUkur()
    {
        return $this->belongsTo(ListSatuanUkur::class, 'ID_Satuan_Bahan', 'ID_Satuan_Ukur');
    }

    // Relasi ke kategori bahan
    public function kategori()
    {
        return $this->belongsTo(ListKategoriBahan::class, 'ID_Kategori', 'ID_Kategori');
    }

    // Relasi ke harga bahan
    public function hargaBahan()
    {
        return $this->hasMany(ListHargaBahan::class, 'ID_Bahan', 'ID_Bahan');
    }

    // Harga terbaru
    public function hargaTerbaru()
    {
        return $this->hasOne(ListHargaBahan::class, 'ID_Bahan', 'ID_Bahan')
                    ->latest('Tanggal_Update_Data');
    }

    // Alias untuk satuan (mengarah ke satuanUkur)
    public function satuan()
    {
        return $this->belongsTo(ListSatuanUkur::class, 'ID_Satuan_Bahan', 'ID_Satuan_Ukur');
    }
}
