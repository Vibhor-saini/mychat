<div class="d-flex flex-column h-100" 
     x-data="{ 
        isOnline: false,
        scrollToBottom() {
            $nextTick(() => {
                const el = document.getElementById('chatBox');
                if (el) el.scrollTop = el.scrollHeight;
            });
        }
     }" 
     x-init="scrollToBottom();"
     x-on:online-users-updated.window="isOnline = $event.detail.users.includes({{ $receiver->id ?? 0 }})"
     x-on:scroll-bottom.window="scrollToBottom()">
    
    @if($receiver)
    <div class="chat-header p-3 border-bottom bg-white d-flex align-items-center">
        <div class="avatar bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            {{ strtoupper(substr($receiver->name, 0, 1)) }}
        </div>
        <div>
            <h6 class="mb-0 fw-bold">{{ $receiver->name }}</h6>
            <small :class="isOnline ? 'text-success fw-bold' : 'text-muted'" x-text="isOnline ? 'Online' : 'Offline'"></small>
        </div>
    </div>

    <div class="chat-messages flex-grow-1 p-3 overflow-auto bg-light" id="chatBox">
        @foreach($messages as $msg)
            <div class="d-flex {{ $msg->sender_id == auth()->id() ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                <div class="p-2 px-3 rounded shadow-sm {{ $msg->sender_id == auth()->id() ? 'bg-primary text-white' : 'bg-white' }}" style="max-width: 75%">
                    {{ $msg->message }}
                    <div class="text-end mt-1" style="font-size: 0.65rem; opacity: 0.8">
                        {{ $msg->created_at->format('h:i A') }}
                        @if($msg->sender_id == auth()->id())
                            <i class="bi {{ $msg->read_at ? 'bi-check2-all text-info' : ($msg->delivered_at ? 'bi-check2-all' : 'bi-check2') }}"></i>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
        
        @if($isTyping)
            <div class="text-muted small fst-italic"><span class="dot-flashing"></span> Typing...</div>
        @endif
    </div>

    <div class="chat-footer p-3 bg-white border-top">
        <form wire:submit.prevent="sendMessage" class="d-flex gap-2">
            <input type="text" wire:model.live="messageText" class="form-control" placeholder="Type message...">
            <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
        </form>
    </div>
    @else
    <div class="h-100 d-flex align-items-center justify-content-center text-muted">Select a chat to start</div>
    @endif
</div>