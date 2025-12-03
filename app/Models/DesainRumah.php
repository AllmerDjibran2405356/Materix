<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesainRumah extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'desain_rumah';

    // Primary key
    protected $primaryKey = 'ID_Desain_Rumah';

    // Jika tabel tidak memiliki created_at & updated_at
    public $timestamps = false;

    // Kolom yang boleh diisi mass assignment
    protected $fillable = [
        'id_user',
        'Nama_Desain',
        'Tanggal_Dibuat',
        'Nama_File'
    ];

    // Relasi ke tabel komponen desain
    public function komponen()
    {
        return $this->hasMany(
            KomponenDesain::class,
            'ID_Desain_Rumah',
            'ID_Desain_Rumah'
        );
    }
}
