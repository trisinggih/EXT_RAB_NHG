<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Suppliers extends Authenticatable
{
    use HasFactory;
    protected $table = 'supplier';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'address',
        'telp',
        'pic',
        'email',
        'password'
    ];
}
