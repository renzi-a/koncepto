<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class AdminChatApiController extends Controller
{

    public function index()
    {
        
        $adminId = Auth::id(); 

        $users = User::where('role', 'school_admin')
            ->with('school:id,school_name,image') 
            ->get();

        $users = $users->map(function ($user) use ($adminId) {

            if (empty($user->id)) {
                
                return null; 
            }

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
        })->filter(); 
        return response()->json($users); 
    }

    
    public function fetchMessages($userId)
    {
        $admin = Auth::user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $user = User::findOrFail($userId);


        Message::where('sender_id', $user->id)
            ->where('receiver_id', $admin->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where(function ($q) use ($admin, $user) {
            $q->where('sender_id', $admin->id)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($admin, $user) {
            $q->where('sender_id', $user->id)->where('receiver_id', $admin->id);
        })
        ->with('sender:id,first_name,last_name') 
        ->orderBy('created_at')
        ->get([
            'id', 'sender_id', 'receiver_id', 'message',
            'attachment', 'original_name', 'created_at' 
        ]);

        return response()->json($messages); 
    }

 
    public function send(Request $request, $receiverId)
    {
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|max:10240', 
        ]);

        if (!$request->message && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Please enter a message or upload an attachment.'], 400);
        }

        $senderId = Auth::id();
        if (!$senderId) {
            return response()->json(['error' => 'Unauthenticated sender.'], 401);
        }

        $data = [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $request->message,
            'is_read' => false,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('chat_attachments', 'public'); // Stores in storage/app/public/chat_attachments
            $data['attachment'] = $path;
            $data['original_name'] = $file->getClientOriginalName();
        }

        $message = Message::create($data);

        Notification::create([
            'user_id' => $receiverId,
            'message' => 'New chat message from ' . (Auth::user()->name ?? 'Admin'),
            'is_read' => false,
        ]);

        return response()->json(['status' => 'Message sent successfully', 'message' => $message]);
    }


    public function typing(Request $request, $userId)
    {
        $adminId = Auth::id();
        if (!$adminId) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        $key = "typing_admin_{$adminId}_to_user_{$userId}";
        Cache::put($key, true, now()->addSeconds(3)); // Cache for 3 seconds
        return response()->json(['status' => 'ok']);
    }

    public function checkTyping($userId)
    {
        $adminId = Auth::id();
        if (!$adminId) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        $key = "typing_user_{$userId}_to_{$adminId}"; 
        $isTyping = Cache::has($key);
        return response()->json(['typing' => $isTyping]);
    }
}