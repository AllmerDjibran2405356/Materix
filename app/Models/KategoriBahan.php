<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBahan extends Model
{
    use HasFactory;

    protected $table = 'list_kategori_bahan';
    protected $primaryKey = 'ID_Kategori';
    
    protected $fillable = [
        'Nama_Kelompok_Bahan'
    ];

    public function bahan() {
        return $this->hasMany(Bahan::class, 'ID_Kategori', 'ID_Kategori');
    }
}