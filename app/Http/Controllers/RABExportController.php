<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectGambar;
use App\Models\ProjectProduct;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use App\Exports\RABExport;

use Illuminate\Support\Facades\DB;

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

// public function export2(Request $request)
// {
//     $projectId = $request->query('project');

//     // === Ambil Data ===
//     $query = DB::select("
//         SELECT DISTINCT 
//             a.project_id, 
//             a.pekerjaan_id, 
//             a.product_id,
//             c.name AS product_name,
//             b.name AS pekerjaan_name,
//             (
//                 SELECT JSON_ARRAYAGG(
//                     JSON_OBJECT(
//                         'id', t.id,
//                         'type', t.type,
//                         'product_id', t.product_id,
//                         'pekerjaan_id', t.pekerjaan_id,
//                         'material_id', t.material_id,
//                         'tambahan', t.tambahan,
//                         'total_jumlah', t.total_jumlah,
//                         'total_estimasi_price', t.total_estimasi_price,
//                         'satuan', t.satuan,
//                         'supplier_name', t.supplier_name
//                     )
//                 )
//                 FROM (
//                     SELECT 
//                         d.id,
//                         'bom' AS type,
//                         d.product_id,
//                         x.pekerjaan_id,
//                         d.tambahan,
//                         d.material_id,
//                         SUM(d.jumlah) AS total_jumlah,
//                         SUM(d.estimasi_price) AS total_estimasi_price,
//                         MAX(d.satuan) AS satuan,
//                         xc.name AS supplier_name
//                     FROM project_detail d
//                     JOIN product_pekerjaan x ON d.pekerjaan_id = x.id
//                     JOIN pekerjaan w ON x.pekerjaan_id = w.id
//                     LEFT JOIN supplier xc ON xc.id = d.supplier_id
//                     WHERE d.project_id = a.project_id
//                         AND d.product_id = a.product_id
//                     GROUP BY d.product_id, x.pekerjaan_id, d.tambahan, d.material_id, d.id

//                     UNION ALL

//                     SELECT 
//                         d.id,
//                         'tambahan' AS type,
//                         d.project_id,
//                         d.pekerjaan_id,
//                         d.tambahan,
//                         NULL AS material_id,
//                         SUM(d.jumlah) AS total_jumlah,
//                         SUM(d.estimasi_price) AS total_estimasi_price,
//                         MAX(d.satuan) AS satuan,
//                         xc.name AS supplier_name
//                     FROM project_detail_tambahan d
//                     JOIN pekerjaan w ON d.pekerjaan_id = w.id
//                     LEFT JOIN supplier xc ON xc.id = d.supplier_id
//                     WHERE d.project_id = a.project_id
//                         AND d.product_id = a.product_id
//                     GROUP BY d.product_id, d.pekerjaan_id, d.tambahan, d.id
//                 ) t
//                 WHERE t.pekerjaan_id = a.pekerjaan_id
//             ) AS detail
//         FROM project_pekerjaan AS a 
//         JOIN pekerjaan AS b ON a.pekerjaan_id = b.id
//         JOIN products AS c ON a.product_id = c.id
//         WHERE a.project_id = ?
//     ", [$projectId]);

//     $projectPekerjaan = json_decode(json_encode($query), true);

//     $projectProduct = \App\Models\ProjectProduct::join('products', 'project_product.product_id', '=', 'products.id')
//         ->where('project_product.project_id', $projectId)
//         ->select('project_product.*', 'products.name as product_name')
//         ->get();

//     $count = $projectProduct->count();
//     $nextcount = $count + 14; // misalnya 14 baris setelah tabel produk

//     // === Export ke Excel ===
//     return Excel::download(
//         new class($projectPekerjaan, $projectProduct, $nextcount) implements 
//             \Maatwebsite\Excel\Concerns\FromView, 
//             \Maatwebsite\Excel\Concerns\WithEvents 
//         {
//             private $projectPekerjaan, $projectProduct, $nextcount;

//             public function __construct($projectPekerjaan, $projectProduct, $nextcount)
//             {
//                 $this->projectPekerjaan = $projectPekerjaan;
//                 $this->projectProduct = $projectProduct;
//                 $this->nextcount = $nextcount;
//             }

//             public function view(): \Illuminate\Contracts\View\View
//             {
//                 return view('rab-excel', [
//                     'projectPekerjaan' => $this->projectPekerjaan,
//                     'projectProduct' => $this->projectProduct,
//                 ]);
//             }

//             public function registerEvents(): array
//             {
//                 return [
//                     \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
//                         $sheet = $event->sheet->getDelegate();
//                         $nextcount = $this->nextcount; // ✅ ambil dari property

//                         // Format kolom jadi TEXT supaya angka tidak berubah
//                         $sheet->getStyle('A:F')->getNumberFormat()
//                             ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

//                         // 💡 Set lebar kolom statis
//                         $sheet->getColumnDimension('A')->setWidth(6);
//                         $sheet->getColumnDimension('B')->setWidth(40);
//                         $sheet->getColumnDimension('C')->setWidth(12);
//                         $sheet->getColumnDimension('D')->setWidth(12);
//                         $sheet->getColumnDimension('E')->setWidth(15);
//                         $sheet->getColumnDimension('F')->setWidth(18);

//                         // 💡 Style Header utama
//                         $sheet->getStyle('A1:F1')->applyFromArray([
//                             'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
//                             'fill' => [
//                                 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                                 'startColor' => ['rgb' => '1F497D']
//                             ],
//                             'alignment' => [
//                                 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
//                                 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
//                             ],
//                             'borders' => [
//                                 'allBorders' => [
//                                     'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
//                                     'color' => ['rgb' => '000000']
//                                 ]
//                             ]
//                         ]);

//                         // 💡 Style Header kedua pakai nextcount
//                         $sheet->getStyle("A{$nextcount}:F{$nextcount}")->applyFromArray([
//                             'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
//                             'fill' => [
//                                 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                                 'startColor' => ['rgb' => '1F497D']
//                             ],
//                             'alignment' => [
//                                 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
//                                 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
//                             ],
//                             'borders' => [
//                                 'allBorders' => [
//                                     'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
//                                     'color' => ['rgb' => '000000']
//                                 ]
//                             ]
//                         ]);

//                         // 💡 Border semua tabel
//                         $highestRow = $sheet->getHighestRow();
//                         $sheet->getStyle("A1:F{$highestRow}")->applyFromArray([
//                             'borders' => [
//                                 'allBorders' => [
//                                     'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
//                                     'color' => ['rgb' => 'CCCCCC']
//                                 ]
//                             ]
//                         ]);
//                         $sheet->getStyle("A1:F{$highestRow}")
//                             ->getAlignment()->setWrapText(true);
//                     }
//                 ];
//             }
//         },
//         'RAB-' . now()->format('Ymd_His') . '.xlsx'
//     );
// }


public function export2(Request $request)
{
    $projectId = $request->query('project');

    // === Ambil Data ===
    $query = DB::select("
        SELECT DISTINCT 
            a.project_id, 
            a.pekerjaan_id, 
            a.product_id,
            c.name AS product_name,
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
                        'bom' AS type,
                        d.product_id,
                        x.pekerjaan_id,
                        d.tambahan,
                        d.material_id,
                        SUM(d.jumlah) AS total_jumlah,
                        SUM(d.estimasi_price) AS total_estimasi_price,
                        MAX(d.satuan) AS satuan,
                        xc.name AS supplier_name
                    FROM project_detail d
                    JOIN product_pekerjaan x ON d.pekerjaan_id = x.id
                    JOIN pekerjaan w ON x.pekerjaan_id = w.id
                    LEFT JOIN supplier xc ON xc.id = d.supplier_id
                    WHERE d.project_id = a.project_id
                        AND d.product_id = a.product_id
                    GROUP BY d.product_id, x.pekerjaan_id, d.tambahan, d.material_id, d.id

                    UNION ALL

                    SELECT 
                        d.id,
                        'tambahan' AS type,
                        d.project_id,
                        d.pekerjaan_id,
                        d.tambahan,
                        NULL AS material_id,
                        SUM(d.jumlah) AS total_jumlah,
                        SUM(d.estimasi_price) AS total_estimasi_price,
                        MAX(d.satuan) AS satuan,
                        xc.name AS supplier_name
                    FROM project_detail_tambahan d
                    JOIN pekerjaan w ON d.pekerjaan_id = w.id
                    LEFT JOIN supplier xc ON xc.id = d.supplier_id
                    WHERE d.project_id = a.project_id
                        AND d.product_id = a.product_id
                    GROUP BY d.product_id, d.pekerjaan_id, d.tambahan, d.id
                ) t
                WHERE t.pekerjaan_id = a.pekerjaan_id
            ) AS detail
        FROM project_pekerjaan AS a 
        JOIN pekerjaan AS b ON a.pekerjaan_id = b.id
        JOIN products AS c ON a.product_id = c.id
        WHERE a.project_id = ?
    ", [$projectId]);

    $projectPekerjaan = json_decode(json_encode($query), true);

    $projectProduct = \App\Models\ProjectProduct::join('products', 'project_product.product_id', '=', 'products.id')
        ->where('project_product.project_id', $projectId)
        ->select('project_product.*', 'products.name as product_name')
        ->get();

    $projectGambar = ProjectGambar::where('project_id', $projectId)->get();

    $count = $projectProduct->count();
    $nextcount = $count;

    $countpekerjaan = count($projectPekerjaan);
    $countdetail = 0;

    foreach ($projectPekerjaan as $row) {
        $details = json_decode($row['detail'], true);
        if (is_array($details)) {
            $countdetail += count($details);
        }
    }

    return Excel::download(new class($projectPekerjaan, $projectProduct, $nextcount, $projectGambar, $countpekerjaan, $countdetail) implements WithMultipleSheets {
        private $projectPekerjaan, $projectProduct, $nextcount, $projectGambar, $countpekerjaan, $countdetail;

        public function __construct($projectPekerjaan, $projectProduct, $nextcount, $projectGambar, $countpekerjaan, $countdetail)
        {
            $this->projectPekerjaan = $projectPekerjaan;
            $this->projectProduct = $projectProduct;
            $this->nextcount = $nextcount;
            $this->projectGambar = $projectGambar;
            $this->countpekerjaan = $countpekerjaan;
            $this->countdetail = $countdetail;
        }

        public function sheets(): array
        {
            $sheets = [];

            for ($i = 1; $i <= 3; $i++) {
                $sheets[] = new class(
                    $this->projectPekerjaan,
                    $this->projectProduct,
                    $this->nextcount,
                    $i,
                    $this->projectGambar,
                    $this->countpekerjaan,
                    $this->countdetail
                ) implements FromView, WithEvents {
                    private $projectPekerjaan, $projectProduct, $nextcount, $index, $projectGambar, $countpekerjaan, $countdetail;

                    public function __construct($projectPekerjaan, $projectProduct, $nextcount, $index, $projectGambar, $countpekerjaan, $countdetail)
                    {
                        $this->projectPekerjaan = $projectPekerjaan;
                        $this->projectProduct = $projectProduct;
                        $this->nextcount = $nextcount;
                        $this->index = $index;
                        $this->projectGambar = $projectGambar;
                        $this->countpekerjaan = $countpekerjaan;
                        $this->countdetail = $countdetail;
                    }

                    public function view(): \Illuminate\Contracts\View\View
                    {
                        $viewName = "rab-excel{$this->index}";
                        return view($viewName, [
                            'projectPekerjaan' => $this->projectPekerjaan,
                            'projectProduct' => $this->projectProduct,
                            'projectGambar' => $this->projectGambar
                        ]);
                    }

                    public function registerEvents(): array
                    {
                        return [
                            AfterSheet::class => function (AfterSheet $event) {
                                $nextcount = $this->nextcount;

                                $sheet = $event->sheet->getDelegate();
                                $sheet->getColumnDimension('A')->setWidth(6);
                                $sheet->getColumnDimension('B')->setWidth(40);
                                $sheet->getColumnDimension('C')->setWidth(30);
                                $sheet->getColumnDimension('D')->setWidth(12);
                                $sheet->getColumnDimension('E')->setWidth(15);
                                $sheet->getColumnDimension('F')->setWidth(18);

                                if ($this->index != 3) {
                                    $sheet->getStyle('A1:F1')->applyFromArray([
                                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                                        'fill' => [
                                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                            'startColor' => ['rgb' => '1F497D']
                                        ],
                                        'alignment' => [
                                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                        ],
                                        'borders' => [
                                            'allBorders' => [
                                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                'color' => ['rgb' => '000000']
                                            ]
                                        ]
                                    ]);
                                }

                                if ($this->index == 1) {
                                    $fillGray = [
                                        'fill' => [
                                            'fillType' => Fill::FILL_SOLID,
                                            'startColor' => ['rgb' => 'E8E8E8'],
                                        ],
                                    ];
                                    $rowAfter10 = $nextcount + 3;
                                    $sheet->getStyle("A{$rowAfter10}:F{$rowAfter10}")->applyFromArray($fillGray);
                                    $rowAfter11 = $rowAfter10 + 11;
                                    $sheet->getStyle("A{$rowAfter11}:F{$rowAfter11}")->applyFromArray($fillGray);
                                }

                                if ($this->index == 2) {
                                    $currentRow = 2;
                                    foreach ($this->projectProduct as $product) {
                                        $sheet->setCellValue("A{$currentRow}", $product->product_name);
                                        $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
                                        $sheet->getStyle("A{$currentRow}")->applyFromArray([
                                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
                                        ]);
                                        $currentRow++;

                                        $totalProduct = 0;
                                        $pekerjaans = array_filter($this->projectPekerjaan, fn($p) => $p['product_id'] == $product->product_id);

                                        foreach ($pekerjaans as $pk) {
                                            $sheet->setCellValue("B{$currentRow}", "Pekerjaan: " . $pk['pekerjaan_name']);
                                            $sheet->getStyle("B{$currentRow}")->applyFromArray([
                                                'font' => ['bold' => true],
                                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                                            ]);
                                            $currentRow++;

                                            $details = json_decode($pk['detail'], true) ?? [];
                                            $subtotalPekerjaan = 0;

                                            foreach ($details as $dt) {
                                                $jumlah = $dt['total_jumlah'] ?? 0;
                                                $harga = $dt['total_estimasi_price'] ?? 0;
                                                $subtotal = $jumlah * $harga;
                                                $subtotalPekerjaan += $subtotal;

                                               $sheet->setCellValue("C{$currentRow}", $dt['tambahan'] ?? '-');

                                                // Format jumlah, harga, dan subtotal dengan pemisah ribuan
                                                $jumlah = isset($dt['total_jumlah']) ? (float) $dt['total_jumlah'] : 0;
                                                $harga = isset($dt['total_estimasi_price']) ? (float) $dt['total_estimasi_price'] : 0;
                                                $subtotal = $jumlah * $harga;

                                                // Set nilai ke Excel
                                                $sheet->setCellValue("D{$currentRow}", number_format($jumlah, 0, ',', '.'));
                                                $sheet->setCellValue("E{$currentRow}", 'Rp ' . number_format($harga, 0, ',', '.'));
                                                $sheet->setCellValue("F{$currentRow}", 'Rp ' . number_format($subtotal, 0, ',', '.'));

                                                // (Opsional) tambahkan perataan kanan untuk kolom angka
                                                $sheet->getStyle("D{$currentRow}:F{$currentRow}")
                                                    ->getAlignment()
                                                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                                                $currentRow++;
                                            }

                                            // subtotal per pekerjaan
                                            $sheet->mergeCells("B{$currentRow}:E{$currentRow}");
                                            $sheet->setCellValue("B{$currentRow}", "Subtotal " . $pk['pekerjaan_name']);

                                            $sheet->setCellValue("F{$currentRow}", 'Rp ' . number_format($subtotalPekerjaan, 0, ',', '.'));

                                            $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']],
                                                'font' => ['bold' => true],
                                            ]);
                                            $currentRow++;

                                            $totalProduct += $subtotalPekerjaan;
                                        }

                                        // total per product
                                        $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
                                        $sheet->setCellValue("A{$currentRow}", "TOTAL PRODUCT: " . $product->product_name);

                                        $sheet->setCellValue("F{$currentRow}", 'Rp ' . number_format($totalProduct, 0, ',', '.'));


                                        $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D0CECE']],
                                            'font' => ['bold' => true],
                                        ]);
                                        $currentRow += 2;
                                    }
                                }

                                $highestRow = $sheet->getHighestRow();
                                $sheet->getStyle("A1:F{$highestRow}")->applyFromArray([
                                    'borders' => [
                                        'allBorders' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => ['rgb' => 'CCCCCC']
                                        ]
                                    ]
                                ]);

                                if ($this->index == 1) {
                                    $sheet->setTitle("RAB");
                                } elseif ($this->index == 2) {
                                    $sheet->setTitle("Analisa Satuan");
                                } else {
                                    $sheet->setTitle("Lampiran");
                                }
                            }
                        ];
                    }
                };
            }

            return $sheets;
        }
    }, 'RAB-' . now()->format('Ymd_His') . '.xlsx');
}








}
