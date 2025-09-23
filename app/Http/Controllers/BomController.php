<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\Annotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BomController extends Controller
{
    public function index(): Response
    {
        $bom = Bom::paginate(10);
        return Inertia::render('pekerjaan/Index', [
            'bom' => $bom
        ]);

    }


    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required',
            'mark_id' => 'required',
            'material_id' => 'required',
            'satuan' => 'required',
            'jumlah' => 'required',
            'panjang' => 'required',
            'lebar' => 'required',
            'tinggi' => 'required',
            'estimasi_price' => 'required',
        ]);
        $cekdata = Bom::where('product_id', $data['product_id'])->where('mark_id', $data['mark_id'])->where('material_id', $data['material_id'])->first();
        if ($cekdata) {
            Bom::where('id', $cekdata->id)->update([
                'satuan' => $data['satuan'],
                'jumlah' => $data['jumlah'],
                'panjang' => $data['panjang'],
                'lebar' => $data['lebar'],
                'tinggi' => $data['tinggi'],
                'estimasi_price' => $data['estimasi_price'],
            ]);
        }else{
            Bom::create($data);
        }        
        return redirect()->route('products.material', $data['product_id'])->with('message', 'Bom created successfully.');
    }


    public function destroy(Request $request): RedirectResponse
    {
        Bom::where('product_id', $request->input('product_id'))->where('mark_id', $request->input('mark_id'))->where('material_id', $request->input('material_id'))->delete();
        Annotation::where('id', $request->input('mark_id'))->delete();
        return redirect()->route('products.material', $request->input('product_id'))->with('message', 'Bom deleted successfully.');
    }
    
}

  