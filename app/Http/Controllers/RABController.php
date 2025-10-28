<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectGambar;
use App\Models\ProjectProduct;
use Mpdf\Mpdf;

use Illuminate\Support\Facades\DB;

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


    public function generatePDFID(Request $request)
    {

        // $projectPekerjaan = json_decode(urldecode($request->query('projectPekerjaan')), true);
        // $projectProduct   = json_decode(urldecode($request->query('product')), true);
        // $projectId        = $request->query('project');

        $projectId        = $request->query('project');

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
                        GROUP BY d.product_id, x.pekerjaan_id, d.tambahan, d.material_id, d.id

                        UNION ALL

                        SELECT 
                            d.id,
                            'tambahan' as type,
                                            d.project_id,
                            d.pekerjaan_id,
                            d.tambahan,
                            null as material_id,
                            SUM(d.jumlah) AS total_jumlah,
                            SUM(d.estimasi_price) AS total_estimasi_price,
                            MAX(d.satuan) AS satuan,
                                            xc.`name` as supplier_name
                        FROM project_detail_tambahan d
                        JOIN pekerjaan w ON d.pekerjaan_id = w.id
                                    LEFT JOIN supplier xc on xc.id = d.supplier_id
                        WHERE d.project_id = a.project_id
                                    and d.product_id=a.product_id
                        GROUP BY d.product_id, d.pekerjaan_id, d.tambahan, d.id
                    ) t
                    WHERE t.pekerjaan_id = a.pekerjaan_id
                ) AS detail
            FROM project_pekerjaan AS a 
            JOIN pekerjaan AS b ON a.pekerjaan_id = b.id
            Join products as c on a.product_id=c.id
                        WHERE a.project_id = ?
        ", [$projectId]); 

        $query = json_decode(json_encode($query), true);
        
        $projectPekerjaan = $query;
        // $projectProduct   = ProjectProduct::where('project_id', $projectId)->get();

        $projectProduct = ProjectProduct::join('products', 'project_product.product_id', '=', 'products.id')
            ->where('project_product.project_id', $projectId)
            ->select('project_product.*', 'products.name as product_name')
            ->get();
        

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
