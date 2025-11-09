<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
        $clients = Clients::all();
        return Inertia::render('client/Index', [
            'clients' => $clients
        ]);

    }

    public function create(): Response
    {
        return Inertia::render('client/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'telp' => 'required',
            'address' => 'required|string|max:255',
        ]);
        Clients::create($data);
        return redirect()->route('clients.index')->with('message', 'Clients created successfully.');
    }

    public function edit(Clients $client): Response
    {

        return Inertia::render('client/Edit', compact('client'));
    }

    public function update(Request $request, Clients $client): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'telp' => 'required',
            'address' => 'required|string|max:255',
        ]);
        $client->update($data);
        return redirect()->route('clients.index')->with('message', 'Client updated successfully.');
    }

    public function destroy(Clients $client): RedirectResponse
    {
        $client->delete();
        return redirect()->route('clients.index')->with('message', 'Client deleted successfully.');
    }
}

  