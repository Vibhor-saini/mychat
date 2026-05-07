<div class="d-flex flex-column h-100 bg-white"
    x-data="{ 
        onlineUsers: window.onlineUsersList || [], 
        isOnline: false,
        updateStatus() { 
            this.isOnline = this.onlineUsers.includes({{ (int)($receiver->id ?? 0) }}); 
        },
        scrollToBottom() { 
            const el = document.getElementById('chatBox'); 
            if (el) el.scrollTop = el.scrollHeight; 
        }
    }"
    x-init="updateStatus(); $nextTick(() => scrollToBottom());"
    x-on:online-users-updated.window="onlineUsers = $event.detail.users; updateStatus();"
    x-on:scroll-bottom.window="scrollToBottom()"
    x-on:msg-sent.window="scrollToBottom()">

    @if($receiver)
    {{-- Teams Header --}}
    <div class="p-3 border-bottom d-flex align-items-center justify-content-between shadow-sm" style="height: 60px;">
        <div class="d-flex align-items-center">
            <div class="position-relative me-3">
                <div class="avatar text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm"
                    style="width: 38px; height: 38px; background-color: #6264a7;">
                    @if($receiver->profile_image)
                    <img src="{{ asset('storage/'.$receiver->profile_image) }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                    @else
                    {{ strtoupper(substr($receiver->name, 0, 1)) }}
                    @endif
                </div>
                {{-- Dynamic Online Indicator --}}
                <span class="position-absolute border border-2 border-white rounded-circle"
                    :class="isOnline ? 'bg-success' : 'bg-secondary'"
                    style="width: 12px; height: 12px; bottom: 0; right: 0;"></span>
            </div>
            <div>
                <h6 class="mb-0 fw-bold small text-dark">{{ $receiver->name }} <i class="bi bi-pencil small ms-1 text-muted"></i></h6>
                <small :class="isOnline ? 'text-success fw-bold' : 'text-muted'" style="font-size: 0.7rem;">
                    <span x-text="isOnline ? 'Available' : 'Offline'"></span>
                </small>
            </div>
        </div>
        <div class="d-flex gap-3 text-muted align-items-center">
            <button class="btn btn-sm btn-outline-secondary border fw-bold d-none d-md-block" style="font-size: 0.75rem;"><i class="bi bi-camera-video me-2"></i> Meet now</button>
            <i class="bi bi-telephone cursor-pointer"></i>
            <i class="bi bi-people cursor-pointer"></i>
            <i class="bi bi-box-arrow-up-right cursor-pointer"></i>
        </div>
    </div>

    {{-- Messages Section --}}
    <div class="flex-grow-1 p-4 overflow-auto d-flex flex-column gap-3 bg-white" id="chatBox">
        @foreach($messages as $msg)
        <div class="d-flex {{ $msg->sender_id == auth()->id() ? 'justify-content-end' : 'justify-content-start' }}" wire:key="msg-{{ $msg->id }}">
            <div class="d-flex flex-column {{ $msg->sender_id == auth()->id() ? 'align-items-end' : 'align-items-start' }}" style="max-width: 80%;">
                <div class="mb-1 small text-muted d-flex align-items-center" style="font-size: 0.7rem;">
                    <span class="fw-bold">{{ $msg->sender_id == auth()->id() ? 'You' : $receiver->name }}</span>
                    <span class="ms-2">{{ $msg->created_at->format('h:i A') }}</span>
                </div>
                {{-- Message Bubble --}}
                <div class="p-2 px-3 rounded-3 shadow-sm border"
                    style="font-size: 0.9rem; {{ $msg->sender_id == auth()->id() ? 'background-color: #6264a7; color: white; border-color: #6264a7;' : 'background-color: #f5f5f5; color: #242424; border-color: #e1dfdd;' }}">

                    <div class="message-text text-break">{{ $msg->message }}</div>

                    {{-- Ticks Section (Only for Sender) --}}
                    @if($msg->sender_id == auth()->id())
                    <div class="d-flex align-items-center justify-content-end mt-1" style="font-size: 0.8rem; opacity: 0.8;">
                        @if($msg->read_at || $receiver->id == auth()->id())
                        {{-- Read: Blue Ticks --}}
                        <i class="bi bi-check2-all text-info"></i>
                        @elseif($msg->delivered_at)
                        {{-- Delivered: Double Grey --}}
                        <i class="bi bi-check2-all"></i>
                        @else
                        {{-- Sent: Single Tick --}}
                        <i class="bi bi-check2"></i>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Input Section --}}
    <div class="p-3 bg-white border-top">
        @if($isTyping)<div class="small text-muted italic ms-2 mb-1 animate__animated animate__fadeInUp" style="font-size: 0.75rem;">{{ $receiver->name }} is typing...</div>@endif
        <div class="border rounded-2 p-1" style="border-color: #e1dfdd !important;">
            <form wire:submit.prevent="sendMessage" x-data="{ typingTimer: null }"
                x-on:submit="clearTimeout(typingTimer); $dispatch('user-stopped-typing')" class="d-flex flex-column">
                <input type="text" wire:model="messageText" id="msgInput"
                    x-on:input="$dispatch('user-typing'); clearTimeout(typingTimer); typingTimer = setTimeout(() => $dispatch('user-stopped-typing'), 2000)"
                    class="form-control border-0 shadow-none py-2" placeholder="Type a message" autocomplete="off">
                <div class="d-flex justify-content-between align-items-center mt-1 px-2 pb-1">
                    <div class="d-flex gap-3 text-muted" style="font-size: 1.1rem;">
                        <i class="bi bi-emoji-smile cursor-pointer"></i><i class="bi bi-paperclip cursor-pointer"></i><i class="bi bi-image cursor-pointer"></i>
                    </div>
                    <button type="submit" class="btn p-0 border-0" style="color: #6264a7;"><i class="bi bi-send-fill fs-5"></i></button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
            let channel = Echo.join('chat-presence');

            // Listen for Typing
            channel.listenForWhisper('typing', (e) => {
                Livewire.dispatch('typing-received', {
                    data: e
                });
            });

            // Emit Typing
            window.addEventListener('user-typing', () => {
                let comp = Livewire.find(document.getElementById('chatBox').closest('[wire\\:id]').getAttribute('wire:id'));
                if (comp && comp.receiverId) {
                    channel.whisper('typing', {
                        sender_id: {
                            {
                                auth() - > id()
                            }
                        },
                        receiver_id: comp.receiverId,
                        typing: true
                    });
                }
            });

            window.addEventListener('user-stopped-typing', () => {
                let comp = Livewire.find(document.getElementById('chatBox').closest('[wire\\:id]').getAttribute('wire:id'));
                if (comp && comp.receiverId) {
                    channel.whisper('typing', {
                        sender_id: {
                            {
                                auth() - > id()
                            }
                        },
                        receiver_id: comp.receiverId,
                        typing: false
                    });
                }
            });
        });
    </script>
</div>