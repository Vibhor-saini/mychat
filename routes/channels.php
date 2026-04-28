<?php
use Illuminate\Support\Facades\Broadcast;

// Global presence channel for Online/Offline and Global Typing
Broadcast::channel('chat-presence', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role
    ];
});

// Private channel for messages and ticks
Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});