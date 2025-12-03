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

    public function satuanUkur() {
        return $this->belongsTo(ListSatuanUkur::class, 'ID_Satuan_Bahan', 'ID_Satuan_Ukur');
    }

    public function hargaBahan() {
        return $this->hasMany(ListHargaBahan::class, 'ID_Bahan', 'ID_Bahan');
    }

    public function hargaTerbaru() {
        return $this->hasOne(ListHargaBahan::class, 'ID_Bahan', 'ID_Bahan')
                    ->latest('ID_Harga');
    }
}
