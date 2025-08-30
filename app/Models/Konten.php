<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konten extends Model
{
    use HasFactory;
    protected $table = 'konten';
    protected $primaryKey = 'id';
    protected $fillable = [
        'judul_konten',
        'konten',
        'img_konten',
    ];
}
