<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierKontak extends Model
{
    use HasFactory;
    protected $table = 'supplier_kontak';

    protected $fillable = ['Kontak_Supplier'];

    public function supplier() {
        return $this->belongsTo(ListSupplier::class, 'ID_Supplier');
    }
}
