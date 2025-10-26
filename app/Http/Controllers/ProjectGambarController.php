<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectGambar;

class ProjectGambarController extends Controller
{


public function store(Request $request)
{
    $project_id = $request->input('project_id');
    $keterangans = $request->input('keterangan', []);

    foreach ($request->file('gambar', []) as $i => $file) {
        $path = $file->store('project_gambar', 'public');

        \App\Models\ProjectGambar::create([
            'project_id' => $project_id,
            'gambar' => $path,
            'keterangan' => $keterangans[$i] ?? '',
        ]);
    }

    return redirect()->back()->with('success', 'Gambar berhasil diupload!');
}



public function destroy($id)
{
    $gambar = \DB::table('project_gambar')->where('id', $id)->first();

    if ($gambar) {
        // Hapus file fisik
        if (\Storage::disk('public')->exists($gambar->gambar)) {
            \Storage::disk('public')->delete($gambar->gambar);
        }

        // Hapus record DB
        \DB::table('project_gambar')->where('id', $id)->delete();
    }

    return back()->with('success', 'Foto berhasil dihapus.');
}



}
