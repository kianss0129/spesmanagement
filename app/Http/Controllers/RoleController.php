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

    public function getJson(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'created_at')
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->pluck('name')->first() ?? null,
                ];
            });

        $roles = Role::pluck('name')->toArray();

        return response()->json([
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
    $currentUser = auth()->user();

    // Only Admin and PESO Admin can remove roles
    if (!$currentUser->hasAnyRole(['Admin', 'PESO Admin'])) {
        return back()->with('error', 'You do not have permission to remove roles.');
    }

    // Prevent removing PESO Admin role
    if ($user->hasRole('PESO Admin')) {
        return back()->with('error', 'Cannot remove PESO Admin role.');
    }

    // Prevent removing your own role
    if ($user->id === $currentUser->id) {
        return back()->with('error', 'You cannot remove your own role.');
    }

    $user->syncRoles([]);

    return back()->with('success', 'Role removed successfully!');
}

    public function destroy(User $user)
{
    $currentUser = auth()->user();

    // Only Admin and PESO Admin can delete users
    if (!$currentUser->hasAnyRole(['Admin', 'PESO Admin'])) {
        return back()->with('error', 'You do not have permission to delete users.');
    }

    // Prevent deleting PESO Admin
    if ($user->hasRole('PESO Admin')) {
        return back()->with('error', 'Cannot delete PESO Admin.');
    }

    // Prevent deleting self
    if ($user->id === $currentUser->id) {
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