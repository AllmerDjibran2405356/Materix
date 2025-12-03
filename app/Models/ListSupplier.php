<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListSupplier extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'list_supplier';
    protected $primaryKey = 'ID_Supplier';

    protected $fillable = ['Nama_Supplier'];
}
