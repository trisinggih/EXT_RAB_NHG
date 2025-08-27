<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banners extends Model
{
    use HasFactory;
    protected $table = 'banners';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title_banner',
        'img_banner',
        'link_banner',
    ];
}
