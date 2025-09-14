<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('admin.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id && $user->role === 'admin';
});
