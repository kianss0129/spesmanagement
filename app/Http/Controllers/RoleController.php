<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
class RoleController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $users = User::select('id', 'name', 'email', 'created_at')
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                $user->role = $user->roles->pluck('name')->first() ?? '—';
                return $user;
            });

        $roles = Role::pluck('name');


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

    public function destroy(User $user)
{
    // Prevent deleting Super Admin
    if ($user->hasRole('Super Admin')) {
        return back()->with('error', 'Cannot delete Super Admin.');
    }

    // Prevent deleting self
    if ($user->id === auth()->id()) {
        return back()->with('error', 'You cannot delete your own account.');
    }

    DB::transaction(function () use ($user) {

        // Remove roles first
        $user->syncRoles([]);

        // If user has beneficiary record
        if ($user->beneficiary) {

            // Delete applications first
            $user->beneficiary->applications()->delete();

            // Delete beneficiary
            $user->beneficiary->delete();
        }

        // Finally delete user
        $user->delete();
    });

    return back()->with('success', 'User deleted successfully!');
}

}