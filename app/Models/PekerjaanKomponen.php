<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PekerjaanKomponen extends Model
{
    use HasFactory;

    protected $table = 'list_pekerjaan_komponen';

    // PENTING: Matikan timestamps agar tidak error 'updated_at' not found
    public $timestamps = false;

    protected $fillable = [
        'ID_Komponen',
        'ID_Pekerjaan'
    ];
}
