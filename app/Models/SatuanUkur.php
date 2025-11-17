<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatuanUkur extends Model
{
    use HasFactory;

    protected $table = 'list_satuan_ukur';
    protected $primaryKey = 'ID_Satuan_Ukur';
    
    protected $fillable = [
        'Nama_Satuan'
    ];

    public function bahan() {
        return $this->hasMany(Bahan::class, 'ID_Satuan_Bahan', 'ID_Satuan_Ukur');
    }
}