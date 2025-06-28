<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ChatController extends Controller
{
    public function chat($userId = null)
    {
        $users = User::where('role', 'school_admin')->with('school')->get();

        $activeUser = null;
        $messages = collect();

        if ($userId) {
            $activeUser = $users->firstWhere('id', $userId);
            $messages = $activeUser
                ? $activeUser->messages()->with('sender')->latest()->get()->reverse()
                : collect();
        }

        return view('admin.admin_chat', compact('users', 'activeUser', 'messages'));
    }
}
