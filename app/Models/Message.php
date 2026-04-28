<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'read_at',
        'delivered_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];
}
