<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::with('users')->get()->map(function ($school) {
            $regularOrders = $school->orders()->count();
            $customOrder = $school->customOrder()->count();

            $school->orders_count = $regularOrders + $customOrder;

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
            'principal' => 'required|string|max:255',

            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_contact' => 'required|string|max:20',
            'admin_role' => 'required|in:school_admin',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $admin = User::create([
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
            'user_id' => $admin->id,
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'principal' => $validated['principal'],
        ]);

        $admin->school_id = $school->id;
        $admin->save();

        return redirect()->route('admin.schools.show', $school)->with('success', 'School and Admin created successfully.');
    }

public function show(School $school)
{
    $school->load('user');
    $admin = $school->user;

    return view('admin.schools.show', compact('school', 'admin'));
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
        'principal' => 'required|string|max:255',
        'admin_first_name' => 'required|string|max:255',
        'admin_last_name' => 'required|string|max:255',
        'admin_email' => 'required|email|unique:users,email,' . $school->user_id,
        'admin_contact' => 'required|string|max:20',
        'admin_role' => 'required|in:school_admin',
        'admin_password' => 'nullable|string|min:8|confirmed',
    ]);

    $admin = $school->users()->where('role', 'school_admin')->first();
    $admin->first_name = $validated['admin_first_name'];
    $admin->last_name = $validated['admin_last_name'];
    $admin->email = $validated['admin_email'];
    $admin->cp_no = $validated['admin_contact'];
    $admin->role = $validated['admin_role'];
    if (!empty($validated['admin_password'])) {
        $admin->password = Hash::make($validated['admin_password']);
    }
    $admin->save();

    if ($request->hasFile('logo')) {
        if ($school->image) {
            Storage::disk('public')->delete($school->image);
        }
        $logoPath = $request->file('logo')->store('logos', 'public');
        $school->image = $logoPath;
    }

    $school->update([
        'school_name' => $validated['school_name'],
        'address' => $validated['address'],
        'school_email' => $validated['school_email'],
        'principal' => $validated['principal'],
    ]);

    return response()->json(['message' => 'School and Admin updated successfully.']);
}

    public function destroy(School $school)
    {
        $school->delete();
        return redirect()->route('admin.schools.index')->with('success', 'School deleted.');
    }
}