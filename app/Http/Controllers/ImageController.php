<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Image;


class ImageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'product_id' => 'required|exists:products,id',
        ]);

        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();

        // Simpan di public/project
        $image->move(public_path('project'), $imageName);
        $path = url('project/' . $imageName);

        $data = [
            'product_id'     => $request->product_id,
            'path'           => $path,
            'original_name'  => $image->getClientOriginalName(),
            'mime_type'      => "",
            'size'           => "0",
        ];

        $insert = Image::create($data);
        return redirect()->route('products.material', $request->product_id)->with('message', 'Banner created successfully.');
        
    }

    // public function store(Request $request)
    // {

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Image uploaded successfully',
    //         'path'    => $path,
    //         'data'    => $insert
    //     ]);
    // }

}
