<?php

namespace App\Http\Controllers;

use App\Models\ProductPekerjaan;
use App\Models\Pekerjaan;
use App\Models\ProductDetail;
use App\Models\PekerjaanDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductPekerjaanController extends Controller
{


    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required',
            'pekerjaan_id' => 'required',
        ]);
        $cek = ProductPekerjaan::where('product_id', $request->product_id)->where('pekerjaan_id',$request->pekerjaan_id)->count();
        if($cek == 0){
            $product = ProductPekerjaan::create($data);
            $id = $product->id;

            $cekdetail = PekerjaanDetail::where('pekerjaan_id', $request->pekerjaan_id)->get();
            foreach($cekdetail as $cd){
                ProductDetail::create([
                    'product_id' => $request->product_id,
                    'pekerjaan_id' =>  $id,
                    'tambahan' => $cd->name,
                    'jumlah' => $cd->jumlah,
                    'estimasi_price' => $cd->biaya,
                    'satuan' => $cd->satuan,
                    'pekerjaan_detail_id' => $cd->id,
                ]
                );
            }
        }

        

        

         
        return redirect()->route('products.services', $data['product_id'])->with('message', 'Service created successfully.');
    }

    public function ambil($ids): \Illuminate\Http\JsonResponse
    {

       $idArray = explode(',', $ids);

        // $pekerjaan = ProductPekerjaan::join('pekerjaan', 'product_pekerjaan.pekerjaan_id', '=', 'pekerjaan.id')
        //     ->select('product_pekerjaan.pekerjaan_id', 'pekerjaan.name as pekerjaan_name')
        //     ->whereIn('product_pekerjaan.product_id', $idArray) 
        //     ->distinct()
        //     ->get();
        $pekerjaan = Pekerjaan::get();


        return response()->json($pekerjaan);

    }


    public function storeDetail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required',
            'pekerjaan_id' => 'required',
            'tambahan' => 'required',
            'jumlah' => 'required',
            'estimasi_price' => 'required',
            'satuan' => 'required',
        ]);
 
        ProductDetail::create($data);
            
        return redirect()->route('products.services', $data['product_id'])->with('message', 'Service updated successfully.');
    }



    public function destroy($id): RedirectResponse
    {
        $pekerjaan = ProductPekerjaan::find($id);
        ProductDetail::where('pekerjaan_id', $pekerjaan->pekerjaan_id)->where('product_id', $pekerjaan->product_id)->delete();
        $pekerjaan->delete();
        return redirect()->route('products.services', $pekerjaan->product_id)->with('message', 'Bom deleted successfully.');
    }

    public function destroyDetail($id): RedirectResponse
    {
        $material = ProductDetail::find($id);
        $material->delete();
        return redirect()->route('products.services', $material->product_id)->with('message', 'Bom deleted successfully.');
    }
    
}

  