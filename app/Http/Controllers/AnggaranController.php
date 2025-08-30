<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Pekerjaan;
use App\Models\ProjectPekerjaan;
use App\Models\ProjectDetail;
use App\Models\Product;
use App\Models\ProductMaterials;
use App\Models\Jasa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnggaranController extends Controller
{
    public function index(): Response
    {
        $projects = Project::get();
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
        ]);
        ProjectPekerjaan::create([
            'project_id' => $validated['project_id'],
            'pekerjaan_id' => $validated['pekerjaan_id'],
        ]);
        return redirect()->route('anggarans.index')->with('success', 'Anggaran pekerjaan berhasil ditambahkan.');
    }

    public function projectpekerjaan($id): \Illuminate\Http\JsonResponse
    {
        $data = ProjectPekerjaan::with('details')
            ->join('pekerjaan', 'project_pekerjaan.pekerjaan_id', '=', 'pekerjaan.id')
            ->select('project_pekerjaan.*', 'pekerjaan.name as pekerjaan_name')
            ->where('project_pekerjaan.project_id', $id)
            ->get();
        return response()->json($data);
    }

    public function anggarandetail(Request $request): RedirectResponse
    {
  
        if ($request->type == 'produk') {
            $product = Product::find($request->product_id);
            $material = ProductMaterials::select('material.*', 'product_materials.jumlah', 'product_materials.estimasi_price')->join('material', 'product_materials.material_id', '=', 'material.id')
                -> where('product_materials.product_id', $request->product_id)->get();
            foreach ($material as $mat) {
                ProjectDetail::create([
                    'project_id' => $request->project_id,
                    'pekerjaan_id' => $request->pekerjaan_id,
                    'material_id' => $mat->id,
                    'tambahan' => $mat->name,
                    'satuan' => $mat->satuan,
                    'jumlah' => $mat->jumlah * ($request->qty ?? 1),
                    'estimasi_price' => $mat->estimasi_price,
                    'rab' => $request->rab,
                ]);
            }
        } else if ($request->type == 'jasa') {
            $jasa = Jasa::find($request->jasa_id);
            if ($jasa) {
                ProjectDetail::create([
                    'project_id' => $request->project_id,
                    'pekerjaan_id' => $request->pekerjaan_id,
                    'jasa_id' => $jasa->id,
                    'tambahan' => $jasa->name,
                    'satuan' => 'unit',
                    'jumlah' => $request->qty ?? 1,
                    'rab' => $request->rab,
                    'estimasi_price' => $jasa->estimasi_price,
                ]);
            }
        } elseif ($request->type === 'manual') {
            ProjectDetail::create([
                'project_id' => $request->project_id,
                'pekerjaan_id' => $request->pekerjaan_id,
                'tambahan' => $request->tambahan ?? '',
                'satuan' => $request->satuan ?? '',
                'jumlah' => $request->qty ?? 0,
                'estimasi_price' => $request->harga ?? 0,
                'rab' => $request->rab,
            ]);
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
        return redirect()->route('anggarans.index')->with('success', 'RAB Awal berhasil diapprove.');
    }

    public function projectrabkedua(Request $request, $id): RedirectResponse
    {
        $project = Project::find($id);
        if ($project) {
            $project->rab = '3';
            $project->save();
        }
        return redirect()->route('anggarans.index')->with('success', 'RAB Awal berhasil diapprove.');
    }



    public function anggarandelete($id): RedirectResponse
    {
        $detail = ProjectDetail::find($id);
        if ($detail) {
            $detail->delete();
        }
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

}