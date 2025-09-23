<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RABExport implements FromCollection, WithHeadings, WithEvents
{
    protected $projectId;
    protected $headerRows = []; // simpan nomor baris header pekerjaan

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function collection()
    {
        $data = DB::select("
            select DISTINCT a.project_id, a.pekerjaan_id, b.name as pekerjaan_name,
            (
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'pekerjaan_id', t.pekerjaan_id,
                        'tambahan', t.tambahan,
                        'total_jumlah', t.total_jumlah,
                        'total_estimasi_price', t.total_estimasi_price,
                        'satuan', t.satuan
                    )
                )
                FROM (
                    SELECT 
                        x.pekerjaan_id,
                        d.tambahan,
                        SUM(d.jumlah) AS total_jumlah,
                        SUM(d.estimasi_price) AS total_estimasi_price,
                        MAX(d.satuan) AS satuan
                    FROM project_detail d
                    JOIN product_pekerjaan x ON d.pekerjaan_id = x.id
                    JOIN pekerjaan w ON x.pekerjaan_id = w.id
                    WHERE d.project_id = a.project_id
                    GROUP BY x.pekerjaan_id, d.tambahan

                    UNION ALL

                    SELECT 
                        d.pekerjaan_id,
                        d.tambahan,
                        SUM(d.jumlah) AS total_jumlah,
                        SUM(d.estimasi_price) AS total_estimasi_price,
                        MAX(d.satuan) AS satuan
                    FROM project_detail_tambahan d
                    JOIN pekerjaan w ON d.pekerjaan_id = w.id
                    WHERE d.project_id = a.project_id
                    GROUP BY d.pekerjaan_id, d.tambahan
                ) t
                WHERE t.pekerjaan_id = a.pekerjaan_id
            ) AS detail
            from project_pekerjaan as a 
            join pekerjaan as b on a.pekerjaan_id=b.id
            where a.project_id = ?
        ", [$this->projectId]);

        $rows = collect();
        $rowIndex = 2; // heading ada di baris 1

        foreach ($data as $row) {
            // Simpan baris pekerjaan untuk di merge & warnai nanti
            $this->headerRows[] = $rowIndex;

            $rows->push([
                $row->pekerjaan_name, '', '', '', ''
            ]);
            $rowIndex++;

            $details = json_decode($row->detail, true) ?? [];
            foreach ($details as $d) {
                $rows->push([
                    '', 
                    $d['tambahan'],
                    $d['total_jumlah'],
                    $d['satuan'],
                    $d['total_estimasi_price'],
                ]);
                $rowIndex++;
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Pekerjaan',
            'Tambahan',
            'Jumlah',
            'Satuan',
            'Harga',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach ($this->headerRows as $row) {
                    // Merge cell dari kolom A sampai E
                    $sheet->mergeCells("A{$row}:E{$row}");

                    // Styling aquamarine + bold
                    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'color' => ['rgb' => '7FFFD4'], // Aquamarine
                        ],
                    ]);
                }
            }
        ];
    }
}
