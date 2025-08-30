<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDetail extends Model
{
    use HasFactory;
    protected $table = 'project_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'project_id',
        'pekerjaan_id',
        'material_id',
        'jasa_id',
        'tambahan',
        'jumlah',
        'estimasi_price',
        'rab',
        'satuan',
    ];
}
