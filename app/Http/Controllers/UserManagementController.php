<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cp_no' => 'required|string|max:20',
            'role' => 'required|in:school_admin,teacher,student',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = new User($validated);
        $user->password = Hash::make($validated['password']);
        $user->school_id = $school->id;
        $user->save();

        return redirect()->route('admin.schools.show', $school->id)->with('success', 'User created successfully.');
    }

    public function create(School $school)
    {
        return view('admin.schools.user.create', compact('school'));
    }

    public function edit(User $user)
    {
        return view('admin.schools.user.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'cp_no' => 'required|string|max:20',
            'role' => 'required|in:school_admin,teacher,student',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->email = $validated['email'];
        $user->cp_no = $validated['cp_no'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.schools.show', $user->school_id)
            ->with('success', 'User updated successfully.');
    }

    
public function all(Request $request)
{
    $query = User::with('school')->whereIn('role', ['school_admin', 'teacher', 'student']);

    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }

    if ($request->filled('school_id')) {
        $query->where('school_id', $request->school_id);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    $users = $query->latest()->paginate(10);
    $schools = School::orderBy('school_name')->get();

    if ($request->ajax()) {
        return response()->json([
            'html' => view('components.user_table', compact('users'))->render()
        ]);
    }

    return view('admin.user', compact('users', 'schools'));
}

public function destroy(User $user)
{
    $user->delete();

    return redirect()->back()->with('success', 'User deleted successfully.');
}

}
