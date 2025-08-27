<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PekerjaanController extends Controller
{
    public function index(): Response
    {
        $pekerjaan = Pekerjaan::paginate(10);
        return Inertia::render('pekerjaan/Index', [
            'pekerjaan' => $pekerjaan
        ]);

    }

    public function create(): Response
    {
        return Inertia::render('pekerjaan/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2048',
        ]);
        Pekerjaan::create($data);
        return redirect()->route('pekerjaan.index')->with('message', 'Pekerjaan created successfully.');
    }

    public function edit(Pekerjaan $pekerjaan): Response
    {
        return Inertia::render('pekerjaan/Edit', compact('pekerjaan'));
    }

    public function update(Request $request, Pekerjaan $pekerjaan): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2048',
        ]);
        $pekerjaan->update($data);
        return redirect()->route('pekerjaan.index')->with('message', 'Pekerjaan updated successfully.');
    }

    public function destroy(Pekerjaan $pekerjaan): RedirectResponse
    {
        $pekerjaan->delete();
        return redirect()->route('pekerjaan.index')->with('message', 'Pekerjaan deleted successfully.');
    }
}

  