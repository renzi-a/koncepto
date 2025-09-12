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

public function update(Request $request, $id)
{
    $school = School::findOrFail($id);
    $admin  = $school->school_admin;

    $validatedSchool = $request->validate([
        'school_name'   => 'required|string|max:255',
        'address'       => 'required|string|max:255',
        'school_email'  => 'required|email',
        'principal'     => 'nullable|string|max:255',
        'logo'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $validatedAdmin = $request->validate([
        'admin_first_name'            => 'required|string|max:255',
        'admin_last_name'             => 'required|string|max:255',
        'admin_email'                 => ['required','email',Rule::unique('users','email')->ignore($admin?->id)],
        'admin_contact'               => 'required|string|max:20',
        'admin_password'              => 'nullable|string|min:8|confirmed',
    ]);

    $school->update($validatedSchool);

    if ($request->hasFile('logo')) {
        $path = $request->file('logo')->store('schools', 'public');
        $school->image = $path;
        $school->save();
    }

    if ($admin) {
        $admin->first_name = $validatedAdmin['admin_first_name'];
        $admin->last_name  = $validatedAdmin['admin_last_name'];
        $admin->email      = $validatedAdmin['admin_email'];
        $admin->cp_no      = $validatedAdmin['admin_contact'];
        if (!empty($validatedAdmin['admin_password'])) {
            $admin->password = Hash::make($validatedAdmin['admin_password']);
        }
        $admin->save();
    }

if ($request->ajax()) {
    return response()->json([
        'message'  => 'School and admin updated successfully!',
        'redirect' => route('admin.schools.index'), 
    ]);
}

return redirect()->route('admin.schools.index') 
    ->with('success', 'School and admin updated successfully!');

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
