<div class="d-flex flex-column h-100 bg-white" 
     x-data="{ 
        onlineUsers: window.onlineUsersList || [], 
        isOnline: false,
        scrollToBottom() { 
            $nextTick(() => { 
                const el = document.getElementById('chatBox'); 
                if (el) el.scrollTop = el.scrollHeight; 
            }); 
        }
     }" 
     x-init="scrollToBottom(); isOnline = onlineUsers.includes({{ $receiver->id ?? 0 }});"
     x-on:online-users-updated.window="onlineUsers = $event.detail.users; window.onlineUsersList = onlineUsers; isOnline = onlineUsers.includes({{ $receiver->id ?? 0 }});"
     x-on:scroll-bottom.window="scrollToBottom()">

    @if($receiver)
        {{-- Header Section --}}
        <div class="chat-header p-3 border-bottom bg-white d-flex align-items-center shadow-sm">
            <div class="avatar bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                {{ strtoupper(substr($receiver->name, 0, 1)) }}
            </div>
            <div>
                <h6 class="mb-0 fw-bold">
                    {{ $receiver->name }} 
                    @if($receiver->id == auth()->id()) <span class="text-muted fw-normal small">(You)</span> @endif
                </h6>
                <small :class="isOnline ? 'text-success fw-bold' : 'text-muted'">
                    <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                    <span x-text="isOnline ? 'Online' : 'Offline'"></span>
                </small>
            </div>
        </div>

        {{-- Messages Section --}}
        <div class="chat-messages flex-grow-1 p-3 overflow-auto bg-light" id="chatBox" 
             style="background-color: #e5ddd5 !important; background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');">
            
            @foreach($messages as $msg)
                <div class="d-flex {{ $msg->sender_id == auth()->id() ? 'justify-content-end' : 'justify-content-start' }} mb-2" wire:key="msg-{{ $msg->id }}">
                    <div class="p-2 px-3 rounded shadow-sm {{ $msg->sender_id == auth()->id() ? 'bg-primary text-white' : 'bg-white text-dark' }}" 
                         style="max-width: 75%; min-width: 80px; position: relative;">
                        
                        <div class="message-text text-break">{{ $msg->message }}</div>
                        
                        {{-- Message Footer: Time + Professional Ticks --}}
                        <div class="d-flex align-items-center justify-content-end mt-1" style="font-size: 0.65rem; opacity: 0.85; min-width: 65px;">
                            <span class="me-1">{{ $msg->created_at->format('h:i A') }}</span>

                            @if($msg->sender_id == auth()->id())
                                <span class="d-flex align-items-center">
                                    @if($msg->read_at || $receiver->id == auth()->id())
                                        {{-- Blue Double Ticks --}}
                                        <i class="bi bi-check2-all text-info" style="font-size: 0.95rem;"></i>
                                    @elseif($msg->delivered_at)
                                        {{-- Double Grey Ticks --}}
                                        <i class="bi bi-check2-all {{ $msg->sender_id == auth()->id() ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.95rem;"></i>
                                    @else
                                        {{-- Single Grey Tick --}}
                                        <i class="bi bi-check2 {{ $msg->sender_id == auth()->id() ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.95rem;"></i>
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Real-time Typing Indicator --}}
            @if($isTyping)
                <div class="d-flex justify-content-start mb-3">
                    <div class="bg-white p-2 px-3 rounded shadow-sm text-muted small d-flex align-items-center border">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <span class="ms-2 italic">{{ $receiver->name }} is typing...</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer Input Section --}}
        <div class="chat-footer p-3 bg-white border-top">
            <form wire:submit.prevent="sendMessage" 
      x-on:submit="clearTimeout(typingTimer); $dispatch('user-stopped-typing')" 
      class="d-flex gap-2 align-items-center">
<input type="text" 
       wire:model.live.debounce.150ms="messageText" 
       id="msgInput" 
       {{-- Alpine logic added here --}}
       x-on:input="
           $dispatch('user-typing'); 
           clearTimeout(typingTimer); 
           typingTimer = setTimeout(() => $dispatch('user-stopped-typing'), 2000)
       "
       class="form-control rounded-pill px-3 shadow-none border" 
       placeholder="Type a message..." 
       autocomplete="off">
                
                <button type="submit" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px;">
                    <i class="bi bi-send-fill text-white"></i>
                </button>
            </form>
        </div>
    @endif

    {{-- Styling for Professional Look --}}
    <style>
        .typing-dot { width: 6px; height: 6px; margin: 0 1px; background-color: #28a745; border-radius: 50%; display: inline-block; animation: typing-blink 1.4s infinite both; }
        .typing-dot:nth-child(2) { animation-delay: .2s; }
        .typing-dot:nth-child(3) { animation-delay: .4s; }
        @keyframes typing-blink { 0%, 80%, 100% { opacity: 0; } 40% { opacity: 1; } }
        .italic { font-style: italic; }
        #chatBox::-webkit-scrollbar { width: 5px; }
        #chatBox::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    </style>

    {{-- Echo/Whisper Logic for Typing --}}
<script>
    document.addEventListener('livewire:init', () => {
        let channel = Echo.join('chat-presence');
        // Timer ko window object par rakhte hain taaki easily clear ho sake
        window.typingTimer = null;

        window.addEventListener('user-typing', () => {
            channel.whisper('typing', {
                sender_id: {{ auth()->id() }},
                receiver_id: {{ $receiverId ?? 0 }},
                typing: true
            });
        });

        window.addEventListener('user-stopped-typing', () => {
            // Agar pehle se koi timer chal raha hai toh use clear karo
            if(window.typingTimer) clearTimeout(window.typingTimer);
            
            channel.whisper('typing', {
                sender_id: {{ auth()->id() }},
                receiver_id: {{ $receiverId ?? 0 }},
                typing: false
            });
        });

        channel.listenForWhisper('typing', (e) => {
            Livewire.dispatch('typing-received', { data: e });
        });
    });
</script>   
</div>