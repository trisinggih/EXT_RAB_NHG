<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $query = DB::select("select count(a.id) total_a, (select count(b.id) from products as b ) as total_b, (select count(c.id) from client as c ) as total_c, (select count(d.id) from users as d ) as total_d from project as a");
        return response()->json($query[0]);
    }

    public function grafik(): \Illuminate\Http\JsonResponse
    {
        $query = DB::select("
            WITH RECURSIVE months AS (
                SELECT 1 AS bulan
                UNION ALL
                SELECT bulan + 1 FROM months WHERE bulan < 12
            )
            SELECT 
                m.bulan,
                COALESCE(COUNT(p.id), 0) AS total_project
            FROM months m
            LEFT JOIN project p 
            ON MONTH(p.created_at) = m.bulan
            AND YEAR(p.created_at) = YEAR(CURDATE())
            GROUP BY m.bulan
            ORDER BY m.bulan;
        ");
        return response()->json($query);
    }

}

  