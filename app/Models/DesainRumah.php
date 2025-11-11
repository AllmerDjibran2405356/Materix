<?
 namespace App\Models;

 use Illuminate\Database\Eloquent\Factories\HasFactory;
 use Illuminate\Database\Eloquent\Model;

 class DesainRumah extends Model
 {
     use HasFactory;
 

 protected $table = 'desain_rumah';

 protected $primaryKey = 'id';
 public $incrementing = true;
 protected $keyType = 'bigint'