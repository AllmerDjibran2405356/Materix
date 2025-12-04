<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierKontak extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'supplier_kontak';
    protected $fillable = ['ID_Supplier', 'Kontak_Supplier'];

    public function supplier() {
        return $this->belongsTo(ListSupplier::class, 'ID_Supplier');
    }
}
