<?php

namespace App\Http\Controllers;

use App\Models\Suppliers;
use App\Models\SupplierMaterials;
use App\Models\Materials;
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

    public function storeMaterials(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => 'required|int',
            'material_id' => 'required|int',
            'price' => 'required|numeric',
            'link' => 'nullable',
        ]);
        $cekdata = SupplierMaterials::where('supplier_id', $data['supplier_id'])->where('material_id', $data['material_id'])->count();
        if($cekdata > 0) {
            SupplierMaterials::where('supplier_id', $data['supplier_id'])->where('material_id', $data['material_id'])->update($data);
            return redirect()->route('suppliers.material', $data['supplier_id'])->with('message', 'Supplier updated successfully.');
        } else {
            SupplierMaterials::create($data);
            return redirect()->route('suppliers.material', $data['supplier_id'])->with('message', 'Supplier added successfully.');
        }
        
    }

    public function edit(Suppliers $supplier): Response
    {

        return Inertia::render('supplier/Edit', compact('supplier'));
    }

    public function material(Suppliers $supplier): Response
    {
        $materials = SupplierMaterials::join('material', 'supplier_material.material_id', '=', 'material.id')
            ->select('supplier_material.*', 'material.name as material_name', 'material.satuan as material_satuan')
            ->where('supplier_material.supplier_id', $supplier->id)
            ->get();

        $ms_material = Materials::where('deleted_at', null)->get();
        return Inertia::render('supplier/Material', compact('supplier', 'materials', 'ms_material'));
    }

    public function materialjson($supplier): \Illuminate\Http\JsonResponse
    {
        $materials = SupplierMaterials::join('supplier', 'supplier_material.supplier_id', '=', 'supplier.id')
            ->join('material', 'supplier_material.material_id', '=', 'material.id')
            ->select('supplier_material.*','material.name as material_name', 'material.satuan as material_satuan', 'supplier.name as supplier_name', 'supplier.address as supplier_address', 'supplier.telp as supplier_telp', 'supplier.pic as supplier_pic')
            ->where('supplier_material.material_id', $supplier)
            ->get();

         return response()->json($materials);
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

    public function destroyMaterial($id): RedirectResponse
    {
        $data = SupplierMaterials::where('id',$id)->first();
        $material = SupplierMaterials::where('id',$id)->delete();
         
        return redirect()->route('suppliers.material', $data['supplier_id'])->with('message', 'Supplier updated successfully.');

    }

}

  