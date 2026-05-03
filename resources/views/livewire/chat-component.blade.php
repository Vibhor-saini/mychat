<div class="d-flex flex-column h-100 bg-white"
    x-data="{ 
        onlineUsers: window.onlineUsersList || [], 
        isOnline: false,
        scrollToBottom() { 
            const el = document.getElementById('chatBox'); 
            if (el) el.scrollTop = el.scrollHeight; 
        },
        {{-- Naya Observer Logic: Jo naye message ko aate hi settle karega --}}
        initChatObserver() {
            const el = document.getElementById('chatBox');
            if (el) {
                this.scrollToBottom();
                const observer = new MutationObserver(() => this.scrollToBottom());
                observer.observe(el, { childList: true });
            }
        }
    }"
    {{-- Header functionality untouched --}}
    x-init="initChatObserver(); isOnline = onlineUsers.includes({{ $receiver->id ?? 0 }});"
    x-on:online-users-updated.window="onlineUsers = $event.detail.users; window.onlineUsersList = onlineUsers; isOnline = onlineUsers.includes({{ $receiver->id ?? 0 }});"
    
    x-on:scroll-bottom.window="scrollToBottom()"
    x-on:msg-sent.window="scrollToBottom()">
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
    </div>

    {{-- Footer Input Section --}}
    <div class="chat-footer p-3 bg-white border-top" style="position: relative;">
        @if($isTyping)
    <div style="position: absolute; top: -45px; left: 20px; z-index: 10;">
        <div class="bg-white p-2 px-3 rounded-pill shadow border d-flex align-items-center animate__animated animate__fadeInUp" 
             style="font-size: 0.8rem; background: rgba(255, 255, 255, 0.9) !important; backdrop-filter: blur(5px);">
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <span class="ms-2 text-dark italic">{{ $receiver->name }} is typing...</span>
        </div>
    </div>
    @endif
        <form wire:submit.prevent="sendMessage"
            {{-- Alpine Logic: Submit hote hi typing timer clear karo aur input UI se turant gayab --}}
            x-on:submit="clearTimeout(typingTimer); $dispatch('user-stopped-typing')"
            class="d-flex gap-2 align-items-center">

            <input type="text"
                {{-- FIX 1: .live hata diya taaki typing lag na kare --}}
                wire:model="messageText"
                id="msgInput"
                {{-- FIX 2: x-data yahi define kar diya taaki timer conflict na ho --}}
                x-data="{ typingTimer: null }"
                x-on:input="
               $dispatch('user-typing'); 
               clearTimeout(typingTimer); 
               typingTimer = setTimeout(() => $dispatch('user-stopped-typing'), 2000)
           "
                {{-- FIX 3: Enter maarte hi box turant khali ho jaye (Optimistic) --}}
                x-on:keydown.enter="setTimeout(() => $el.value = '', 10)"
                class="form-control rounded-pill px-3 shadow-none border"
                placeholder="Type a message..."
                autocomplete="off">

            <button type="submit"
                wire:loading.attr="disabled" {{-- Double click rokne ke liye --}}
                class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                style="width: 42px; height: 42px;">
                <i class="bi bi-send-fill text-white"></i>
            </button>
        </form>
    </div>
    @endif

    {{-- Styling for Professional Look --}}
    <style>
        .typing-dot {
            width: 6px;
            height: 6px;
            margin: 0 1px;
            background-color: #28a745;
            border-radius: 50%;
            display: inline-block;
            animation: typing-blink 1.4s infinite both;
        }

        .typing-dot:nth-child(2) {
            animation-delay: .2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: .4s;
        }

        @keyframes typing-blink {

            0%,
            80%,
            100% {
                opacity: 0;
            }

            40% {
                opacity: 1;
            }
        }

        .italic {
            font-style: italic;
        }

        #chatBox::-webkit-scrollbar {
            width: 5px;
        }

        #chatBox::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

#chatBox {
    display: flex;
    flex-direction: column;
    overflow-anchor: none !important;
    scroll-behavior: auto !important; 
}

#chatBox > div:last-child {
    overflow-anchor: auto !important;
}
    </style>

<script>
    document.addEventListener('livewire:init', () => {
        let channel = Echo.join('chat-presence');
        window.typingTimer = null;

        // Function jo sirf scroll position ko end par set karega bina kisi animation ke
        const lockToBottom = () => {
            const container = document.getElementById('chatBox');
            if (container) {
                // Bina scrollTo use kiye seedha height set karo
                container.scrollTop = container.scrollHeight;
            }
        };

        // Page load par seedha bottom par le jao
        lockToBottom();

        // 1. Typing Logic (Sirf typing received par UI update karo, scroll nahi)
        channel.listenForWhisper('typing', (e) => {
            Livewire.dispatch('typing-received', { data: e });
            // Typing indicator aane par jhatka na lage isliye scroll trigger mat karo
        });

        // 2. Real-time Message reception
        channel.listen('MessageSent', (e) => {
            // CSS (overflow-anchor) khud hi message ko niche rakhega
            // Hum bas ek chota sa check rakhenge
            setTimeout(lockToBottom, 10); 
        });

        // Jab aap khud message bhejo (Livewire events)
        window.addEventListener('scroll-bottom', lockToBottom);
        window.addEventListener('msg-sent', lockToBottom);

        // Typing setup
        window.addEventListener('user-typing', () => {
            channel.whisper('typing', {
                sender_id: {{ auth()->id() }},
                receiver_id: {{ $receiver->id ?? 0}},
                typing: true
            });
        });

        window.addEventListener('user-stopped-typing', () => {
            if (window.typingTimer) clearTimeout(window.typingTimer);
            channel.whisper('typing', {
                sender_id: {{ auth()->id() }},
                receiver_id: {{ $receiverId ?? 0 }},
                typing: false
            });
        });
    });
</script>
</div>