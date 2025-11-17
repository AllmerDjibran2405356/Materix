<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomponenDesain extends Model
{
    use HasFactory;

    protected $table = 'list_komponen_desain';
    protected $primaryKey = 'ID_Komponen';
    
    protected $fillable = [
        'ID_Desain_Rumah',
        'Nama_Komponen',
        'Ukuran_Komponen',
        'ID_Satuan_Ukur'
    ];

    public function materials() {
        return $this->hasMany(Material::class, 'ID_Komponen', 'ID_Komponen');
    }

    public function desainRumah() {
        return $this->belongsTo(DesainRumah::class, 'ID_Desain_Rumah');
    }
}