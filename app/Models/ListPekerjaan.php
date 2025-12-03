<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ListPekerjaan extends Model
{
    protected $table = 'list_kode_pekerjaan';
    protected $primaryKey = 'ID_Pekerjaan';

    // Relasi ke Resep (Koefisien Bahan)
    public function analisaBahan() {
        return $this->hasMany(ListBahanPekerjaan::class, 'ID_Pekerjaan', 'ID_Pekerjaan');
    }

    // Relasi ke Satuan Ukur (untuk tahu ini m2 atau m3)
    public function satuan() {
        return $this->belongsTo(ListSatuanUkur::class, 'ID_Satuan', 'ID_Satuan_Ukur');
    }
}
