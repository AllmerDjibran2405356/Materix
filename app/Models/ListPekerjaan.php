<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListPekerjaan extends Model
{
    use HasFactory;

    protected $table = 'list_kode_pekerjaan';
    protected $primaryKey = 'ID_Pekerjaan';
}

?>
