<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDetailTambahan extends Model
{
    use HasFactory;
    protected $table = 'project_detail_tambahan';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'project_id',
        'pekerjaan_id',
        'tambahan',
        'jumlah',
        'estimasi_price',
        'rab',
        'satuan',
    ];
}
