<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RABExport;

class RABExportController extends Controller
{
    public function export(Request $request)
    {
        // $projectPekerjaan = $request->input('projectPekerjaan');

        $projectPekerjaan = $request->input('projectPekerjaan');

        if (is_string($projectPekerjaan)) {
            $projectPekerjaan = json_decode($projectPekerjaan, true);
        }
        
        $projectProduct = $request->input('projectProduct');

        if (is_string($projectProduct)) {
            $projectProduct = json_decode($projectProduct, true);
        }

        return Excel::download(
            new RABExport($projectPekerjaan, $projectProduct),
            'RAB-' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}
