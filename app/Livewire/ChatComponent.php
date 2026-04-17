<?php


namespace App\Livewire;
use Livewire\Component;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent; // Isse error chala jayega
use App\Events\MessageRead;

class ChatComponent extends Component
{
    public $receiverId;
    public $messageText = '';

    public function getListeners()
    {
        $authId = Auth::id();
        return [
            "echo-private:chat.{$authId},MessageSent" => 'handleIncomingMessage',
            "echo-private:chat.{$authId},MessageRead" => 'refreshComponent',
        ];
    }

    public function handleIncomingMessage($event)
    {
        // Agar main wahi user hoon jisne chat kholi hui hai aur message mujhe aaya hai
        if ($this->receiverId == $event['message']['sender_id']) {
            $message = Message::find($event['message']['id']);
            if ($message && !$message->read_at) {
                $message->update(['read_at' => now()]);
                
                // TRIGGER: Sender ko batana ki message read ho gaya
                broadcast(new MessageRead(Auth::id(), $this->receiverId))->toOthers();
            }
        }
        $this->refreshComponent();
    }

    public function refreshComponent()
    {
        // Livewire re-render trigger
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

        broadcast(new MessageSent($message))->toOthers();

        $this->messageText = '';
        $this->dispatch('messageSent'); 
    }

    public function render()
    {
        $messages = [];
        $receiver = null;

        if ($this->receiverId) {
            $receiver = User::find($this->receiverId);

            // LOGIC: Check for unread messages sent by the person I'm chatting with
            $unreadQuery = Message::where('sender_id', $this->receiverId)
                ->where('receiver_id', Auth::id())
                ->whereNull('read_at');

            if ($unreadQuery->count() > 0) {
                $unreadQuery->update(['read_at' => now()]);

                // IMPORTANT: Broadcast 'MessageRead' to the SENDER ($this->receiverId)
                // Taaki unki screen par Blue Tick turant aa jaye
                broadcast(new MessageRead(Auth::id(), $this->receiverId))->toOthers();
            }

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