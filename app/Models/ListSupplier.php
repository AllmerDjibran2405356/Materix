<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListSupplier extends Model
{
    use HasFactory;
    protected $table = 'list_supplier';
    protected $primaryKey = 'ID_Supplier';
    public $timestamps = false;

    protected $fillable = ['Nama_Supplier'];

    // Relasi one-to-many ke alamat (sudah benar)
    public function alamat() {
        return $this->hasMany(SupplierAlamat::class, 'ID_Supplier', 'ID_Supplier');
    }

    // Relasi one-to-many ke kontak (sudah benar)
    public function kontak() {
        return $this->hasMany(SupplierKontak::class, 'ID_Supplier', 'ID_Supplier');
    }

    public function hargaBahan() {
        return $this->hasMany(ListHargaBahan::class, 'ID_Supplier', 'ID_Supplier');
    }

    public function rekap() {
        return $this->hasMany(RekapKebutuhanBahanProyek::class, 'ID_Supplier', 'ID_Supplier');
    }
}
