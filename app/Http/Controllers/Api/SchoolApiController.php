<?php

namespace App\Http\Controllers\Api; 

use App\Http\Controllers\Controller; 
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

class SchoolApiController extends Controller
{
    /**
     * Display a listing of the schools for API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $schools = School::with('users')->get()->map(function ($school) {
            $school->orders_count = $school->orders()->count();
            $school->users_count = $school->users()->count();
            $school->admin_count = $school->users()->where('role', 'school_admin')->count();
            $school->teacher_count = $school->users()->where('role', 'teacher')->count();
            $school->student_count = $school->users()->where('role', 'student')->count();

            $school->id = (int) $school->id;

            return $school;
        });

        return response()->json($schools);
    }

    
}