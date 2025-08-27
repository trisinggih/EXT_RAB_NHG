<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::join('role', 'users.role_id', '=', 'role.id')
            ->select('users.*', 'role.role as role_name')
            ->paginate(10);
        return Inertia::render('users/Index', [
            'users' => $users
        ]);

    }

    public function create(): Response
    {
        $roles = Role::get();
        return Inertia::render('users/Create',[
            'roles' => $roles
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:3',
            'role_id' => 'required|numeric',
            'jabatan' => 'required|string'
        ]);

        // save the data into the db.
        User::create($data);

        // redirect to another page.
        return redirect()->route('users.index')->with('message', 'User created successfully.');
    }

    public function edit(User $user): Response
    {
        $roles = Role::get();
        return Inertia::render('users/Edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'required|numeric',
            'jabatan' => 'required|string'
        ]);
        // update the user values.
        $user->update($data);

        // if password is provided, hash it and update.
        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password'));
            $user->save();
        }
        // redirect to another page.
        return redirect()->route('users.index')->with('message', 'User updated successfully.'); 
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        // redirect to another page.
        return redirect()->route('users.index')->with('message', 'User deleted successfully.');   
    }
}
