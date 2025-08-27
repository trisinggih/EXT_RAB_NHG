<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnggaranController extends Controller
{
    public function index(): Response
    {
        $projects = Project::get();
        return Inertia::render('anggaran/Index', [
            'projects' => $projects
        ]);

    }

    
}

  