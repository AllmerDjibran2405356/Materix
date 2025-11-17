<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
    use HasFactory;

    protected $table = 'list_bahan';
    
    protected $fillable = [
        'nama_bahan',
        'ID_Kategori',
        'ID_Satuan_Ukur',
        'harga',
        'deskripsi'
    ];

    // Relasi ke Kategori
    public function kategori() {
        return $this->belongsTo(Kategori::class, 'ID_Kategori');
    }
    
    // Relasi ke SatuanUkur
    public function satuanUkur() {
        return $this->belongsTo(SatuanUkur::class, 'ID_Satuan_Ukur');
    }
    
    // Relasi ke Material (bahan punya banyak material)
    public function materials() {
        return $this->hasMany(Material::class, 'ID_Bahan');
    }
}