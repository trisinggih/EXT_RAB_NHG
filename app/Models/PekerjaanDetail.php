<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PekerjaanDetail extends Model
{
    use HasFactory;
    protected $table = 'pekerjaan_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'pekerjaan_id',
        'name',
        'jumlah',
        'biaya',
        'subtotal',
        'satuan'
    ];



    
}
