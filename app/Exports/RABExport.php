<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RABExport implements FromView
{
    protected $projectPekerjaan;
    protected $projectProduct;

    public function __construct($projectPekerjaan, $projectProduct)
    {
        $this->projectPekerjaan = $projectPekerjaan;
        $this->projectProduct = $projectProduct;
    }

    public function view(): View
    {
        return view('rab-excel', [
            'projectPekerjaan' => $this->projectPekerjaan,
            'projectProduct' => $this->projectProduct,
        ]);
    }
}
