<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Pekerjaan</th>
            <th>Nama Produk</th>
            <th>Item</th>
            <th>Satuan</th>
            <th>Jumlah</th>
            <th>Harga Satuan</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach ($projectProduct as $product)
            <tr>
                <td colspan="8" style="font-weight:bold;">{{ $product['product_name'] }}</td>
            </tr>

            @foreach ($projectPekerjaan as $pekerjaan)
                @if ($pekerjaan['product_id'] == $product['product_id'])
                    @php
                        $details = is_string($pekerjaan['detail'])
                            ? json_decode($pekerjaan['detail'], true)
                            : $pekerjaan['detail'];
                    @endphp

                    <tr>
                        <td></td>
                        <td colspan="7" style="font-weight:bold;">{{ $pekerjaan['pekerjaan_name'] }}</td>
                    </tr>

                    @foreach ($details as $d)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $pekerjaan['pekerjaan_name'] }}</td>
                            <td>{{ $product['product_name'] }}</td>
                            <td>{{ $d['tambahan'] ?? '-' }}</td>
                            <td>{{ $d['satuan'] ?? '-' }}</td>
                            <td>{{ $d['total_jumlah'] }}</td>
                            <td>{{ $d['total_estimasi_price'] }}</td>
                            <td>{{ $d['total_jumlah'] * $d['total_estimasi_price'] }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        @endforeach
    </tbody>
</table>
