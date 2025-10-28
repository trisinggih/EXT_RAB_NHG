@if(isset($projectGambar) && count($projectGambar) > 0)
    <table width="100%" cellpadding="8" cellspacing="0" style="border: none;">
        @foreach ($projectGambar->chunk(2) as $row)
            <tr>
                @foreach ($row as $gambar)
                    <td colspan="6">
                        @php
                            $path = public_path('storage/' . $gambar->gambar);
                        @endphp

                        @if(file_exists($path))
                            <img 
                                src="{{ $path }}" 
                                style="width:350px; height:200px; border:1px solid #ccc; border-radius:6px;"
                                alt="Lampiran {{ $loop->parent->iteration }}-{{ $loop->iteration }}"
                            >
                        @else
                          Gambar tidak ditemukan
                        @endif

                        @if(!empty($gambar->keterangan))
                            
                                {{ $gambar->keterangan }}
                         
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
   <table width="100%" cellpadding="8" cellspacing="0" style="border: none;">
        <tr>
            <td colspan="6">Tidak ada gambar lampiran untuk project ini</td>
        </tr>
            
    </table>
@endif