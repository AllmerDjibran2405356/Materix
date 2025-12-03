<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ListBahanPekerjaan extends Model
{
    protected $table = 'list_bahan_pekerjaan';
    // Karena tabel ini mungkin tidak punya Primary Key tunggal (Composite Key),
    // kita set false jika tidak ada kolom 'id' auto increment.
    // Tapi untuk baca data saja (readonly) ini aman.

    public function material() {
        return $this->belongsTo(ListBahan::class, 'ID_Bahan', 'ID_Bahan');
    }
}
