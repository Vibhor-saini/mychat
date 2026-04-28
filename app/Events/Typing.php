<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class Typing implements ShouldBroadcastNow
{
    use SerializesModels;

    public $sender_id;
    public $receiver_id;
    public $typing;

    public function __construct($sender_id, $receiver_id, $typing)
    {
        $this->sender_id = $sender_id;
        $this->receiver_id = $receiver_id;
        $this->typing = $typing;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('chat-presence');
    }

    public function broadcastAs()
    {
        return 'typing';
    }
}