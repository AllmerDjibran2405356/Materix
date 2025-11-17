<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
    use HasFactory;

    protected $table = 'list_bahan';
    
    protected $fillable = [
        'Nama_Bahan',
        'ID_Kategori',
        'ID_Satuan_Bahan'
    ];

    protected $primaryKey = 'ID_Bahan';

    // Relasi ke KategoriBahan
    public function kategori() {
        return $this->belongsTo(KategoriBahan::class, 'ID_Kategori', 'ID_Kategori');
    }
    
    // Relasi ke SatuanUkur
    public function satuanUkur() {
        return $this->belongsTo(SatuanUkur::class, 'ID_Satuan_Bahan', 'ID_Satuan_Ukur');
    }
    
    // Relasi ke Material
    public function materials() {
        return $this->hasMany(Material::class, 'ID_Bahan', 'ID_Bahan');
    }

    // Relasi ke ListHargaBahan
    public function hargaBahan() {
        return $this->hasMany(ListHargaBahan::class, 'ID_Bahan', 'ID_Bahan');
    }
}