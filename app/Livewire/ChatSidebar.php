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

    // Sidebar Component PHP mein dalo
    protected $listeners = ['status-updated' => '$refresh', 'profile-updated' => '$refresh'];


    public function getListeners()
    {
        $authId = Auth::id();
        return [
            "echo-private:chat.{$authId},MessageSent" => 'refreshSidebar',
            "echo-private:chat.{$authId},MessageRead" => 'refreshSidebar',
            "echo-presence:chat-presence,here" => 'setOnlineUsers',
            "echo-presence:chat-presence,joining" => 'userJoined',
            "echo-presence:chat-presence,leaving" => 'userLeft',
            'refreshSidebar' => '$refresh',
            'typing-received' => 'handleWhisperTyping',
            'update-receiver' => 'setReceiverId',

            'status-updated' => '$refresh',
            'profile-updated' => '$refresh'
        ];
    }

    public function setReceiverId($id)
    {
        $this->receiverId = (int) $id;
        $this->render(); // Force refresh to apply unread logic
    }

    public function handleWhisperTyping($data)
    {
        $senderId = $data['data']['sender_id'] ?? $data['sender_id'];
        $receiverId = $data['data']['receiver_id'] ?? $data['receiver_id'];

        // Strict comparison with (int) casting
        if ((int)$receiverId === (int)Auth::id()) {
            if ($data['data']['typing'] ?? $data['typing']) {
                $this->typingUsers[$senderId] = true;
            } else {
                unset($this->typingUsers[$senderId]);
            }
        }
    }

    public function setOnlineUsers($users)
    {
        $this->onlineUsers = collect($users)->pluck('id')->toArray();
        $this->dispatch('online-users-updated', users: array_values($this->onlineUsers));
    }
    public function userJoined($user)
    {
        if (!in_array($user['id'], $this->onlineUsers)) {
            $this->onlineUsers[] = $user['id'];
            $this->dispatch('online-users-updated', users: array_values($this->onlineUsers));
        }
    }
    public function userLeft($user)
    {
        $this->onlineUsers = array_diff($this->onlineUsers, [$user['id']]);
        $this->dispatch('online-users-updated', users: array_values($this->onlineUsers));
    }

    public function render()
    {
        $authId = Auth::id();

        $users = User::all()->map(function ($user) use ($authId) {
            // 1. Latest message logic wahi rahega
            $user->latest_msg = Message::where(function ($q) use ($authId, $user) {
                $q->where('sender_id', $authId)->where('receiver_id', $user->id);
            })->orWhere(function ($q) use ($authId, $user) {
                $q->where('sender_id', $user->id)->where('receiver_id', $authId);
            })->latest()->first();

            // 2. OPTIMIZED UNREAD LOGIC: 
            // Agar ye user wahi hai jiski chat open hai ($this->receiverId), 
            // toh query mat chalao, seedha 0 set karo.
            if ($this->receiverId == $user->id || $user->id == $authId) {
                $user->unread_count = 0;
            } else {
                // Sirf un users ke liye query chalegi jo abhi active nahi hain
                $user->unread_count = Message::where('sender_id', $user->id)
                    ->where('receiver_id', $authId)
                    ->whereNull('read_at')
                    ->count();
            }

            return $user;
        });

        // Sorting logic (same as before)
        // $sortedUsers = $users->sortByDesc(function ($user) use ($authId) {
        //     if ($user->id == $authId) return now()->addYear();
        //     return $user->latest_msg ? $user->latest_msg->created_at : 0;
        // });

        $sortedUsers = $users->sortByDesc(function ($user) use ($authId) {
            // 1. Agar user khud hai, toh use sabse upar rakho (Future timestamp)
            if ($user->id == $authId) {
                return now()->addYear()->timestamp;
            }

            // 2. Agar message hai, toh uska timestamp return karo, warna 0
            // .timestamp property date ko integer mein badal deti hai
            return $user->latest_msg ? $user->latest_msg->created_at->timestamp : 0;
        });


        return view('livewire.chat-sidebar', ['users' => $sortedUsers]);
    }
}
