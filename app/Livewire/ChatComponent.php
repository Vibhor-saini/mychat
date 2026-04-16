<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatComponent extends Component
{
    public $receiverId;
    public $messageText = '';

    // Dashboard se receiverId lene ke liye
    public function mount($receiverId = null)
    {
        $this->receiverId = $receiverId;
    }

    // Message bhejte hi ye function chalega
    public function sendMessage()
    {
        if (empty(trim($this->messageText))) return;

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $this->receiverId,
            'message' => $this->messageText,
        ]);

        $this->messageText = ''; // Input box khali kar do
        $this->dispatch('messageSent');
    }

    public function render()
    {
        $messages = [];
        $receiver = null;

        if ($this->receiverId) {
            $receiver = User::find($this->receiverId);
            $messages = Message::where(function($q) {
                $q->where('sender_id', Auth::id())->where('receiver_id', $this->receiverId);
            })->orWhere(function($q) {
                $q->where('sender_id', $this->receiverId)->where('receiver_id', Auth::id());
            })->orderBy('created_at', 'asc')->get();
        }

        return view('livewire.chat-component', [
            'messages' => $messages,
            'receiver' => $receiver
        ]);
    }
}