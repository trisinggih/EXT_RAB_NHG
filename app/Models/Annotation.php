<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Annotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_id',
        'x',
        'y',
        'label',
        'color',
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
