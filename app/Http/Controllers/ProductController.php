<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Image;
use App\Models\ProductMaterials;
use App\Models\Annotation;
use App\Models\Materials;
use App\Models\ProductPekerjaan;
use App\Models\ProductDetail;
use App\Models\Pekerjaan;
use App\Models\Bom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        // get all the resent products inside the db.
        $products = Product::get();
        return Inertia::render('products/Index', compact('products'));
    }

    public function create(): Response
    {
        // we render the create page of the products.
        return Inertia::render('products/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string'
        ]);

        // save the data into the db.
        Product::create($data);

        // redirect to another page. Here is the index page of the products' manager.
        return redirect()->route('products.index')->with('message', 'Product created successfully.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('products/Edit', compact('product'));
    }

    public function material(Product $product): Response
    {

        $material = ProductMaterials::join('material', 'product_materials.material_id', '=', 'material.id')
            ->where('product_materials.product_id', $product->id)
            ->get();
        $image = Image::where('product_id', $product->id)->first();
        if(!$image){
            $annotations = [];
        }else{
            $annotations = Annotation::where('image_id', $image->id)->get();
        }
        
        $ms_material = Materials::where('deleted_at', null)->get();
        $bom = Bom::where('product_id', $product->id)->get();
        return Inertia::render('products/Material', compact('product', 'material', 'image', 'annotations', 'ms_material', 'bom'));
    }

    public function services(Product $product): Response
    {
        $pekerjaan = Pekerjaan::get();
        $productpekerjaan = ProductPekerjaan::join('pekerjaan', 'product_pekerjaan.pekerjaan_id', '=', 'pekerjaan.id')
            ->select('product_pekerjaan.*', 'pekerjaan.name as pekerjaan_name')
            ->where('product_id', $product->id)->get();
        $productdetail = ProductDetail::where('product_id', $product->id)->get();
        return Inertia::render('products/Services', compact('product', 'pekerjaan', 'productpekerjaan', 'productdetail'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string'
        ]);

        // update the product values.
        $product->update([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'description' => $request->input('description')
        ]);
        return redirect()->route('products.index')->with('message', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('products.index')->with('message', 'Product deleted successfully.');
    }

    public function destroyMaterial($id): RedirectResponse
    {
        $material = ProductMaterials::find($id);
        $material->delete();
         
        return redirect()->back()->with('error', 'Product material not found.');
    }
}
