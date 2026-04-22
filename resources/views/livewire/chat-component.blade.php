{{-- resources/views/livewire/chat-component.blade.php --}}
<div class="d-flex flex-column h-100"
    x-data="{ 
        isOnline: window.onlineUsersList ? window.onlineUsersList.includes({{ $receiver->id ?? 0 }}) : false 
     }"
    x-on:online-users-updated.window="isOnline = $event.detail.users.includes({{ $receiver->id ?? 0 }})">

    @if($receiver)
    <div class="chat-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="avatar bg-primary text-white d-flex align-items-center justify-content-center rounded-circle mr-2" style="width: 40px; height: 40px;">
                    {{ strtoupper(substr($receiver->name, 0, 1)) }}
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">{{ $receiver->name }}</h5>
                    {{-- Alpine.js handles this instantly from the global window.onlineUsersList --}}
                    <template x-if="isOnline">
                        <small class="text-success fw-bold">Online</small>
                    </template>
                    <template x-if="!isOnline">
                        <small class="text-muted">Offline</small>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="chat-messages" id="chatBox" style="overflow-y: auto; flex-grow: 1; padding: 20px;">
        @foreach($messages as $msg)
        <div class="d-flex {{ $msg->sender_id == Auth::id() ? 'justify-content-end' : 'justify-content-start' }} mb-3" wire:key="msg-{{ $msg->id }}">
            <div class="p-2 px-3 rounded shadow-sm {{ $msg->sender_id == Auth::id() ? 'bg-primary text-white' : 'bg-white text-dark' }}" style="max-width: 70%;">
                {{ $msg->message }}
                <div style="font-size: 0.7rem;" class="text-end opacity-75">
                    {{ $msg->created_at->format('h:i A') }}

                    @if($msg->sender_id == Auth::id())
                    @if($msg->read_at)
                    <i class="bi bi-check2-all text-info" title="Seen at {{ $msg->read_at->format('h:i A') }}"></i>
                    @else
                    <i class="bi bi-check2"></i>
                    @endif
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="chat-footer p-3 bg-light border-top">
        <form wire:submit.prevent="sendMessage" class="d-flex gap-2">
            <input type="text" wire:model="messageText" class="form-control" placeholder="Type a message..." required autocomplete="off">
            <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
        </form>
    </div>
    @else
    <div class="h-100 d-flex align-items-center justify-content-center text-muted text-center">
        <div>
            <i class="bi bi-chat-dots" style="font-size: 4rem;"></i>
            <p>Select a user to chat</p>
        </div>
    </div>
    @endif
</div>

<script>
    // Global list to persist status across wire:navigate
    window.onlineUsersList = window.onlineUsersList || [];

    function scrollToBottom() {
        const chatBox = document.getElementById('chatBox');
        if (chatBox) {
            chatBox.scrollTo({
                top: chatBox.scrollHeight,
                behavior: 'smooth'
            });
        }
    }

    function updateSidebarStatus(userId, isOnline) {
        // 1. Update Global Memory
        if (isOnline) {
            if (!window.onlineUsersList.includes(userId)) window.onlineUsersList.push(userId);
        } else {
            window.onlineUsersList = window.onlineUsersList.filter(id => id !== userId);
        }

        // 2. Notify Alpine Components (The Header)
        window.dispatchEvent(new CustomEvent('online-users-updated', {
            detail: {
                users: window.onlineUsersList
            }
        }));

        // 3. Update Sidebar Dots (Physical DOM)
        const dot = document.getElementById(`status-dot-${userId}`);
        if (dot) {
            dot.classList.toggle('bg-success', isOnline);
            dot.classList.toggle('bg-secondary', !isOnline);
        }
    }

    function initPresence() {
        if (!window.Echo) return;

        // Leave existing channel to avoid double-binding on navigation
        window.Echo.leave('chat-presence');

        window.Echo.join('chat-presence')
            .here((users) => {
                const ids = users.map(u => u.id);
                window.onlineUsersList = ids;
                ids.forEach(id => updateSidebarStatus(id, true));
            })
            .joining((user) => {
                updateSidebarStatus(user.id, true);
            })
            .leaving((user) => {
                updateSidebarStatus(user.id, false);
            });
    }

    function initApp() {
        scrollToBottom();
        initPresence();

        // MutationObserver for auto-scroll on new messages
        const chatBox = document.getElementById('chatBox');
        if (chatBox) {
            const observer = new MutationObserver(scrollToBottom);
            observer.observe(chatBox, {
                childList: true
            });
        }

        window.addEventListener('typing-received', (event) => {
            const senderId = event.detail.senderId;
            const isTyping = event.detail.typing;

            const typingDiv = document.getElementById(`typing-indicator-${senderId}`);
            const msgDiv = document.getElementById(`latest-msg-${senderId}`);

            if (typingDiv && msgDiv) {
                if (isTyping) {
                    typingDiv.classList.remove('d-none');
                    msgDiv.classList.add('d-none');

                    // Auto-hide typing after 3 seconds if no new whisper comes
                    clearTimeout(window[`typingTimer_${senderId}`]);
                    window[`typingTimer_${senderId}`] = setTimeout(() => {
                        typingDiv.classList.add('d-none');
                        msgDiv.classList.remove('d-none');
                    }, 3000);
                } else {
                    typingDiv.classList.add('d-none');
                    msgDiv.classList.remove('d-none');
                }
            }
        });
    }

    document.addEventListener('livewire:initialized', () => {
        initApp();

        // 1. Existing: Scroll to bottom when message is sent
        Livewire.on('messageSent', () => {
            setTimeout(scrollToBottom, 100);
        });

        // 2. NAYA: Real-time Sidebar Update Listener
        // Jab bhi PHP se 'update-sidebar-text' dispatch hoga, ye function chalega
        window.addEventListener('update-sidebar-text', (event) => {
            const data = event.detail; // Isme userId, message, time aur isMe milega

            // Sidebar mein message ka text update karo
            const msgDiv = document.getElementById(`latest-msg-${data.userId}`);
            if (msgDiv) {
                msgDiv.innerText = (data.isMe ? 'You: ' : '') + data.message;
            }

            // Sidebar mein time update karo
            const timeDiv = document.getElementById(`time-${data.userId}`);
            if (timeDiv) {
                timeDiv.innerText = data.time;
            }
        });
    });

    document.addEventListener('livewire:navigated', () => {
        // Apply instant status from memory while Echo reconnects
        window.onlineUsersList.forEach(id => updateSidebarStatus(id, true));
        initApp();
    });

    window.addEventListener('refresh-sidebar', () => {
        Livewire.dispatch('$refresh');
    });
</script>