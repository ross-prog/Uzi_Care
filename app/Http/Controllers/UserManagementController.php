<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'employee_id', 'role', 'department', 'contact_number', 'is_active', 'last_login_at', 'created_by', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);

        return Inertia::render('UserManagement/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return Inertia::render('UserManagement/Create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'employee_id' => 'required|string|max:50|unique:users',
            'role' => 'required|in:admin,nurse,inventory_manager,account_manager',
            'department' => 'required|string|max:255',
            'campus' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'employee_id' => $request->employee_id,
            'role' => $request->role,
            'department' => $request->department,
            'campus' => $request->campus,
            'contact_number' => $request->contact_number,
            'password' => Hash::make($request->password),
            'is_active' => true,
            'created_by' => Auth::user()->email,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing a user
     */
    public function edit(User $user)
    {
        return Inertia::render('UserManagement/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,nurse,inventory_manager,account_manager',
            'department' => 'required|string|max:255',
            'campus' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'employee_id' => $request->employee_id,
            'role' => $request->role,
            'department' => $request->department,
            'campus' => $request->campus,
            'contact_number' => $request->contact_number,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password reset successfully.');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully.");
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        // Prevent deletion of the last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Cannot delete the last active admin user.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
