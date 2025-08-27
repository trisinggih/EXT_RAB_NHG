<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMaterials extends Model
{
    use HasFactory;
    protected $table = 'product_materials';
    protected $primaryKey = 'id';
    protected $fillable = [
        'product_id',
        'material_id',
        'jumlah',
        'estimasi_price',
    ];
}
