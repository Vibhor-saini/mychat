<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatSidebar extends Component
{
    public $receiverId;
    public $typingUsers = [];
    public $onlineUsers = [];

    protected $listeners = [
        'refreshSidebar' => '$refresh', 
    ];

    public function getListeners()
    {
        $authId = Auth::id();
        return [
            // Jab naya message aaye (Private Channel)
            "echo-private:chat.{$authId},MessageSent" => 'refreshSidebar',
            "echo-private:chat.{$authId},MessageRead" => 'refreshSidebar',

            // Presence Channel (Typing & Online)
            "echo-presence:chat-presence,typing" => 'handleTyping',
            "echo-presence:chat-presence,here" => 'setOnlineUsers',
            "echo-presence:chat-presence,joining" => 'userJoined',
            "echo-presence:chat-presence,leaving" => 'userLeft',

            // Listener for manual dispatch from ChatComponent
            'refreshSidebar' => '$refresh',
        ];
    }

    public function handleTyping($event)
    {
// Receiver validation
    if ($event['receiver_id'] == Auth::id()) {
        $senderId = $event['sender_id'];
        
        if ($event['typing']) {
            $this->typingUsers[$senderId] = true;
        } else {
            unset($this->typingUsers[$senderId]);
        }
    }
    }

    public function setOnlineUsers($users)
    {
        $this->onlineUsers = collect($users)->pluck('id')->toArray();
        $this->dispatch('online-users-updated', users: $this->onlineUsers);
    }

    public function userJoined($user)
    {
        if (!in_array($user['id'], $this->onlineUsers)) {
            $this->onlineUsers[] = $user['id'];
            $this->dispatch('online-users-updated', users: $this->onlineUsers); // Header fix
        }
    }

    public function userLeft($user)
    {
        $this->onlineUsers = array_diff($this->onlineUsers, [$user['id']]);
        $this->dispatch('online-users-updated', users: $this->onlineUsers);
    }
    public function refreshSidebar()
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        $authId = Auth::id();

        // Subquery for professional sorting (Latest message top par)
        $recentIds = Message::where('sender_id', $authId)->orWhere('receiver_id', $authId)
            ->selectRaw("CASE WHEN sender_id = $authId THEN receiver_id ELSE sender_id END as contact_id")
            ->selectRaw("MAX(created_at) as last_chat")
            ->groupBy('contact_id')->orderBy('last_chat', 'desc')->pluck('contact_id')->toArray();

        $query = User::query()->orderByRaw('id = ? DESC', [$authId]);
        if (!empty($recentIds)) {
            $idsString = implode(',', $recentIds);
            $query->orderByRaw("FIELD(id, $idsString) DESC");
        }

        $sidebarUsers = $query->orderBy('name', 'asc')->get()->map(function ($user) use ($authId) {
            $user->latest_msg = Message::where(function ($q) use ($authId, $user) {
                $q->where('sender_id', $authId)->where('receiver_id', $user->id);
            })->orWhere(function ($q) use ($authId, $user) {
                $q->where('sender_id', $user->id)->where('receiver_id', $authId);
            })->latest()->first();

            $user->unread_count = ($this->receiverId == $user->id) ? 0 : Message::where('sender_id', $user->id)
                ->where('receiver_id', $authId)
                ->whereNull('read_at')
                ->count();
            return $user;
        });

        return view('livewire.chat-sidebar', ['users' => $sidebarUsers]);
    }
}
