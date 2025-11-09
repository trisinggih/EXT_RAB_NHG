<?php

namespace App\Http\Controllers;

use App\Models\Banners;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    public function index(): Response
    {
        $banners = Banners::all();
        return Inertia::render('banner/Index', [
            'banners' => $banners
        ]);

    }

    public function create(): Response
    {
        return Inertia::render('banner/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title_banner' => 'required|string|max:255',
            'img_banner' => 'required|image|max:2048',
            'link_banner' => 'required',
        ]);
        if ($request->hasFile('img_banner')) {
            $image = $request->file('img_banner');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images'), $imageName);
            $data['img_banner'] = $imageName; 
        }
        
        Banners::create($data);
        return redirect()->route('banners.index')->with('message', 'Banner created successfully.');
    }

    public function edit(Banners $banner): Response
    {
        return Inertia::render('banner/Edit', compact('banner'));
    }

    public function update(Request $request, Banners $banner): RedirectResponse
    {
        $data = $request->validate([
            'title_banner' => 'required|string|max:255',
            'img_banner' => 'required|image|max:2048',
            'link_banner' => 'required',
        ]);
        if ($request->hasFile('img_banner')) {
            $image = $request->file('img_banner');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images'), $imageName);
            $data['img_banner'] = $imageName; 
        }
        $banner->update($data);
        return redirect()->route('banners.index')->with('message', 'Banner updated successfully.');
    }

    public function destroy(Banners $banner): RedirectResponse
    {
        $banner->delete();
        return redirect()->route('banners.index')->with('message', 'Banner deleted successfully.');
    }
    
}

  