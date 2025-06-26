<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
public function index()
    {
        $schools = School::all()->map(function ($school) {
            $school->orders_count = method_exists($school, 'orders') ? $school->orders()->count() : 0;
            $school->users_count = method_exists($school, 'users') ? $school->users()->count() : 0;
            return $school;
        });

        return view('admin.schools', compact('schools'));
    }


    // public function show(School $school)
    // {
    //     $school->load(['users', 'orders']);
    //     return view('admin.schools.show', compact('school'));
    // }

    // public function create(School $school)
    // {
    //     return view('admin.schools.create', compact('school'));
    // }

    // public function store(Request $request, School $school)
    // {

    //     return redirect()->route('admin.schools.show', $school)->with('success', 'Contract created.');
    // }
}
