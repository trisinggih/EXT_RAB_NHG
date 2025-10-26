<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectGambar extends Model
{
    use HasFactory;
    protected $table = 'project_gambar';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'project_id',
        'gambar',
        'keterangan'
    ];
}
