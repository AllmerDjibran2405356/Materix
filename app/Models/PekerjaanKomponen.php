<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PekerjaanKomponen extends Model
{
    use HasFactory;

    protected $table = 'list_pekerjaan_komponen';

    public $timestamps = false;

    protected $fillable = [
        'ID_Komponen',
        'ID_Pekerjaan'
    ];

    public function komponen() {
        return $this->belongsTo(KomponenDesain::class, 'ID_Komponen', 'ID_Komponen');
    }

    public function pekerjaan() {
        return $this->belongsTo(ListPekerjaan::class, 'ID_Pekerjaan', 'ID_Pekerjaan');
    }
}
