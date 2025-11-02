<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPekerjaan extends Model
{
    use HasFactory;
    protected $table = 'project_pekerjaan';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'project_id',
        'pekerjaan_id',
        'product_id'
    ];


    public function details()
    {
        return $this->hasMany(ProjectDetail::class, 'project_id', 'project_id');
    }

    
}
