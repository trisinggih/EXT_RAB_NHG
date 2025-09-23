<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bom extends Model
{
    use HasFactory;

    protected $table = 'bom';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'mark_id',
        'material_id',
        'product_id',
        'satuan',
        'jumlah',
        'panjang',
        'lebar',
        'tinggi',
        'estimasi_price'
    ];


}
