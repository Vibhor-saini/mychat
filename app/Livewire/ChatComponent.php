<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;
use App\Events\MessageRead;
use App\Events\Typing;

class ChatComponent extends Component
{
    #[Url]
    public $receiverId;
    public $messageText = '';
    public $isTyping = false;

    public function getListeners()
    {
        $authId = Auth::id();
        return [
            "echo-private:chat.{$authId},MessageSent" => 'handleIncomingMessage',
            "echo-private:chat.{$authId},MessageRead" => '$refresh',
            "echo-presence:chat-presence,typing" => 'handleTypingIndicator',
        ];
    }

    public function mount($receiverId = null)
    {
        $this->receiverId = $receiverId;
        $this->markAsRead();
    }
    public function updatedReceiverId()
    {
        $this->markAsRead();
    }

    public function markAsRead()
    {
        if (!$this->receiverId) return;
        $unread = Message::where('sender_id', $this->receiverId)->where('receiver_id', Auth::id())->whereNull('read_at');
        if ($unread->exists()) {
            $unread->update(['read_at' => now()]);
            broadcast(new MessageRead(Auth::id(), $this->receiverId))->toOthers();
            $this->dispatch('refreshSidebar')->to(\App\Livewire\ChatSidebar::class);
        }
    }

    public function handleIncomingMessage($event)
    {
        if ($this->receiverId == $event['message']['sender_id']) {
            $this->markAsRead();
        } else {
            $this->dispatch('refreshSidebar')->to(\App\Livewire\ChatSidebar::class);
        }
        $this->dispatch('$refresh');
        $this->dispatch('scroll-bottom');

        $this->dispatch('refreshSidebar'); // Global browser event
        $this->dispatch('$refresh');       // Local chat refresh

    }

    public function updatedMessageText()
    {
        if (!$this->receiverId) return;
        broadcast(new Typing(Auth::id(), $this->receiverId, !empty($this->messageText)))->toOthers();
    }

    public function handleTypingIndicator($event)
    {
        if ($event['receiver_id'] == Auth::id() && $event['sender_id'] == $this->receiverId) {
            $this->isTyping = (bool) $event['typing'];
        }
    }

    public function sendMessage()
    {
        if (empty(trim($this->messageText))) return;
        $message = Message::create(['sender_id' => Auth::id(), 'receiver_id' => $this->receiverId, 'message' => $this->messageText]);
        broadcast(new MessageSent($message))->toOthers();
        broadcast(new Typing(Auth::id(), $this->receiverId, false))->toOthers();
        $this->messageText = '';
        $this->dispatch('$refresh');
        $this->dispatch('refreshSidebar')->to(\App\Livewire\ChatSidebar::class);
        $this->dispatch('scroll-bottom');

        $this->dispatch('refreshSidebar'); // Global browser event
        $this->dispatch('$refresh');       // Local chat refresh
    }

    public function render()
    {
        $messages = [];
        $receiver = null;
        if ($this->receiverId) {
            $receiver = User::find($this->receiverId);
            $messages = Message::where(function ($q) {
                $q->where('sender_id', Auth::id())->where('receiver_id', $this->receiverId);
            })
                ->orWhere(function ($q) {
                    $q->where('sender_id', $this->receiverId)->where('receiver_id', Auth::id());
                })
                ->orderBy('created_at', 'asc')->get();
        }
        return view('livewire.chat-component', compact('messages', 'receiver'));
    }
}
