<table style="border-collapse: collapse; width: 100%; font-size: 13px;">
    <colgroup>
        <col style="width:5%">
        <col style="width:35%">
        <col style="width:10%">
        <col style="width:10%">
        <col style="width:15%">
        <col style="width:15%">
    </colgroup>

        @php
            $grandTotalAll = 0;
            $nomor = 0;
        @endphp

        {{-- Loop setiap produk --}}
        @foreach ($projectProduct as $product)
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

                @forelse ($details as $index => $d)
                    @php
                        $qty = floatval($d['total_jumlah'] ?? 0);
                        $harga = floatval($d['total_estimasi_price'] ?? 0);
                        $total = $qty * $harga;
                        $subtotal += $total;
                    @endphp
                @empty
                @endforelse

                @php
                    $grandTotalProduct += $subtotal;
                @endphp
            @empty
            @endforelse

            {{-- Grand total per produk --}}

            @php
                $grandTotalAll += ($grandTotalProduct*$product['jumlah']);
            @endphp
        @endforeach


{{-- Rincian Akhir --}}
@php
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
                                    <td class="text-right">Rp {{ number_format($qty, 2, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada detail</td>
                                </tr>
                            @endforelse

                            {{-- Subtotal per pekerjaan --}}
                            <tr style="font-weight: bold; background-color: #f9f9f9;">
                                <td colspan="5" class="text-right">Subtotal {{ $pekerjaan['pekerjaan_name'] }}</td>
                                <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
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
                            <td class="text-right">Rp {{ number_format($grandTotalProduct, 0, ',', '.') }}</td>
                        </tr>

                        @php
                            $grandTotalAll += $grandTotalProduct;
                        @endphp
       
            @endforeach


                </tbody>
    </table>