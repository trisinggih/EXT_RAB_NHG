<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectGambar;
use Mpdf\Mpdf;

class RABController extends Controller
{
    public function generatePDF(Request $request)
    {

        $projectPekerjaan = json_decode(urldecode($request->query('projectPekerjaan')), true);
        $projectProduct   = json_decode(urldecode($request->query('product')), true);
        $projectId        = $request->query('project');


        $project = Project::where('id', $projectId)->first();
        $projectGambar = ProjectGambar::where('project_id', $projectId)->get();

        // Render view blade
        $html = view('rab', compact('projectPekerjaan', 'projectProduct', 'project', 'projectGambar'))->render();

        // Buat PDF
        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->SetTitle('Rencana Anggaran Biaya');
        $mpdf->WriteHTML($html);
        $mpdf->Output('RAB.pdf', 'I'); // 'I' = tampilkan di browser
    }
}
