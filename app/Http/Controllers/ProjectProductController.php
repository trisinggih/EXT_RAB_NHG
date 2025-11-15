<?php

namespace App\Http\Controllers;

use App\Models\ProjectProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class ProjectProductController extends Controller
{

public function ambil($id, $type): \Illuminate\Http\JsonResponse
{
    // Tentukan jenis RAB
    $jenis = 'Awal';
    if ($type == '0') {
        $jenis = 'Awal';
    } else if ($type == '1') {
        $jenis = 'Kedua';
    } else {
        $jenis = 'Final';
    }

    $pekerjaan = DB::select("
        SELECT 
            pp.*, 
            p.name AS product_name,

            (
                COALESCE(
                    (SELECT SUM(d.estimasi_price * d.jumlah)
                     FROM project_detail d
                     WHERE d.project_id = pp.project_id
                       AND d.rab = '{$jenis}'
                       AND d.product_id = pp.product_id), 0
                )
                +
                COALESCE(
                    (SELECT SUM(dt.estimasi_price * dt.jumlah)
                     FROM project_detail_tambahan dt
                     WHERE dt.project_id = pp.project_id
                       AND dt.rab = '{$jenis}'
                       AND dt.product_id = pp.product_id), 0
                )
            ) AS total,

            (
                pp.jumlah * (
                    COALESCE(
                        (SELECT SUM(d.estimasi_price * d.jumlah)
                         FROM project_detail d
                         WHERE d.project_id = pp.project_id
                           AND d.rab = '{$jenis}'
                           AND d.product_id = pp.product_id), 0
                    )
                    +
                    COALESCE(
                        (SELECT SUM(dt.estimasi_price * dt.jumlah)
                         FROM project_detail_tambahan dt
                         WHERE dt.project_id = pp.project_id
                           AND dt.rab = '{$jenis}'
                           AND dt.product_id = pp.product_id), 0
                    )
                )
            ) AS grandtotal

        FROM project_product pp
        JOIN products p ON pp.product_id = p.id

        WHERE pp.project_id = ?
    ", [$id]);

    return response()->json($pekerjaan);
}


}

  