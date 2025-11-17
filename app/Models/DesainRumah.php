<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesainRumah extends Model
{
    use HasFactory;

    protected $table = 'desain_rumah';
    protected $primaryKey = 'ID_Desain_Rumah';
    
    protected $fillable = [
        'id_user',
        'Nama_Desain',
        'Luas_Tanah_m²',
        'Volume_Bangunan_m³',
        'Jumlah_Ruangan',
        'Jumlah_Lantai',
        'Tanggal_Dibuat'
    ];

    public function materials() {
        return $this->hasMany(Material::class, 'ID_Desain_Rumah', 'ID_Desain_Rumah');
    }

    public function komponen() {
        return $this->hasMany(KomponenDesain::class, 'ID_Desain_Rumah', 'ID_Desain_Rumah');
    }
}