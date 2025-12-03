<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListHargaBahan extends Model
{
    use HasFactory;

    protected $table = 'list_harga_bahan';

    protected $primaryKey = 'ID_Harga';

    public $timestamps = false;

    protected $fillable = [
        'ID_Supplier',
        'ID_Bahan',
        'ID_Satuan',
        'Harga_per_Satuan',
        'Tanggal_Update_Data'
    ];

    public function bahan() {
        return $this->belongsTo(ListBahan::class, 'ID_Bahan', 'ID_Bahan');
    }

    public function supplier() {
        return $this->belongsTo(ListSupplier::class, 'ID_Supplier', 'ID_Supplier');
    }

    public function satuan() {
        return $this->belongsTo(ListSatuanUkur::class, 'ID_Satuan', 'ID_Satuan_Ukur');
    }
}
