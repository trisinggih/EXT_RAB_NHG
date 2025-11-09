<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Materials;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaterialController extends Controller
{
    public function index(): Response
    {
       $material = Materials::all();

        // if (Auth::guard('supplier')->check()) {
        //     return Inertia::render('material/IndexSupplier', [
        //         'material' => $material
        //     ]);
        // }

        return Inertia::render('material/Index', [
            'material' => $material
        ]);

    }


    // public function indexSup(): Response
    // {
    //    $material = Materials::paginate(10);

    //     return Inertia::render('material/IndexSupplier', [
    //         'material' => $material
    //     ]);

    // }


    public function indexSup(Request $request): Response
    {
        $search = $request->input('search');

        $query = Materials::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('satuan', 'LIKE', "%{$search}%");
        }

        $material = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('material/IndexSupplier', [
            'material' => $material,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }


    public function create(): Response
    {
        // if (Auth::guard('supplier')->check()) {
        //     return Inertia::render('material/CreateSupplier');
        // }
        return Inertia::render('material/Create');
    }

    public function createSup(): Response
    {
        // if (Auth::guard('supplier')->check()) {
        //     return Inertia::render('material/CreateSupplier');
        // }
        return Inertia::render('material/CreateSupplier');
    }

    public function store(Request $request): RedirectResponse
    {

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'satuan' => 'required|string|max:255',
        ]);
        Materials::create($data);
        return redirect()->route('materials.index')->with('message', 'Material created successfully.');
    }

    public function storeSup(Request $request): RedirectResponse
    {

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'satuan' => 'required|string|max:255',
        ]);
        Materials::create($data);
        return redirect()->route('supplier.materials.index')->with('message', 'Material created successfully.');
    }

    public function edit(Materials $material): Response
    {
        //  if (Auth::guard('supplier')->check()) {
        //     return Inertia::render('material/EditSupplier', compact('material'));
        // }
        return Inertia::render('material/Edit', compact('material'));
    }

    public function editSup(Materials $material): Response
    {
        //  if (Auth::guard('supplier')->check()) {
        //     return Inertia::render('material/EditSupplier', compact('material'));
        // }
        return Inertia::render('material/EditSupplier', compact('material'));
    }

    public function update(Request $request, Materials $material): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'satuan' => 'required|string|max:255',
        ]);
        $material->update($data);
        return redirect()->route('materials.index')->with('message', 'Material updated successfully.');
    }

    public function updateSup(Request $request, Materials $material): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'satuan' => 'required|string|max:255',
        ]);
        $material->update($data);
        return redirect()->route('supplier.materials.index')->with('message', 'Material updated successfully.');
    }

    public function destroy(Materials $material): RedirectResponse
    {
        $material->delete();
        return redirect()->route('materials.index')->with('message', 'Material deleted successfully.');
    }

    public function destroySup(Materials $material): RedirectResponse
    {
        $material->delete();
        return redirect()->route('supplier.materials.index')->with('message', 'Material deleted successfully.');
    }

    
}

  