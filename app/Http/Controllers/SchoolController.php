<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::with('users')->get()->map(function ($school) {
            $school->orders_count = $school->orders()->count();
            
            $school->users_count = $school->users()->count();
            $school->admin_count = $school->users()->where('role', 'school_admin')->count();
            $school->teacher_count = $school->users()->where('role', 'teacher')->count();
            $school->student_count = $school->users()->where('role', 'student')->count();

            return $school;
        });

        return view('admin.schools.index', compact('schools'));
    }


    public function create()
    {
        return view('admin.schools.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'school_email' => 'required|email|unique:schools,school_email',
            'logo' => 'nullable|image|max:2048',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',

            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_contact' => 'required|string|max:20',
            'admin_role' => 'required|in:school_admin',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'first_name' => $validated['admin_first_name'],
            'last_name' => $validated['admin_last_name'],
            'email' => $validated['admin_email'],
            'cp_no' => $validated['admin_contact'],
            'role' => $validated['admin_role'],
            'password' => Hash::make($validated['admin_password']),
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $school = School::create([
                'school_name' => $validated['school_name'],
                'address' => $validated['address'],
                'school_email' => $validated['school_email'],
                'image' => $logoPath,
                'user_id' => $user->id,
                'lat' => $request->lat,
                'lng' => $request->lng,
            ]);

        $user->school_id = $school->id;
        $user->save();

        return redirect()->route('admin.schools.show', $school)->with('success', 'School and Admin created.');
    }

public function show(School $school)
{
    $school->load(['user', 'orders', 'users']);
    $users = $school->users;
    $activeUser = $school->user;

    return view('admin.schools.show', compact('school', 'users', 'activeUser'));
}




    public function edit(School $school)
    {
        $admin = $school->users()->where('role', 'school_admin')->first();
        return view('admin.schools.edit', compact('school', 'admin'));
    }


    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'school_email' => 'required|email|unique:schools,school_email,' . $school->id,
            'logo' => 'nullable|image|max:2048',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',

            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email,' . $school->user_id,
            'admin_contact' => 'required|string|max:20',
            'admin_role' => 'required|in:school_admin',
            'admin_password' => 'nullable|string|min:8|confirmed',
        ]);

        $user = $school->school_admin;
        $user->update([
            'first_name' => $validated['admin_first_name'],
            'last_name' => $validated['admin_last_name'],
            'email' => $validated['admin_email'],
            'cp_no' => $validated['admin_contact'],
            'role' => $validated['admin_role'],
            'password' => $validated['admin_password'] ? Hash::make($validated['admin_password']) : $user->password,
        ]);

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $school->image = $logoPath;
        }


        $school->update([
            'school_name' => $validated['school_name'],
            'address' => $validated['address'],
            'school_email' => $validated['school_email'],
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);

        return redirect()->route('admin.schools.index')->with('success', 'School and Admin updated successfully.');
    }
    public function destroy(School $school)
    {
        $school->delete();
        return redirect()->route('admin.schools.index')->with('success', 'School deleted.');
    }

}
