<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapKebutuhanBahanProyek extends Model
{
    use HasFactory;
    protected $table = 'rekap_kebutuhan_bahan_proyek';
    protected $primaryKey = 'ID_Rekap';

    public $timestamps = false;
    protected $fillable = [
        'ID_User',
        'ID_Desain_Rumah',
        'ID_Bahan',
        'Volume_Teoritis',
        'Volume_Final',
        'Satuan_Saat_Ini',
        'Harga_Satuan_Saat_Ini',
        'Total_Harga',
        'Tanggal_Hitung'
    ];

    public function Bahan() {
        return $this->belongsTo(ListBahan::class, 'ID_Bahan', 'ID_Bahan');
    }

    public function desainRumah() {
        return $this->belongsTo(DesainRumah::class, 'ID_Desain_Rumah', 'ID_Desain_Rumah');
    }

    public function user() {
        return $this->belongsTo(User::class, 'ID_User', 'id');
    }
}
