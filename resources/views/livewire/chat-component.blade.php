{{-- resources/views/livewire/chat-component.blade.php --}}
<div class="d-flex flex-column h-100"
    x-data="{ 
        isOnline: window.onlineUsersList ? window.onlineUsersList.includes({{ $receiver->id ?? 0 }}) : false 
     }"
    x-on:online-users-updated.window="isOnline = $event.detail.users.includes({{ $receiver->id ?? 0 }})">

    @if($receiver)
    <div class="chat-header p-3 border-bottom bg-white">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="avatar bg-primary text-white d-flex align-items-center justify-content-center rounded-circle me-2" style="width: 40px; height: 40px;">
                    {{ strtoupper(substr($receiver->name, 0, 1)) }}
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">{{ $receiver->name }}</h5>
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

    <div class="chat-messages" id="chatBox" style="overflow-y: auto; flex-grow: 1; padding: 20px; background: #f8f9fa;">
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
        <div id="chat-window-typing" class="d-none mb-3">
        <div class="d-flex justify-content-start">
            <div class="p-2 px-3 rounded bg-light text-muted shadow-sm italic" style="font-size: 0.9rem;">
                <span class="dot-flashing"></span> {{ $receiver->name }} is typing...
            </div>
        </div>
    </div>
    </div>

    <div class="chat-footer p-3 bg-light border-top">
        <form wire:submit.prevent="sendMessage" class="d-flex gap-2">
            <input type="text"
                wire:model="messageText"
                {{-- Alpine.js trigger: Whisper event to channel --}}
                x-on:input="window.Echo.join('chat-presence').whisper('typing', {
                    sender_id: {{ Auth::id() }},
                    receiver_id: {{ $receiverId }},
                    typing: true
                })"
                class="form-control"
                placeholder="Type a message..."
                required
                autocomplete="off">
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
    // Global list to keep track of online users even when navigating between chats
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

    /**
     * Updates the UI for user online/offline status
     */
    function updateSidebarStatus(userId, isOnline) {
        if (isOnline) {
            if (!window.onlineUsersList.includes(userId)) window.onlineUsersList.push(userId);
        } else {
            window.onlineUsersList = window.onlineUsersList.filter(id => id !== userId);
        }

        // Notify Alpine.js components (like the Chat Header)
        window.dispatchEvent(new CustomEvent('online-users-updated', {
            detail: { users: window.onlineUsersList }
        }));

        // Update the physical status dot in the sidebar
        const dot = document.getElementById(`status-dot-${userId}`);
        if (dot) {
            dot.classList.toggle('bg-success', isOnline);
            dot.classList.toggle('bg-secondary', !isOnline);
        }
    }

    /**
     * Core Real-time Presence and Whisper Logic
     */
    function initPresence() {
        if (!window.Echo) return;

        window.Echo.leave('chat-presence');

        window.Echo.join('chat-presence')
            .here((users) => {
                const ids = users.map(u => u.id);
                window.onlineUsersList = ids;
                ids.forEach(id => updateSidebarStatus(id, true));
            })
            .joining((user) => updateSidebarStatus(user.id, true))
            .leaving((user) => updateSidebarStatus(user.id, false))
            
            /**
             * CRITICAL FIX: Listen for the 'typing' whisper directly from the Echo channel.
             * This catches the 'client-typing' event you saw in your terminal.
             */
            .listenForWhisper('typing', (event) => {
                const senderId = event.sender_id;
                const receiverId = event.receiver_id;
                const isTyping = event.typing;

                // Only process if the message is intended for the logged-in user
                if (receiverId == {{ Auth::id() }}) {
                    handleTypingUI(senderId, isTyping);
                }
            });
    }

    /**
     * Handles the visual changes for the Typing Indicator
     */
function handleTypingUI(senderId, isTyping) {

if (senderId == {{ Auth::id() }} && {{ $receiverId ?? 0 }} == {{ Auth::id() }}) {
        return; 
    }
    
    const typingDiv = document.getElementById(`typing-indicator-${senderId}`); // Sidebar
    const msgDiv = document.getElementById(`latest-msg-${senderId}`);           // Sidebar Preview
    const windowTyping = document.getElementById('chat-window-typing');      // Chat Box Indicator

    if (isTyping) {
        // 1. Sidebar Handle
        if (typingDiv) typingDiv.classList.remove('d-none');
        if (msgDiv) msgDiv.classList.add('d-none');

        // 2. Chat Window Handle (Only if sender is the person I'm chatting with)
        if (windowTyping && senderId == {{ $receiverId ?? 0 }}) {
            windowTyping.classList.remove('d-none');
            scrollToBottom(); // Typing dikhte hi scroll niche karo
        }

        // Timer to reset automatically
        clearTimeout(window[`typingTimer_${senderId}`]);
        window[`typingTimer_${senderId}`] = setTimeout(() => {
            if (typingDiv) typingDiv.classList.add('d-none');
            if (msgDiv) msgDiv.classList.remove('d-none');
            if (windowTyping) windowTyping.classList.add('d-none');
            
            // Header reset (Optional)
            if (senderId == {{ $receiverId ?? 0 }}) {
                @this.set('isTyping', false, false); // third param 'false' means don't re-render everything
            }
        }, 1000); // 1 second ka gap is good

        // Update Header
        if (senderId == {{ $receiverId ?? 0 }}) {
            @this.set('isTyping', true, false);
        }
    } else {
        // If we receive typing: false (on message sent)
        if (typingDiv) typingDiv.classList.add('d-none');
        if (msgDiv) msgDiv.classList.remove('d-none');
        if (windowTyping) windowTyping.classList.add('d-none');
        
        if (senderId == {{ $receiverId ?? 0 }}) {
            @this.set('isTyping', false, false);
        }
    }
}

    function initApp() {
        scrollToBottom();
        initPresence();

        // Auto-scroll when new message elements are added to the DOM
        const chatBox = document.getElementById('chatBox');
        if (chatBox) {
            new MutationObserver(scrollToBottom).observe(chatBox, { childList: true });
        }
    }

    document.addEventListener('livewire:initialized', () => {
        initApp();
        
        // Scroll to bottom after sending a message
        Livewire.on('messageSent', () => setTimeout(scrollToBottom, 100));

        // Listen for internal sidebar updates (for messages sent/received via Private Channel)
        window.addEventListener('update-sidebar-text', (event) => {
            const data = event.detail;
            const msgDiv = document.getElementById(`latest-msg-${data.userId}`);
            const timeDiv = document.getElementById(`time-${data.userId}`);
            if (msgDiv) msgDiv.innerText = (data.isMe ? 'You: ' : '') + data.message;
            if (timeDiv) timeDiv.innerText = data.time;
        });
        
        // Bridge for the PHP handleTypingIndicator method if it still dispatches events
        window.addEventListener('typing-received', (event) => {
            handleTypingUI(event.detail.senderId, event.detail.typing);
        });
    });

    document.addEventListener('livewire:navigated', () => {
        // Maintain online status colors immediately while Echo reconnects
        window.onlineUsersList.forEach(id => updateSidebarStatus(id, true));
        initApp();
    });

    window.addEventListener('refresh-sidebar', () => {
        Livewire.dispatch('$refresh');
    });
</script>