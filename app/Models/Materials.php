<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materials extends Model
{
    use HasFactory;
    protected $table = 'material';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'satuan',
        'jumlah',
        'panjang',
        'lebar',
        'tinggi',
        'estimasi_price'
    ];
}
