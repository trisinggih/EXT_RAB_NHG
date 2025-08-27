<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductMaterials;
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

        $material = ProductMaterials::where('product_id', $product->id)->get();

        return Inertia::render('products/Material', compact('product', 'material'));
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
}
