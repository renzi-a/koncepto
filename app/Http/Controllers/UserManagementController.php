<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class UserManagementController extends Controller
{
    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cp_no' => 'required|string|max:20',
            'role' => 'required|in:teacher,student',
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

}
