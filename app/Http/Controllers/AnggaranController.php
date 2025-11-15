<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Pekerjaan;
use App\Models\ProjectPekerjaan;
use App\Models\ProjectProduct;
use App\Models\ProjectDetailTambahan;
use App\Models\ProjectDetail;
use App\Models\Product;
use App\Models\ProductMaterials;
use App\Models\Jasa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

use App\Exports\RABExport;
use Maatwebsite\Excel\Facades\Excel;

class AnggaranController extends Controller
{
    public function index(): Response
    {
        $projects = Project::where('rab','<','4')->get();
        $pekerjaan = Pekerjaan::get();
        $product = Product::get();
        $jasa = Jasa::get();
        return Inertia::render('anggaran/Index', [
            'projects' => $projects,
            'pekerjaan' => $pekerjaan,
            'product' => $product,
            'jasa' => $jasa,
        ]);

    }

    public function indexFront(): Response
    {
        $projects = Project::get();
        $pekerjaan = Pekerjaan::get();
        $product = Product::get();
        $jasa = Jasa::get();
        return Inertia::render('anggaran/IndexFront', [
            'projects' => $projects,
            'pekerjaan' => $pekerjaan,
            'product' => $product,
            'jasa' => $jasa,
        ]);

    }

    public function anggaranpekerjaan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required',
            'pekerjaan_id' => 'required',
            'product_id' => 'required'
        ]);
        $cekdata = ProjectPekerjaan::where('project_id', $validated['project_id'])->where('pekerjaan_id', $validated['pekerjaan_id'])->where('product_id', $validated['product_id'])->first();
        if (!$cekdata) {
            ProjectPekerjaan::create([
                'project_id' => $validated['project_id'],
                'pekerjaan_id' => $validated['pekerjaan_id'],
                'product_id' => $validated['product_id'],
            ]);
        }
        
        return redirect()->route('anggarans.index')->with('success', 'Anggaran pekerjaan berhasil ditambahkan.');
    }

    public function projectpekerjaan($id): \Illuminate\Http\JsonResponse
    {
        $query = DB::select("
SELECT DISTINCT 
    a.project_id, 
    a.pekerjaan_id, 
		a.product_id,
		c.name as product_name,
    b.name AS pekerjaan_name,
    (
        SELECT JSON_ARRAYAGG(
                   JSON_OBJECT(
                        'id', t.id,
                        'type', t.type,
						'product_id', t.product_id,
                       'pekerjaan_id', t.pekerjaan_id,
                       'material_id', t.material_id,
                       'rab', t.rab,
                       'tambahan', t.tambahan,
                       'total_jumlah', t.total_jumlah,
                       'total_estimasi_price', t.total_estimasi_price,
                       'satuan', t.satuan,
					   'supplier_name', t.supplier_name
                   )
               )
        FROM (
            SELECT 
                d.id,
                'bom' as type,
				d.product_id,
                x.pekerjaan_id,
                d.tambahan,
                d.material_id,
                d.rab as rab,
                SUM(d.jumlah) AS total_jumlah,
                SUM(d.estimasi_price) AS total_estimasi_price,
                MAX(d.satuan) AS satuan,
								xc.`name` as supplier_name
            FROM project_detail d
            JOIN product_pekerjaan x ON d.pekerjaan_id = x.id
            JOIN pekerjaan w ON x.pekerjaan_id = w.id
						LEFT JOIN supplier xc on xc.id = d.supplier_id
            WHERE d.project_id = a.project_id
						and d.product_id=a.product_id
            GROUP BY d.product_id, x.pekerjaan_id, d.tambahan,d.rab, d.material_id, d.id

            UNION ALL

            SELECT 
                d.id,
                'tambahan' as type,
				d.product_id,
                d.pekerjaan_id,
                d.tambahan,
                null as material_id,
                d.rab as rab,
                SUM(d.jumlah) AS total_jumlah,
                SUM(d.estimasi_price) AS total_estimasi_price,
                MAX(d.satuan) AS satuan,
								xc.`name` as supplier_name
            FROM project_detail_tambahan d
            JOIN pekerjaan w ON d.pekerjaan_id = w.id
						LEFT JOIN supplier xc on xc.id = d.supplier_id
            WHERE d.project_id = a.project_id
						and d.product_id=a.product_id
            GROUP BY d.product_id, d.pekerjaan_id, d.rab, d.tambahan, d.id
        ) t
        WHERE t.pekerjaan_id = a.pekerjaan_id
    ) AS detail
FROM project_pekerjaan AS a 

JOIN pekerjaan AS b ON a.pekerjaan_id = b.id
Join products as c on a.product_id=c.id
            WHERE a.project_id = ?
        ", [$id]); 

        return response()->json($query);
    }


    public function anggarandetail(Request $request): RedirectResponse
    {
        if($request->id == '' || $request->id == null)
        {
            ProjectDetailTambahan::create([
                'project_id' => $request->project_id,
                'product_id' => $request->product_id,
                'pekerjaan_id' => $request->pekerjaan_id,
                'tambahan' => $request->tambahan,
                'satuan' => $request->satuan,
                'jumlah' => $request->qty,
                'estimasi_price' => $request->harga,
                'rab' => $request->rab,
            ]);
        }else{
            if($request->type == 'bom')
            {
                ProjectDetail::where('id', $request->id)->update([
                    'tambahan' => $request->tambahan,
                    'project_id' => $request->project_id,
                    'product_id' => $request->product_id,
                    'pekerjaan_id' => $request->pekerjaan_id,
                    'satuan' => $request->satuan,
                    'jumlah' => $request->qty,
                    'estimasi_price' => $request->harga,
                    'rab' => $request->rab,
                ]);
            }else{
                ProjectDetailTambahan::where('id', $request->id)->update([
                    'tambahan' => $request->tambahan,
                    'project_id' => $request->project_id,
                    'product_id' => $request->product_id,
                    'pekerjaan_id' => $request->pekerjaan_id,
                    'satuan' => $request->satuan,
                    'jumlah' => $request->qty,
                    'estimasi_price' => $request->harga,
                    'rab' => $request->rab,
                ]);
            }

        }
        
          
        
        return redirect()->route('anggarans.index')->with('success', 'Detail anggaran berhasil ditambahkan.');

    }


    public function projectrabawal(Request $request, $id): RedirectResponse
    {
        $project = Project::find($id);
        if ($project) {
            $project->rab = '2';
            $project->save();
        }

        DB::table('project_detail')->insertUsing(
            [
                'project_id',
                'pekerjaan_id',
                'material_id',
                'jasa_id',
                'tambahan',
                'jumlah',
                'estimasi_price',
                'rab',
                'satuan',
                'product_id',
                'supplier_id'
            ],
            DB::table('project_detail')
                ->select(
                    'project_id',
                    'pekerjaan_id',
                    'material_id',
                    'jasa_id',
                    'tambahan',
                    'jumlah',
                    'estimasi_price',
                    DB::raw("'Kedua' AS rab"),
                    'satuan',
                    'product_id',
                    'supplier_id'
                )
                ->where('project_id', $id)
                ->where('rab', 'Awal')
        );

        DB::table('project_detail_tambahan')->insertUsing(
            [
                'project_id',
                'pekerjaan_id',
                'tambahan',
                'jumlah',
                'estimasi_price',
                'rab',
                'satuan',
                'product_id',
                'supplier_id'
            ],
            DB::table('project_detail_tambahan')
                ->select(
                    'project_id',
                    'pekerjaan_id',
                    'tambahan',
                    'jumlah',
                    'estimasi_price',
                    DB::raw("'Kedua' AS rab"),
                    'satuan',
                    'product_id',
                    'supplier_id'
                )
                ->where('project_id', $id)
                ->where('rab', 'Awal')
        );



        return redirect()->route('anggarans.index')->with('success', 'RAB Awal berhasil diapprove.');
    }

    public function projectrabkedua(Request $request, $id): RedirectResponse
    {
        $project = Project::find($id);
        if ($project) {
            $project->rab = '3';
            $project->save();
        }


         DB::table('project_detail')->insertUsing(
            [
                'project_id',
                'pekerjaan_id',
                'material_id',
                'jasa_id',
                'tambahan',
                'jumlah',
                'estimasi_price',
                'rab',
                'satuan',
                'product_id',
                'supplier_id'
            ],
            DB::table('project_detail')
                ->select(
                    'project_id',
                    'pekerjaan_id',
                    'material_id',
                    'jasa_id',
                    'tambahan',
                    'jumlah',
                    'estimasi_price',
                    DB::raw("'Final' AS rab"),
                    'satuan',
                    'product_id',
                    'supplier_id'
                )
                ->where('project_id', $id)
                ->where('rab', 'Kedua')
        );

        DB::table('project_detail_tambahan')->insertUsing(
            [
                'project_id',
                'pekerjaan_id',
                'tambahan',
                'jumlah',
                'estimasi_price',
                'rab',
                'satuan',
                'product_id',
                'supplier_id'
            ],
            DB::table('project_detail_tambahan')
                ->select(
                    'project_id',
                    'pekerjaan_id',
                    'tambahan',
                    'jumlah',
                    'estimasi_price',
                    DB::raw("'Final' AS rab"),
                    'satuan',
                    'product_id',
                    'supplier_id'
                )
                ->where('project_id', $id)
                ->where('rab', 'Kedua')
        );

        
        return redirect()->route('anggarans.index')->with('success', 'RAB Kedua berhasil diapprove.');
    }

    public function projectrabfinal(Request $request, $id): RedirectResponse
    {
        $project = Project::find($id);
        if ($project) {
            $project->rab = '4';
            $project->save();
        }


        return redirect()->route('anggarans.index')->with('success', 'RAB Final berhasil diapprove.');
    }



    public function distribusiDanaUpdate($projectId, $totalDaaaa)
    {

        $summaryBiaya = DB::table('project_detail')
            ->where('project_id', $projectId)
            ->where('rab', 'Kedua')
            ->whereNull('material_id')
            ->select(DB::raw('SUM(estimasi_price * jumlah) AS total'))
            ->first();

        if($summaryBiaya->total > 100000000){
            $totalDana = 10000000;
        }else{
            $totalDana = 5000000;
        }
   
        $items = DB::table('project_detail')
            ->where('project_id', $projectId)
            ->where('rab', 'Kedua')
            ->whereNull('material_id')
            ->select('id', 'jumlah', 'estimasi_price')
            ->orderBy('id', 'ASC')
            ->get();

        // 2. Hitung total awal
        $items = $items->map(function ($item) {
            $item->harga_satuan = $item->estimasi_price;
            // $item->total = $item->estimasi_price * $item->jumlah ;
            return $item;
        });

        // 3. Loop distribusi dana incremental tanpa sort
        // $penggunaanDana = 0;
        while (true) {
            $alokasi = false;

            foreach ($items as $item) {

                // Jika masih cukup untuk tambah 1 unit
                if ($totalDana >= $item->harga_satuan) {

                    $item->jumlah += 1;
                    // $item->total  += $item->harga_satuan;

                    $totalDana -= $item->harga_satuan;

                    $alokasi = true;
                }
            }

            // Jika dalam seluruh putaran tidak ada penambahan
            if (!$alokasi) break;
        }

        // 4. Simpan hasilnya ke database
        foreach ($items as $item) {
            DB::table('project_detail')
                ->where('id', $item->id)
                ->update([
                    'jumlah' => $item->jumlah
                ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "Distribusi dana selesai tanpa sortBy.",
            "sisa_dana" => $totalDana
        ]);
    }




    public function anggarandelete($tambahan, $id): RedirectResponse
    {
        // $detail = ProjectDetail::find($id);
        // if ($detail) {
        //     $detail->delete();
        // }
        ProjectDetail::where('tambahan', $tambahan)->where('project_id', $id)->delete();
        ProjectDetailTambahan::where('tambahan', $tambahan)->where('project_id', $id)->delete();
        return redirect()->route('anggarans.index')->with('success', 'Detail anggaran berhasil dihapus.');
    }

    public function anggaranpekerjaandelete($id): RedirectResponse
    {
        $detail = projectpekerjaan::find($id);
        if ($detail) {
            $detail->delete();
        }
        return redirect()->route('anggarans.index')->with('success', 'Detail anggaran berhasil dihapus.');
    }


    public function exportRAB($id)
    {
        return Excel::download(new RABExport($id), 'RAB_Project_'.$id.'.xlsx');
    }

}