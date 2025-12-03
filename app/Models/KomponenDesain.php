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
        'Ifc_Guid',
        'Label_Cad',
        'ID_Satuan_Ukur',
        'Panjang',
        'Lebar',
        'Tinggi'
    ];

    public $timestamps = false;

    public function pekerjaanKomponen() {
        return $this->hasMany(PekerjaanKomponen::class, 'ID_Komponen', 'ID_Komponen');
    }

    public function desainRumah() {
        return $this->belongsTo(DesainRumah::class, 'ID_Desain_Rumah');
    }
}
