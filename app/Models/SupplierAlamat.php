<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierAlamat extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'supplier_alamat';
    protected $fillable = ['ID_Supplier', 'Alamat_Supplier'];

    public function supplier() {
        return $this->belongsTo(ListSupplier::class, 'ID_Supplier');
    }
}
