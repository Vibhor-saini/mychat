{{-- resources/views/livewire/chat-component.blade.php --}}
<div class="d-flex flex-column h-100">
    @if($receiver)
    <div class="chat-header">
        <div class="d-flex align-items-center">
            <div class="avatar bg-primary">{{ strtoupper(substr($receiver->name, 0, 1)) }}</div>
            <h5 class="mb-0 fw-bold">{{ $receiver->name }}</h5>
        </div>
    </div>

    <div class="chat-messages" id="chatBox" style="overflow-y: auto;" style="overflow-y: auto;">
        @foreach($messages as $msg)
        <div class="d-flex {{ $msg->sender_id == Auth::id() ? 'justify-content-end' : 'justify-content-start' }} mb-3">
            <div class="p-2 px-3 rounded shadow-sm {{ $msg->sender_id == Auth::id() ? 'bg-primary text-white' : 'bg-white text-dark' }}" style="max-width: 70%;">
                {{ $msg->message }}
                <div style="font-size: 0.7rem;" class="text-end opacity-75">
                    {{ $msg->created_at->format('h:i A') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="chat-footer">
        {{-- Form ko hum Livewire Actions mein badlenge --}}
        <form wire:submit.prevent="sendMessage" class="d-flex gap-2">
            <input type="text" wire:model="messageText" class="form-control" placeholder="Type a message..." required autocomplete="off">
            <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
        </form>
    </div>
    @else
    {{-- Empty state wala code yahan --}}
    <div class="h-100 d-flex align-items-center justify-content-center text-muted text-center">
        <div>
            <i class="bi bi-chat-dots" style="font-size: 4rem;"></i>
            <p>Select a user to chat</p>
        </div>
    </div>
    @endif
</div>

<script>
    function scrollToBottom() {
        const chatBox = document.getElementById('chatBox');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }

    // Har baar jab Livewire message refresh kare, scroll niche jaye
    document.addEventListener('livewire:initialized', () => {
        scrollToBottom();

        Livewire.on('messageSent', () => {
            setTimeout(() => {
                scrollToBottom();
            }, 50);
        });
    });

    //============================================================

    function scrollToBottom() {
        const chatBox = document.getElementById('chatBox');
        if (chatBox) {
            chatBox.scrollTo({
                top: chatBox.scrollHeight,
                behavior: 'smooth' // Smooth scrolling ke liye
            });
        }
    }

    document.addEventListener('livewire:navigated', scrollToBottom);
    document.addEventListener('livewire:initialized', () => {
        // 1. Pehli baar load hone par
        scrollToBottom();

        // 2. Jab aap message bhejein (Custom Event)
        Livewire.on('messageSent', () => {
            setTimeout(scrollToBottom, 100);
        });

        // 3. Jab polling se naye messages aayein (DOM change monitor)
        const chatBox = document.getElementById('chatBox');
        const observer = new MutationObserver(() => {
            scrollToBottom();
        });

        if (chatBox) {
            observer.observe(chatBox, {
                childList: true
            });
        }
    });
</script>