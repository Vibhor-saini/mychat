<?php


namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent; // Isse error chala jayega
use App\Events\MessageRead;

class ChatComponent extends Component
{
    #[Url]
    public $receiverId;
    public $messageText = '';
    public $onlineUsers = [];
    public $isTyping = false;

    /**
     * Updated Listeners:
     * We map the presence channel events to specific PHP methods.
     */
    public function getListeners()
    {
        $authId = Auth::id();
        return [
            "echo-private:chat.{$authId},MessageSent" => 'handleIncomingMessage',
            "echo-private:chat.{$authId},MessageRead" => 'refreshComponent',

            // Presence Channel Events
            "echo-presence:chat-presence,here" => 'initOnlineUsers',
            "echo-presence:chat-presence,joining" => 'userJoined',
            "echo-presence:chat-presence,leaving" => 'userLeft',
            "echo-presence:chat-presence,typing" => 'handleTypingIndicator',
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

        // NEW: Update sidebar when someone sends ME a message
        $this->dispatch(
            'update-sidebar-text',
            userId: $event['message']['sender_id'],
            message: $event['message']['message'],
            isMe: false,
            time: now()->format('h:i A')
        );

        $this->refreshComponent();
        $this->dispatch('refresh-sidebar');
    }

    public function handleTypingIndicator($event)
    {
        // Check if the typing event is for me
        if ($event['receiver_id'] == Auth::id()) {
            // If it's the currently open chat window
            if ($event['sender_id'] == $this->receiverId) {
                $this->isTyping = $event['typing'];
            }

            // Always dispatch to browser for Sidebar update
            $this->dispatch(
                'typing-received',
                senderId: $event['sender_id'],
                typing: $event['typing']
            );
        }
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

        if (Auth::id() != $this->receiverId) {
            broadcast(new MessageSent($message))->toOthers();
        }

        // Reset Typing locally and on Receiver's end instantly
        $this->isTyping = false;
        $this->dispatch('typing-received', senderId: Auth::id(), typing: false);
        // NEW: Dispatch event to JS for sidebar update
        $this->dispatch(
            'update-sidebar-text',
            userId: $this->receiverId,
            message: $this->messageText,
            isMe: true,
            time: now()->format('h:i A')
        );

        $this->messageText = '';
        $this->dispatch('messageSent');
        $this->dispatch('refresh-sidebar');
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
                $this->dispatch('refresh-sidebar');

                // IMPORTANT: Broadcast 'MessageRead' to the SENDER ($this->receiverId)
                // Taaki unki screen par Blue Tick turant aa jaye
                broadcast(new MessageRead(Auth::id(), $this->receiverId))->toOthers();
            }

            $messages = Message::where(function ($q) {
                $q->where('sender_id', Auth::id())->where('receiver_id', $this->receiverId);
            })->orWhere(function ($q) {
                $q->where('sender_id', $this->receiverId)->where('receiver_id', Auth::id());
            })->orderBy('created_at', 'asc')->get();

            if (Auth::id() == $this->receiverId) {
                $messages = Message::where('sender_id', Auth::id())
                    ->where('receiver_id', Auth::id())
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        }

        return view('livewire.chat-component', [
            'messages' => $messages,
            'receiver' => $receiver
        ]);
    }


    /**
     * Triggered when you first load the chat.
     * It receives an array of all users currently online.
     */
    public function initOnlineUsers($users)
    {
        $this->onlineUsers = collect($users)->pluck('id')->toArray();
        // Notify the frontend bridge immediately
        $this->dispatch('online-users-updated', users: $this->onlineUsers);
    }

    /**
     * Triggered when a new user opens their browser/app.
     */
    public function userJoined($user)
    {
        if (!in_array($user['id'], $this->onlineUsers)) {
            $this->onlineUsers[] = $user['id'];
            $this->dispatch('online-users-updated', users: $this->onlineUsers);
        }
    }

    /**
     * Triggered when a user closes their tab or logs out.
     */
    public function userLeft($user)
    {
        $this->onlineUsers = array_diff($this->onlineUsers, [$user['id']]);
        $this->dispatch('online-users-updated', users: $this->onlineUsers);
    }
}
