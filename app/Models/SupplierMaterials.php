<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierMaterials extends Model
{
    use HasFactory;
    protected $table = 'supplier_material';
    protected $primaryKey = 'id';
    protected $fillable = [
        'supplier_id',
        'material_id',
        'price'
    ];
}
