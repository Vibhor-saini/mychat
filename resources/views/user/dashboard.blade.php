@extends('layouts.layout')

@section('sidebar_content')
@persist('sidebar')
@php
$authId = Auth::id();

// 1. Saare users lo aur unka latest message fetch karo
$users = App\Models\User::all()->map(function($user) use ($authId) {
$user->latest_msg = App\Models\Message::where(function($q) use ($authId, $user) {
$q->where('sender_id', $authId)->where('receiver_id', $user->id);
})->orWhere(function($q) use ($authId, $user) {
$q->where('sender_id', $user->id)->where('receiver_id', $authId);
})->latest('id')->first();

// Agar message hai toh uska time lo, warna purani date (sorting ke liye)
$user->last_interaction = $user->latest_msg ? $user->latest_msg->created_at : now()->subYears(10);

$user->unread_count = App\Models\Message::where('sender_id', $user->id)
->where('receiver_id', $authId)
->whereNull('read_at')
->count();

return $user;
})
// 2. Interaction time ke hisaab se Sort karo (Latest on Top)
->sortByDesc('last_interaction');

// 3. "Me" (Admin/You) ko hamesha top par fix rakhna hai toh ye optional logic:
$me = $users->where('id', $authId);
$others = $users->where('id', '!=', $authId);
$finalUsers = $me->concat($others);
@endphp

<div id="sidebar-container" class="d-flex flex-column">
    @foreach($users as $user)
    <a href="{{ route('chat.start', $user->id) }}" wire:navigate
        class="chat-list-item d-flex align-items-center text-decoration-none p-3 {{ isset($receiver) && $receiver->id == $user->id ? 'active' : '' }}">

        <div class="position-relative flex-shrink-0">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm {{ $user->id == $authId ? 'bg-info' : 'bg-secondary' }}"
                style="width: 38px; height: 38px; font-size: 0.85rem;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <span id="status-dot-{{ $user->id }}"
                class="position-absolute border border-white rounded-circle {{ $user->id == $authId ? 'bg-success' : 'bg-secondary' }}"
                style="width: 10px; height: 10px; bottom: 0; right: 0;"></span>
        </div>

        <div class="flex-grow-1 ms-3 min-width-0">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark text-truncate small">{{ $user->name }}</span>
                <small id="time-{{ $user->id }}" class="text-muted" style="font-size: 0.65rem;">
                    {{ $user->latest_msg ? $user->latest_msg->created_at->format('h:i A') : '' }}
                </small>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div id="latest-msg-{{ $user->id }}" class="text-muted text-truncate" style="font-size: 0.75rem; max-width: 150px;">
                    @if($user->latest_msg)
                    {{ $user->latest_msg->sender_id == $authId ? 'You: ' : '' }}{{ $user->latest_msg->message }}
                    @else
                    <span class="fst-italic opacity-50">Start a chat...</span>
                    @endif
                </div>

                {{-- Unread Badge Container --}}
                <div id="unread-badge-{{ $user->id }}">
                    @if($user->unread_count > 0)
                    <span class="badge rounded-pill bg-success" style="font-size: 0.6rem; padding: 3px 6px;">
                        {{ $user->unread_count }}
                    </span>
                    @endif
                </div>
            </div>

            {{-- Typing Indicator --}}
            <div id="typing-indicator-{{ $user->id }}" class="text-success small fw-bold d-none" style="font-size: 0.7rem;">Typing...</div>
        </div>
    </a>
    @endforeach
</div>
@endpersist

<script>
    document.addEventListener('livewire:init', () => {
        // Helper: Current open chat ki ID nikalne ke liye
        const getActiveChatId = () => window.location.pathname.split('/').pop();
        
        // 1. MAIN LISTENER: Jab message aaye ya bheja jaye
        Livewire.on('update-sidebar-text', ({ userId, message, isMe, time }) => {
            const sidebarContainer = document.getElementById('sidebar-container');
            // Chat item dhoondo uske href attribute se
            const chatItem = document.querySelector(`a[href*="/chat/${userId}"]`);
            
            if (chatItem && sidebarContainer) {
                // A. Update Message Preview
                const msgEl = chatItem.querySelector('#latest-msg-' + userId);
                if (msgEl) msgEl.textContent = isMe ? 'You: ' + message : message;
                
                // B. Update Time
                const timeEl = chatItem.querySelector('#time-' + userId);
                if (timeEl) timeEl.textContent = time;

                // C. Badge Logic: Sirf tab jab message receive ho aur wo chat open na ho
                if (!isMe && getActiveChatId() != userId) {
                    const badgeContainer = chatItem.querySelector('#unread-badge-' + userId);
                    if (badgeContainer) {
                        let badge = badgeContainer.querySelector('.badge');
                        if (badge) {
                            badge.textContent = (parseInt(badge.textContent) || 0) + 1;
                        } else {
                            badgeContainer.innerHTML = `<span class="badge rounded-pill bg-success" style="font-size: 0.6rem; padding: 3px 6px;">1</span>`;
                        }
                    }
                }

                // D. REAL-TIME RE-ORDERING: Chat ko top par move karo
                // Teams behavior: Har interaction (send/receive) par chat top par aani chahiye
                sidebarContainer.prepend(chatItem);
            }
        });

        // 2. CLEAR BADGE: Jab user kisi chat par click kare
        document.addEventListener('click', (e) => {
            const chatLink = e.target.closest('.chat-list-item');
            if (chatLink) {
                const userId = chatLink.getAttribute('href').split('/').pop();
                const container = document.getElementById('unread-badge-' + userId);
                if (container) {
                    container.innerHTML = ''; // Badge gayab
                }
            }
        });

        // 3. TYPING INDICATOR: Real-time "Typing..." status dikhane ke liye
        Livewire.on('typing-received', ({ senderId, typing }) => {
            const chatItem = document.querySelector(`a[href*="/chat/${senderId}"]`);
            if (chatItem) {
                const typingEl = chatItem.querySelector('#typing-indicator-' + senderId);
                const latestMsgEl = chatItem.querySelector('#latest-msg-' + senderId);
                
                if (typingEl && latestMsgEl) {
                    if (typing) {
                        typingEl.classList.remove('d-none');
                        latestMsgEl.classList.add('d-none');
                    } else {
                        typingEl.classList.add('d-none');
                        latestMsgEl.classList.remove('d-none');
                    }
                }
            }
        });
    });
</script>
@endsection

@section('content')
<livewire:chat-component
    :receiverId="isset($receiver) ? $receiver->id : null"
    :key="'chat-' . (isset($receiver) ? $receiver->id : 'empty')" />
@endsection