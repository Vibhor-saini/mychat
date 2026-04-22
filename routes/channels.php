<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Presence Channel: chat-presence
 * Used to track the online/offline status of all users.
 * Returns the user's ID and Name to all other connected clients.
 */
Broadcast::channel('chat-presence', function ($user) {
    if (auth()->check()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }
});