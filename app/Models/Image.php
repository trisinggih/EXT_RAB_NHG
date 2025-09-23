<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
    use HasFactory;

    protected $table = 'images';

    protected $fillable = [
        'product_id',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function annotations()
    {
        return $this->hasMany(Annotation::class);
    }
}
