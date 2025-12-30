<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $users = User::select('id', 'name', 'email')
            ->with('roles')
            ->get()
            ->map(function ($user) {
                $user->role = $user->roles->pluck('name')->first() ?? '—';
                return $user;
            });

        $roles = Role::pluck('name');

        // If JS/Inertia isn't available (direct navigation), render a server-side Blade fallback
        if (!$request->header('X-Inertia')) {
            return view('admin.roles.index', [
                'users' => $users,
                'roles' => $roles
            ]);
        }

        return Inertia::render('Role/Index', [
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->syncRoles([$request->role]);

        return back()->with('success', 'Role updated!');
    }

    public function remove(User $user)
    {
        $user->syncRoles([]); // Remove all roles from user

        return back()->with('success', 'Role removed successfully!');
    }
}
