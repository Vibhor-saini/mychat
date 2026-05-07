<div class="chat-sidebar-list overflow-auto" style="background: #f5f5f5; height: 100%;">
    {{-- Section Header --}}
    <div class="px-3 py-2 small fw-bold text-muted" style="font-size: 0.75rem;">
        <i class="bi bi-chevron-down me-2" style="font-size: 0.6rem;"></i> Pinned
    </div>

    @foreach($users as $user)
    @php $isMe = ($user->id === auth()->id()); @endphp

    <a href="{{ route('chat.start', $user->id) }}" wire:navigate
        wire:key="sidebar-user-{{ $user->id }}"
        class="d-flex align-items-center px-3 py-2 text-decoration-none mx-2 mb-1 rounded-2 {{ $receiverId == $user->id ? 'bg-white shadow-sm border' : '' }}"
        style="transition: 0.2s; border: 1px solid transparent;">

        {{-- 1. Avatar with Online Indicator --}}
        <div class="position-relative">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                style="width: 38px; height: 38px; background-color: #6264a7; border: 1px solid #ddd;">
                @if($user->profile_image)
                <img src="{{ asset('storage/'.$user->profile_image) }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                @else
                {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </div>
            <span class="position-absolute border border-2 border-white rounded-circle"
                x-data="{ online: window.onlineUsersList?.includes({{ $user->id }}) || false }"
                x-on:instant-online-status.window="online = $event.detail.users.includes({{ $user->id }})"
                {{-- Dot Color Logic --}}
                :class="!online ? 'bg-secondary' : 
           ('{{ $user->availability }}' == 'busy' ? 'bg-danger' : 
           ('{{ $user->availability }}' == 'away' ? 'bg-warning' : 'bg-success'))"
                style="width: 11px; height: 11px; bottom: 0; right: 0;">
            </span>
        </div>

        {{-- 2. Info Content --}}
        <div class="flex-grow-1 ms-2 overflow-hidden">
            {{-- Name and Time Row --}}
            <div class="d-flex justify-content-between align-items-center">
                <span class="small text-dark {{ $user->unread_count > 0 ? 'fw-bold' : '' }}" style="font-size: 0.85rem; white-space: nowrap;">
                    {{ $user->name }} @if($isMe) <small class="text-muted fw-normal">(You)</small> @endif
                </span>

                @if($user->latest_msg)
                <small class="text-muted" style="font-size: 0.65rem; min-width: 45px; text-align: right;">
                    @if($user->latest_msg->created_at->isToday())
                    {{ $user->latest_msg->created_at->format('h:i A') }}
                    @elseif($user->latest_msg->created_at->isYesterday())
                    Yesterday
                    @else
                    {{ $user->latest_msg->created_at->format('n/j') }}
                    @endif
                </small>
                @endif
            </div>

            {{-- Message Preview and Ticks Row --}}
            <div class="d-flex justify-content-between align-items-center mt-1">
                <div class="text-truncate text-muted d-flex align-items-center" style="font-size: 0.75rem; flex: 1;">
                    @if(isset($typingUsers[$user->id]))
                    <span class="text-success fw-bold italic animate__animated animate__pulse animate__infinite">Typing...</span>
                    @else
                    {{-- Ticks for Sender --}}
                    @if($user->latest_msg && $user->latest_msg->sender_id == auth()->id())
                    <span class="me-1 d-flex align-items-center">
                        @if($user->latest_msg->read_at)
                        <i class="bi bi-check2-all text-info" style="font-size: 0.9rem;"></i>
                        @elseif($user->latest_msg->delivered_at)
                        <i class="bi bi-check2-all" style="font-size: 0.9rem;"></i>
                        @else
                        <i class="bi bi-check2" style="font-size: 0.9rem;"></i>
                        @endif
                    </span>
                    @endif

                    <span class="text-truncate">{{ $user->latest_msg?->message ?? ucfirst($user->role) }}</span>
                    @endif
                </div>

                {{-- Unread Count Badge --}}
                @if($user->unread_count > 0 && !$isMe)
                <span class="badge rounded-pill bg-danger ms-1" style="font-size: 0.6rem; padding: 0.35em 0.65em;">
                    {{ $user->unread_count }}
                </span>
                @endif
            </div>
        </div>
    </a>
    @endforeach
</div>