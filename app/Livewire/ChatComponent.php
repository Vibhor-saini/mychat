<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;
use App\Events\MessageRead;

class ChatComponent extends Component
{
    #[Url]
    public $receiverId;
    public $messageText = '';
    public $isTyping = false;
    public $onlineUsers = [];

    protected $listeners = ['typing-received' => 'handleWhisperTyping', 'scroll-bottom' => '$refresh', 'online-users-updated' => 'updateOnlineStatus'];

    public function getListeners()
    {
        $authId = Auth::id();
        return array_merge($this->listeners, [
            "echo-private:chat.{$authId},MessageSent" => 'handleIncomingMessage',
            "echo-private:chat.{$authId},MessageRead" => '$refresh',
        ]);
    }

    public function updateOnlineStatus($users) { $this->onlineUsers = $users; }

public function mount($receiverId = null) { 
    $this->receiverId = $receiverId; 
    
    // Sidebar ko active user id bhejo taaki badge na dikhe
    $this->dispatch('update-receiver', id: $receiverId)->to(ChatSidebar::class);

    $this->markAsDelivered();
    $this->markAsRead(); 
}

    public function markAsDelivered() {
        if ($this->receiverId) {
            Message::where('sender_id', $this->receiverId)->where('receiver_id', Auth::id())->whereNull('delivered_at')->update(['delivered_at' => now()]);
        }
    }

    public function markAsRead() {
        if (!$this->receiverId) return;
        $unread = Message::where('sender_id', $this->receiverId)->where('receiver_id', Auth::id())->whereNull('read_at');
        if ($unread->exists()) {
            $unread->update(['read_at' => now(), 'delivered_at' => now()]);
            broadcast(new MessageRead(Auth::id(), $this->receiverId))->toOthers();
            $this->dispatch('refreshSidebar')->to(ChatSidebar::class);
        }
    }

    public function handleIncomingMessage($event) {
        if ($this->receiverId == $event['message']['sender_id']) { $this->markAsRead(); }
        $this->dispatch('refreshSidebar')->to(ChatSidebar::class);
        $this->dispatch('scroll-bottom');
    }

    public function handleWhisperTyping($data) {
        $payload = $data['data'] ?? $data;
        if ($payload['receiver_id'] == Auth::id() && $payload['sender_id'] == $this->receiverId) {
            $this->isTyping = $payload['typing'];
        }
    }

    // public function sendMessage() {
    //     if (empty(trim($this->messageText))) return;
    //     $authId = Auth::id();
    //     $data = ['sender_id' => $authId, 'receiver_id' => $this->receiverId, 'message' => $this->messageText];
        
    //     if ($authId == $this->receiverId) { $data['delivered_at'] = now(); $data['read_at'] = now(); }
    //     elseif (in_array($this->receiverId, $this->onlineUsers)) { $data['delivered_at'] = now(); }

    //     $message = Message::create($data);
    //     broadcast(new MessageSent($message))->toOthers();
    //     $this->messageText = '';
    //     $this->isTyping = false;    
    //     $this->dispatch('scroll-bottom');
    //     $this->dispatch('refreshSidebar')->to(ChatSidebar::class);
    // }

    public function sendMessage() {
    if (empty(trim($this->messageText))) return;

    $authId = Auth::id();
    
    // 1. Text ko variable mein lo aur input ko TURANT khali karo
    // Isse user ko lagega message chala gaya, backend piche chalta rahega
    $text = $this->messageText;
    $this->messageText = ''; 
    $this->isTyping = false;

    // 2. Data prepare karo
    $data = [
        'sender_id' => $authId, 
        'receiver_id' => $this->receiverId, 
        'message' => $text // variable use karo
    ];
    
    // Delivered/Read logic
    if ($authId == $this->receiverId) { 
        $data['delivered_at'] = now(); 
        $data['read_at'] = now(); 
    } elseif (in_array($this->receiverId, $this->onlineUsers)) { 
        $data['delivered_at'] = now(); 
    }

    // 3. Database operation
    $message = Message::create($data);

    // 4. Broadcast (Ensure MessageSent implements ShouldBroadcastNow)
    broadcast(new MessageSent($message))->toOthers();
    
    // 5. UI Updates
    $this->dispatch('msg-sent');
    $this->dispatch('scroll-bottom');
    $this->dispatch('refreshSidebar')->to(ChatSidebar::class);
}

    public function render() {
        $messages = [];
        $receiver = $this->receiverId ? User::find($this->receiverId) : null;
        if ($receiver) {
            $messages = Message::where(fn($q) => $q->where('sender_id', Auth::id())->where('receiver_id', $this->receiverId))
                ->orWhere(fn($q) => $q->where('sender_id', $this->receiverId)->where('receiver_id', Auth::id()))
                ->orderBy('created_at', 'asc')->get();
        }
        return view('livewire.chat-component', compact('messages', 'receiver'));
    }
}