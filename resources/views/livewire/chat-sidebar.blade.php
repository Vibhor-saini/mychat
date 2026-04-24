<div class="chat-sidebar-list overflow-auto" style="height: calc(100vh - 100px);" id="sidebarList">
    @foreach($users as $user)
    {{-- wire:key is critical for preventing flickers --}}
    <a href="{{ route('chat.start', $user->id) }}" wire:navigate 
       wire:key="sidebar-user-{{ $user->id }}-{{ $user->unread_count }}-{{ isset($typingUsers[$user->id]) }}"
       class="chat-list-item {{ $receiverId == $user->id ? 'active' : '' }}">
        
        <div class="position-relative">
            <div class="avatar {{ $user->id == auth()->id() ? 'bg-info' : 'bg-secondary' }}">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            
            {{-- Green Dot (Online Status) --}}
            <span class="position-absolute bottom-0 end-0 p-1 border border-2 border-white rounded-circle {{ in_array($user->id, $onlineUsers) ? 'bg-success' : 'bg-secondary' }}"
                  style="width: 12px; height: 12px; transform: translate(25%, 25%);">
            </span>
        </div>

        <div class="flex-grow-1 ms-3 overflow-hidden">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-truncate text-dark">{{ $user->name }} @if($user->id == auth()->id()) <small class="text-primary">(You)</small> @endif</span>
                <small class="text-muted" style="font-size: 0.7rem;">
                    {{ $user->latest_msg ? $user->latest_msg->created_at->format('h:i A') : '' }}
                </small>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-1">
                <div class="text-truncate flex-grow-1">
                    {{-- 1. TYPING INDICATOR FIX --}}
                    @if(isset($typingUsers[$user->id]) && $typingUsers[$user->id])
                        <span class="text-success small fw-bold">Typing...</span>
                    @else
                        <span class="text-muted small">
                            {{-- Ticks Logic --}}
                            @if($user->latest_msg && $user->latest_msg->sender_id == auth()->id())
                                @if($user->latest_msg->read_at) <i class="bi bi-check2-all text-info"></i>
                                @elseif($user->latest_msg->delivered_at) <i class="bi bi-check2-all"></i>
                                @else <i class="bi bi-check2"></i> @endif
                            @endif
                            {{ $user->latest_msg->message ?? 'No messages yet' }}
                        </span>
                    @endif
                </div>

                {{-- 2. BADGE FIX (Logic in Component handles if chat is open) --}}
                @if($user->unread_count > 0 && $receiverId != $user->id)
                    <span class="badge rounded-pill bg-success ms-2" style="font-size: 0.7rem;">
                        {{ $user->unread_count }}
                    </span>
                @endif
            </div>
        </div>
    </a>
    @endforeach
</div>

<script>
    document.addEventListener('livewire:init', () => {
        // Echo se typing event pakadne ke liye
        window.Echo.join('chat-presence')
            .listen('.typing', (e) => {
                // Sidebar refresh trigger karo typing status update karne ke liye
                Livewire.dispatch('refreshSidebar');
            });

        // Naya message aane par sidebar refresh
        window.Echo.private('chat.' + {{ auth()->id() }})
            .listen('MessageSent', (e) => {
                Livewire.dispatch('refreshSidebar');
            });
    });
</script>