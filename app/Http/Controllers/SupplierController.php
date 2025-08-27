<?php

namespace App\Http\Controllers;

use App\Models\Suppliers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function index(): Response
    {
        $suppliers = Suppliers::paginate(10);
        return Inertia::render('supplier/Index', [
            'suppliers' => $suppliers
        ]);

    }

    public function create(): Response
    {
        return Inertia::render('supplier/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'telp' => 'required|max:20',
            'address' => 'required|string|max:255',
            'pic' => 'required|string|max:255',
        ]);
        Suppliers::create($data);
        return redirect()->route('suppliers.index')->with('message', 'Suppliers created successfully.');
    }

    public function edit(Suppliers $supplier): Response
    {

        return Inertia::render('supplier/Edit', compact('supplier'));
    }

    public function maetrial(Suppliers $supplier): Response
    {

        return Inertia::render('supplier/Material', compact('supplier'));
    }

    public function update(Request $request, Suppliers $supplier): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'telp' => 'required|max:20',
            'address' => 'required|string|max:255',
            'pic' => 'required|string|max:255',
        ]);
        $supplier->update($data);
        return redirect()->route('suppliers.index')->with('message', 'Supplier updated successfully.');
    }

    public function destroy(Suppliers $supplier): RedirectResponse
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('message', 'Supplier deleted successfully.');
    }
}

  