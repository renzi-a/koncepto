<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $admin = User::where('role', 'admin')->first();

        $admin->unread_count = Message::where('sender_id', $admin->id)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        $lastMessage = Message::where(function ($q) use ($admin, $userId) {
            $q->where('sender_id', $admin->id)->where('receiver_id', $userId);
        })->orWhere(function ($q) use ($admin, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $admin->id);
        })->latest()->first();

        $admin->last_message = $lastMessage?->message;

        return view('user.chat', [
            'admin' => $admin,
            'messages' => [],
            'activeAdmin' => null,
        ]);
    }

    public function show($adminId)
    {
        $userId = Auth::id();
        $admin = User::findOrFail($adminId);

        $messages = Message::where(function ($q) use ($userId, $adminId) {
            $q->where('sender_id', $userId)->where('receiver_id', $adminId);
        })->orWhere(function ($q) use ($userId, $adminId) {
            $q->where('sender_id', $adminId)->where('receiver_id', $userId);
        })->orderBy('created_at')->get();

        Message::where('sender_id', $adminId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('user.chat', [
            'admin' => $admin,
            'messages' => $messages,
            'activeAdmin' => $admin,
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if (!$request->message && !$request->hasFile('attachment')) {
            return back()->withErrors(['message' => 'Please enter a message or upload an attachment.']);
        }

        $admin = User::where('role', 'admin')->first();

        $data = [
            'sender_id' => Auth::id(),
            'receiver_id' => $admin->id,
            'message' => $request->message,
            'is_read' => false,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('chat_attachments', 'public');

            $data['attachment'] = $path;
            $data['original_name'] = $file->getClientOriginalName();
        }


        Message::create($data);

        return response('', 204);
    }

    public function popup()
    {
        $user = Auth::user();
        $admin = User::where('role', 'admin')->first();

        $messages = Message::where(function ($q) use ($user, $admin) {
            $q->where('sender_id', $user->id)->where('receiver_id', $admin->id);
        })->orWhere(function ($q) use ($user, $admin) {
            $q->where('sender_id', $admin->id)->where('receiver_id', $user->id);
        })->with('sender')->latest()->limit(20)->get()->reverse();

        return view('user.chat-popup', compact('messages', 'admin'));
    }

    public function full()
    {
        $user = Auth::user();
        $admin = User::where('role', 'admin')->first();

        $messages = Message::where(function ($query) use ($user, $admin) {
            $query->where('sender_id', $user->id)->where('receiver_id', $admin->id);
        })->orWhere(function ($query) use ($user, $admin) {
            $query->where('sender_id', $admin->id)->where('receiver_id', $user->id);
        })->with('sender')->latest()->limit(50)->get()->reverse();

        return view('user.chat', compact('messages', 'admin'));
    }

public function fetchMessages()
    {
        $user = auth::user();
        $admin = User::where('role', 'admin')->first();

        $messages = Message::where(function ($q) use ($user, $admin) {
            $q->where('sender_id', $user->id)->where('receiver_id', $admin->id);
        })->orWhere(function ($q) use ($user, $admin) {
            $q->where('sender_id', $admin->id)->where('receiver_id', $user->id);
        })->with('sender')->orderBy('created_at')->get();

        return response()->json($messages);
    }

public function typing(Request $request)
{
    $userId = Auth::id();
    $admin = User::where('role', 'admin')->first();

    $key = "typing_user_{$userId}_to_{$admin->id}";
    Cache::put($key, true, now()->addSeconds(3));

    return response()->json(['status' => 'ok']);
}



public function checkTyping()
{
    $userId = Auth::id();
    $admin = User::where('role', 'admin')->first();

    if (!$admin) return response()->json(['typing' => false]);

    $key = "typing_admin_{$admin->id}_to_user_{$userId}";
    $isTyping = Cache::has($key);

    return response()->json(['typing' => $isTyping]);
}


}
