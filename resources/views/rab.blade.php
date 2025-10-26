<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rencana Anggaran Biaya (RAB)</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        h2, h3 {
            text-align: center;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f3f3f3;
        }
        .text-right {
            text-align: right;
        }
        .subtotal {
            background: #eef;
            font-weight: bold;
        }
        .grandtotal {
            background: #dfe;
            font-weight: bold;
            font-size: 14px;
        }
        .page-break {
            page-break-before: always;
        }


    </style>
</head>
<body>


<table width="100%" cellspacing="0" cellpadding="4" style="border-collapse: collapse;">
    <tr>
        <td width="15%" style="vertical-align: middle;">
            <img src="{{ public_path('images/nhg.png') }}" height="80" alt="Logo">
        </td>
        <td style="vertical-align: middle; text-align: left;">
            <div style="font-size: 20px; font-weight: bold; text-transform: uppercase;">
                PT. NUSANTARA HYDROGATE
            </div>
            <div style="font-size: 12px; color: #333; line-height: 1.4;">
                Jl. Merdeka No. 45, Bandung 40111<br>
                Telp: (022) 123456 | Email: info@nhg.com
            </div>
        </td>
    </tr>
</table>

    <h2>RENCANA ANGGARAN BIAYA (RAB)</h2>
    <p style="text-align:center;">{{ $project['name'] ?? 'Nama Project Tidak Diketahui' }}</p>

    
         <table>
            <thead>
                <tr>
                    <th style="width:5%;">No</th>
                    <th style="width:35%;">Uraian Pekerjaan</th>
                    <th style="width:10%;">Satuan</th>
                    <th style="width:10%;" class="text-right">Kuantitas</th>
                    <th style="width:15%;" class="text-right">Harga Satuan</th>
                    <th style="width:15%;" class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>

            @php
                $grandTotalAll = 0;
                $nomor = 0;
            @endphp

            {{-- Loop setiap produk --}}
            @foreach ($projectProduct as $product)

            <tr style="font-weight: bold; background-color: #ffffff;">
                <td colspan="6"></td>
            </tr>

                        @php
                            $grandTotalProduct = 0;
                            $pekerjaanList = collect($projectPekerjaan)->where('product_id', $product['product_id'])->values();
                        @endphp

                        @forelse ($pekerjaanList as $pekerjaan)
                            @php
                                $details = is_string($pekerjaan['detail'])
                                    ? json_decode($pekerjaan['detail'], true)
                                    : $pekerjaan['detail'];
                                $subtotal = 0;
                            @endphp

                            {{-- Detail BOM / item pekerjaan --}}
                            @forelse ($details as $index => $d)
                                @php
                                    $qty = floatval($d['total_jumlah'] ?? 0);
                                    $harga = floatval($d['total_estimasi_price'] ?? 0);
                                    $total = $qty * $harga;
                                    $subtotal += $total;
                                @endphp

                            @empty

                            @endforelse

                            {{-- Subtotal per pekerjaan --}}
         
                            @php
                                $grandTotalProduct += $subtotal;
                            @endphp
                        @empty
              
                        @endforelse

                        {{-- Grand total per produk --}}
                        <tr style="font-weight: bold; background-color: #e6f7ff;">
                            <td>{{ ++$nomor }}</td>
                            <td  class="text-left">{{ $product['product_name'] }}</td>
                            <td  class="text-left">{{ $product['satuan'] }}</td>
                            <td  class="text-left">{{ $product['jumlah'] }}</td>
                            <td  class="text-left">{{ number_format($grandTotalProduct, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format(($grandTotalProduct*$product['jumlah']), 0, ',', '.') }}</td>
                        </tr>

                        @php
                            $grandTotalAll += ($grandTotalProduct*$product['jumlah']);
                        @endphp
       
            @endforeach


                </tbody>
    </table>


 

    @php
    // perhitungan sesuai contoh
    $profit = $grandTotalAll * 0.20;
    $feeKantor = $grandTotalAll * 0.20;
    $feeStaf = $grandTotalAll * 0.02;
    $feeKonsultan = $grandTotalAll * 0.03;
    $feeBendera = $grandTotalAll * 0.03;
    $feeMarketing = $grandTotalAll * 0.02;
    $pph = $grandTotalAll * 0.0265;
    $ppn = $grandTotalAll * 0.11;

    $grandTotalFinal = $grandTotalAll + $profit + $feeKantor + $feeStaf + $feeKonsultan + $feeBendera + $feeMarketing + $pph + $ppn;
@endphp

<table width="100%" cellspacing="0" cellpadding="6" style="border-collapse: collapse; margin-top: 30px; font-size: 13px;">
    <tr style="background-color: #f8f9fa; font-weight: bold;">
        <td style="text-align:left;">TOTAL AWAL</td>
        <td style="text-align:right; color: green;">
            Rp {{ number_format($grandTotalAll, 0, ',', '.') }}
        </td>
    </tr>
    <tr>
        <td>a. Profit 20%</td>
        <td style="text-align:right;">Rp {{ number_format($profit, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>b. Fee Kantor 20%</td>
        <td style="text-align:right;">Rp {{ number_format($feeKantor, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>c. Fee Staf 2%</td>
        <td style="text-align:right;">Rp {{ number_format($feeStaf, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>d. Fee Konsultan 3%</td>
        <td style="text-align:right;">Rp {{ number_format($feeKonsultan, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>e. Fee Bendera 3%</td>
        <td style="text-align:right;">Rp {{ number_format($feeBendera, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>f. Fee Marketing 2%</td>
        <td style="text-align:right;">Rp {{ number_format($feeMarketing, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>g. PPh 2.65%</td>
        <td style="text-align:right;">Rp {{ number_format($pph, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>h. PPN 11%</td>
        <td style="text-align:right;">Rp {{ number_format($ppn, 0, ',', '.') }}</td>
    </tr>
    <tr style="background-color: #f8f9fa; font-weight: bold;">
        <td>TOTAL AKHIR (Grand Total)</td>
        <td style="text-align:right; color: green;">
            Rp {{ number_format($grandTotalFinal, 0, ',', '.') }}
        </td>
    </tr>
</table>










    <div style="page-break-before: always;"></div>


 

    <h2>ANALISA  HARGA SATUAN  PEKERJAAN</h2>
    <p style="text-align:center;">{{ $project['name'] ?? 'Nama Project Tidak Diketahui' }}</p>



     <table>
            <thead>
                <tr>
                    <th style="width:5%;">No</th>
                    <th style="width:35%;">Uraian</th>
                    <th style="width:10%;">Satuan</th>
                    <th style="width:10%;" class="text-right">Kuantitas</th>
                    <th style="width:15%;" class="text-right">Harga Satuan</th>
                    <th style="width:15%;" class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>

            @php
                $grandTotalAll = 0;
            @endphp

            {{-- Loop setiap produk --}}
            @foreach ($projectProduct as $product)

            <tr style="font-weight: bold; background-color: #fafafa;">
                <td colspan="6">{{ $product['product_name'] ?? 'Tanpa Nama Produk' }}</td>
            </tr>

                        @php
                            $grandTotalProduct = 0;
                            $pekerjaanList = collect($projectPekerjaan)->where('product_id', $product['product_id'])->values();
                        @endphp

                        @forelse ($pekerjaanList as $pekerjaan)
                            @php
                                $details = is_string($pekerjaan['detail'])
                                    ? json_decode($pekerjaan['detail'], true)
                                    : $pekerjaan['detail'];
                                $subtotal = 0;
                            @endphp

                            {{-- Judul pekerjaan --}}
                            <tr style="font-weight: bold; background-color: #fafafa;">
                                <td colspan="1"></td>
                                <td colspan="5">{{ $pekerjaan['pekerjaan_name'] ?? 'Tanpa Nama Pekerjaan' }}</td>
                            </tr>

                            {{-- Detail BOM / item pekerjaan --}}
                            @forelse ($details as $index => $d)
                                @php
                                    $qty = floatval($d['total_jumlah'] ?? 0);
                                    $harga = floatval($d['total_estimasi_price'] ?? 0);
                                    $total = $qty * $harga;
                                    $subtotal += $total;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $d['tambahan'] ?? '-' }}</td>
                                    <td>{{ $d['satuan'] ?? '-' }}</td>
                                    <td class="text-right">{{ number_format($qty, 2, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($harga, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada detail</td>
                                </tr>
                            @endforelse

                            {{-- Subtotal per pekerjaan --}}
                            <tr style="font-weight: bold; background-color: #f9f9f9;">
                                <td colspan="5" class="text-right">Subtotal {{ $pekerjaan['pekerjaan_name'] }}</td>
                                <td class="text-right">{{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>

                            @php
                                $grandTotalProduct += $subtotal;
                            @endphp
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada pekerjaan untuk produk ini</td>
                            </tr>
                        @endforelse

                        {{-- Grand total per produk --}}
                        <tr style="font-weight: bold; background-color: #e6f7ff;">
                            <td colspan="5" class="text-right">Grand Total {{ $product['product_name'] }}</td>
                            <td class="text-right">{{ number_format($grandTotalProduct, 0, ',', '.') }}</td>
                        </tr>

                        @php
                            $grandTotalAll += $grandTotalProduct;
                        @endphp
       
            @endforeach


                </tbody>
    </table>



    <div style="page-break-before: always;"></div>


 

    <h2>Lampiran</h2>
    <br>

    @if(isset($projectGambar) && count($projectGambar) > 0)
    <table width="100%" cellpadding="8" cellspacing="0" style="border: none;">
        @foreach ($projectGambar->chunk(2) as $row)
            <tr>
                @foreach ($row as $gambar)
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none;">
                        @php
                            $path = public_path('storage/' . $gambar->gambar);
                        @endphp

                        @if(file_exists($path))
                            <img 
                                src="{{ $path }}" 
                                style="width:100%; max-width:350px; height:auto; border:1px solid #ccc; border-radius:6px;"
                                alt="Lampiran {{ $loop->parent->iteration }}-{{ $loop->iteration }}"
                            >
                        @else
                            <div style="color:#999; font-style:italic;">Gambar tidak ditemukan</div>
                        @endif

                        @if(!empty($gambar->keterangan))
                            <div style="margin-top:5px; font-size:12px; color:#333;">
                                {{ $gambar->keterangan }}
                            </div>
                        @endif
                    </td>
                @endforeach

                {{-- Jika baris hanya 1 gambar, tambahkan kolom kosong agar tetap dua kolom --}}
                @if($row->count() < 2)
                    <td style="width: 50%; border:none;"></td>
                @endif
            </tr>
        @endforeach
    </table>
@else
    <p style="text-align:center; color:#888;">Tidak ada gambar lampiran untuk project ini.</p>
@endif





</body>
</html>
