<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::paginate(10);
        return Inertia::render('role/Index', [
            'roles' => $roles
        ]);

    }

    public function create(): Response
    {
        return Inertia::render('role/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => 'required|string|max:255|unique:role',
            'description' => 'nullable|string|max:500',
        ]);
        Role::create($data);
        return redirect()->route('roles.index')->with('message', 'Role created successfully.');
    }

    public function edit(Role $role): Response
    {
        return Inertia::render('role/Edit', compact('role'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'role' => 'required|string|max:255|unique:role,role,' . $role->id,
            'description' => 'nullable|string|max:500',
        ]);
        $role->update($data);
        return redirect()->route('roles.index')->with('message', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();
        return redirect()->route('roles.index')->with('message', 'Role deleted successfully.');
    }
}

  