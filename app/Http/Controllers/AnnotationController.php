<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Annotation;

class AnnotationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'image_id' => 'required|exists:images,id',
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'label' => 'required|string',
        ]);

        Annotation::create([
            'product_id' => $request->product_id,
            'image_id' => $request->image_id,
            'x' => $request->x,
            'y' => $request->y,
            'label' => $request->label,
        ]);

        return back();
    }

}
