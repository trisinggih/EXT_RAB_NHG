<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\Annotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class BomController extends Controller
{
    public function index(): Response
    {
        $bom = Bom::all();
        return Inertia::render('pekerjaan/Index', [
            'bom' => $bom
        ]);

    }


    // public function store(Request $request): RedirectResponse
    // {
    //     $data = $request->validate([
    //         'product_id' => 'required',
    //         'mark_id' => 'required',
    //         'material_id' => 'required',
    //         'satuan' => 'required',
    //         'jumlah' => 'required',
    //         'panjang' => 'required',
    //         'lebar' => 'required',
    //         'tinggi' => 'required',
    //         'estimasi_price' => 'nullable',
    //         'keterangan' => 'nullable'
    //     ]);
    //     $cekdata = Bom::where('product_id', $data['product_id'])->where('mark_id', $data['mark_id'])->where('material_id', $data['material_id'])->first();
    //     if ($cekdata) {
    //         Bom::where('id', $cekdata->id)->update([
    //             'satuan' => $data['satuan'],
    //             'jumlah' => $data['jumlah'],
    //             'panjang' => $data['panjang'],
    //             'lebar' => $data['lebar'],
    //             'tinggi' => $data['tinggi'],
    //             'estimasi_price' => 0,
    //             'keterangan' => $data['keterangan']
    //         ]);
    //     }else{
    //         Bom::create($data);
    //     }        
    //     return redirect()->route('products.material', $data['product_id'])->with('message', 'Bom created successfully.');
    // }


    public function store(Request $request)
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
            'estimasi_price' => 'nullable',
            'keterangan' => 'nullable'
        ]);

        $cekdata = Bom::where('product_id', $data['product_id'])
            ->where('mark_id', $data['mark_id'])
            ->where('material_id', $data['material_id'])
            ->first();

        if ($cekdata) {
            $cekdata->update([
                'satuan' => $data['satuan'],
                'jumlah' => $data['jumlah'],
                'panjang' => $data['panjang'],
                'lebar' => $data['lebar'],
                'tinggi' => $data['tinggi'],
                'estimasi_price' => 0,
                'keterangan' => $data['keterangan']
            ]);
        } else {
            Bom::create($data);
        }

        return back()->with('message', 'Bom created successfully.');
    }


    public function destroy(Request $request): RedirectResponse
    {
        $productId = $request->input('product_id');
        $markId = $request->input('mark_id');
        $materialId = $request->input('material_id');

        // Cek apakah ada BOM terkait
        $bom = Bom::where('product_id', $productId)
            ->where('mark_id', $markId)
            ->where('material_id', $materialId)
            ->first();

        if ($bom) {
            // Jika ada BOM, hapus dulu BOM-nya
            $bom->delete();

            // Setelah itu hapus annotation
            Annotation::where('id', $markId)->delete();

            $message = 'BOM dan Annotation berhasil dihapus.';
        } else {
            // Jika tidak ada BOM, hapus annotation langsung
            Annotation::where('id', $markId)->delete();

            $message = 'Annotation berhasil dihapus (tanpa BOM).';
        }

        return redirect()
            ->route('products.material', $productId)
            ->with('message', $message);
    }

    
}

  