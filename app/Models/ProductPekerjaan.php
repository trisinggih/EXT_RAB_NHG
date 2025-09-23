<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPekerjaan extends Model
{
    use HasFactory;
    protected $table = 'product_pekerjaan';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'pekerjaan_id',
    ];



    
}
