<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User; // default User model

Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    } else {
        return response()->json(['success' => false, 'message' => 'Invalid email or password']);
    }
});
