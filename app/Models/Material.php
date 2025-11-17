<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $table = 'list_material_komponen';
    
    protected $fillable = [
        'ID_Desain_Rumah',
        'ID_Bahan', 
        'ID_Komponen',
        'ID_Supplier',
        'Jumlah'
    ];

    // Relasi ke DesainRumah
    public function desainRumah() {
        return $this->belongsTo(DesainRumah::class, 'ID_Desain_Rumah');
    }
    
    // Relasi ke Bahan (dengan kategori dan satuan)
    public function bahan() {
        return $this->belongsTo(Bahan::class, 'ID_Bahan');
    }
    
    // Relasi ke Supplier  
    public function supplier() {
        return $this->belongsTo(Supplier::class, 'ID_Supplier');
    }
    
    // Relasi ke Komponen
    public function komponen() {
        return $this->belongsTo(Komponen::class, 'ID_Komponen');
    }
}