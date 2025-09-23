<?php

namespace App\Http\Controllers;

use App\Models\ProjectProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectProductController extends Controller
{


    public function ambil($id): \Illuminate\Http\JsonResponse
    {
        $pekerjaan = ProjectProduct::join('products', 'project_product.product_id', '=', 'products.id')->select('project_product.*', 'products.name as product_name')->where('project_product.project_id', $id)->get();
        return response()->json($pekerjaan);

    }

}

  