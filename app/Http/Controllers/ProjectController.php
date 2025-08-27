<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Clients;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        $projects = Project::join('client', 'project.client_id', '=', 'client.id')
        ->select('project.*', 'client.name as client_name') // pilih kolom yang dibutuhkan
        ->orderBy('project.id', 'desc')
        ->get();

        return Inertia::render('project/Index', [
            'projects' => $projects
        ]);

    }

    public function create(): Response
    {
        $clients = Clients::get();
        return Inertia::render('project/Create', [
            'clients' => $clients
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2048',
            'client_id' => 'required|int',
        ]);
        Project::create($data);
        return redirect()->route('projects.index')->with('message', 'Project created successfully.');
    }

    public function edit($projects): Response
    {
        $clients = Clients::get();
        $projects = Project::find($projects);
        return Inertia::render('project/Edit', [
            'projects' => $projects,
            'clients' => $clients
        ]);
    }

    public function update(Request $request, Project $project ): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2048',
            'client_id' => 'required|int',
        ]);
        $project->update($data);
        return redirect()->route('projects.index')->with('message', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();
        return redirect()->route('projects.index')->with('message', 'Project deleted successfully.');
    }
}

  