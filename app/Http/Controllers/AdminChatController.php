<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminChatController extends Controller
{
    public function index()
    {
        $adminId = Auth::id();

        $users = User::where('role', 'school_admin')
            ->with('school')
            ->get()
            ->map(function ($user) use ($adminId) {
                $user->unread_count = Message::where('sender_id', $user->id)
                    ->where('receiver_id', $adminId)
                    ->where('is_read', false)
                    ->count();

                $lastMessage = Message::where(function ($q) use ($user, $adminId) {
                    $q->where('sender_id', $user->id)
                        ->where('receiver_id', $adminId);
                })->orWhere(function ($q) use ($user, $adminId) {
                    $q->where('sender_id', $adminId)
                        ->where('receiver_id', $user->id);
                })->latest()->first();

                $user->last_message = $lastMessage?->message ?? null;

                return $user;
            });

        return view('admin.admin_chat', [
            'users' => $users,
            'messages' => [],
            'activeUser' => null,
        ]);
    }

    public function show($userId)
    {
        $adminId = Auth::id();
        $activeUser = User::findOrFail($userId);

        $messages = Message::where(function ($q) use ($adminId, $userId) {
            $q->where('sender_id', $adminId)->where('receiver_id', $userId);
        })->orWhere(function ($q) use ($adminId, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $adminId);
        })
        ->orderBy('created_at')
        ->get();

        Message::where('sender_id', $userId)
            ->where('receiver_id', $adminId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // 🧠 Refresh the list of users with unread counts
        $users = $this->index()->getData()['users'];

        return view('admin.admin_chat', [
            'users' => $users,
            'messages' => $messages,
            'activeUser' => $activeUser,
        ]);
    }

    public function send(Request $request, $receiverId)
    {
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if (!$request->message && !$request->hasFile('attachment')) {
            return back()->withErrors(['message' => 'Please enter a message or upload an attachment.']);
        }

        $data = [
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
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

        Notification::create([
            'user_id' => $receiverId,
            'message' => 'New chat message from ' . (Auth::user()->name ?? 'Admin'),
            'is_read' => false,
        ]);

        return redirect()->route('admin.chat.show', $receiverId);
    }

    public function fetchMessages($userId)
    {
        $admin = Auth::user();
        $user = User::findOrFail($userId);

        // Mark as read
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $admin->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where(function ($q) use ($admin, $user) {
            $q->where('sender_id', $admin->id)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($admin, $user) {
            $q->where('sender_id', $user->id)->where('receiver_id', $admin->id);
        })
        ->with('sender')
        ->orderBy('created_at')
        ->get([
            'id', 'sender_id', 'receiver_id', 'message',
            'attachment', 'original_name', 'created_at'
        ]);

        return response()->json($messages);
    }

    public function typing(Request $request, $userId)
    {
        $adminId = Auth::id();
        $key = "typing_admin_{$adminId}_to_user_{$userId}";
        Cache::put($key, true, now()->addSeconds(3));
        return response()->json(['status' => 'ok']);
    }

    public function checkTyping($userId)
    {
        $adminId = Auth::id();
        $key = "typing_user_{$userId}_to_{$adminId}";
        $isTyping = Cache::has($key);
        return response()->json(['typing' => $isTyping]);
    }
}
