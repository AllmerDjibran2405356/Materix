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

    public function alamat() {
        return $this->hasOne(SupplierAlamat::class, 'ID_Supplier', 'ID_Supplier');
    }

    public function kontak() {
        return $this->hasOne(SupplierKontak::class, 'ID_Supplier', 'ID_Supplier');
    }

    public function hargaBahan() {
        return $this->hasMany(ListHargaBahan::class, 'ID_Supplier', 'ID_Supplier');
    }

    public function rekap() {
        return $this->hasMany(RekapKebutuhanBahanProyek::class, 'ID_Supplier', 'ID_Supplier');
    }
}
