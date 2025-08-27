<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suppliers extends Model
{
    use HasFactory;
    protected $table = 'supplier';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'address',
        'telp',
        'pic'
    ];
}
