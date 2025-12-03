<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'list_supplier';
    protected $primaryKey = 'ID_Supplier';

    protected $fillable = [
        'Nama_Supplier'
    ];

    public function hargaBahan() {
        return $this->hasMany(ListHargaBahan::class, 'ID_Supplier', 'ID_Supplier');
    }
}
