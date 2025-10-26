<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RABExport;

class RABExportController extends Controller
{
    public function export(Request $request)
{
    // Ambil projectPekerjaan dari POST atau GET
    $projectPekerjaan = $request->input('projectPekerjaan', $request->query('projectPekerjaan'));
    if (is_string($projectPekerjaan)) {
        $projectPekerjaan = json_decode(urldecode($projectPekerjaan), true);
    }

    // Ambil projectProduct dari POST atau GET
    $projectProduct = $request->input('projectProduct', $request->query('projectProduct'));
    if (is_string($projectProduct)) {
        $projectProduct = json_decode(urldecode($projectProduct), true);
    }

    // Download Excel
    return Excel::download(
        new RABExport($projectPekerjaan, $projectProduct),
        'RAB-' . now()->format('Ymd_His') . '.xlsx'
    );
}
}
