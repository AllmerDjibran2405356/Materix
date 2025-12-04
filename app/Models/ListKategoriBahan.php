<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListKategoriBahan extends Model
{
    use HasFactory;
    protected $table = 'list_kategori_bahan';
    protected $primaryKey = 'ID_Kategori';

    public $timestamps = false;

    public function bahan() {
        return $this->hasMany(ListBahan::class, 'ID_Kategori', 'ID_Kategori');
    }
}
