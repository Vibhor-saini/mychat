<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent; // <--- Event import karein

class ChatComponent extends Component
{
    public $receiverId;
    public $messageText = '';

    // Ye function batata hai ki Livewire ko kaunsa WebSocket channel sunna hai
    public function getListeners()
    {
        $authId = Auth::id();
        return [
            // Jab mere ID wale private channel par msg aaye, toh refreshComponent chalao
            "echo-private:chat.{$authId},MessageSent" => 'refreshComponent',
        ];
    }

    public function refreshComponent()
    {
        // Ye function khali rahega, iska call hona hi UI ko refresh kar dega
    }

    public function mount($receiverId = null)
    {
        $this->receiverId = $receiverId;
    }

    public function sendMessage()
    {
        if (empty(trim($this->messageText))) return;

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $this->receiverId,
            'message' => $this->messageText,
        ]);

        // --- ASLI MAGIC YAHAN HAI ---
        // Dusre user ko signal bhej rahe hain ki naya msg aaya hai
        broadcast(new MessageSent($message))->toOthers();

        $this->messageText = ''; 
        $this->dispatch('messageSent'); // Scroll down karne ke liye JS event
    }

    public function render()
    {
        $messages = [];
        $receiver = null;

        if ($this->receiverId) {
            $receiver = User::find($this->receiverId);
            $messages = Message::where(function ($q) {
                $q->where('sender_id', Auth::id())->where('receiver_id', $this->receiverId);
            })->orWhere(function ($q) {
                $q->where('sender_id', $this->receiverId)->where('receiver_id', Auth::id());
            })->orderBy('created_at', 'asc')->get();
        }

        return view('livewire.chat-component', [
            'messages' => $messages,
            'receiver' => $receiver
        ]);
    }
}