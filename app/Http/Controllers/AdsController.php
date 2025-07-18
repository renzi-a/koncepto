<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdsController extends Controller
{
     public function index()
    {
        return view('admin.ads');
    }

    public function create()
    {
        return view('admin.ads-create');
    }
}
