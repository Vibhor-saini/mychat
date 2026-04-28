<div class="chat-sidebar-list overflow-auto" style="height: calc(100vh - 100px);">
    @foreach($users as $user)
    @php
        $isMe = ($user->id === auth()->id());
    @endphp
    
    <a href="{{ route('chat.start', $user->id) }}" wire:navigate 
       wire:key="row-{{ $user->id }}-{{ $user->latest_msg?->id }}-{{ $user->unread_count }}-{{ count($onlineUsers) }}"
       class="chat-list-item {{ $receiverId == $user->id ? 'active' : '' }} d-flex align-items-center p-3 text-decoration-none border-bottom shadow-sm-hover">
       
        {{-- Avatar Section --}}
        <div class="position-relative">
            <div class="avatar {{ $isMe ? 'bg-secondary' : 'bg-primary' }} text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px;">
                @if($user->profile_image)
                    <img src="{{ asset('storage/'.$user->profile_image) }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </div>
            {{-- Online Indicator --}}
            <span class="position-absolute bottom-0 end-0 p-1 border border-2 border-white rounded-circle {{ in_array($user->id, $onlineUsers) ? 'bg-success blink' : 'bg-secondary' }}"
                  style="width: 12px; height: 12px; transform: translate(20%, 20%);"></span>
        </div>

        <div class="flex-grow-1 ms-3 overflow-hidden">
            <div class="d-flex justify-content-between align-items-center">
                {{-- Name + "You" Label --}}
                <span class="fw-bold text-dark text-truncate">
                    {{ $user->name }} @if($isMe) <span class="text-muted fw-normal small">(You)</span> @endif
                </span>
                
                @if($user->latest_msg)
                    <small class="text-muted" style="font-size: 0.7rem;">
                        {{ $user->latest_msg->created_at->format('h:i A') }}
                    </small>
                @endif
            </div>

            <div class="d-flex justify-content-between align-items-center">
                {{-- Message Preview and Ticks --}}
                <div class="text-truncate flex-grow-1 pe-2">
                    @if(isset($typingUsers[$user->id]))
                        <span class="text-success small fw-bold italic">Typing...</span>
                    @else
                        <span class="text-muted small d-flex align-items-center">
                            @if($user->latest_msg)
                                {{-- Professional Ticks Logic --}}
                                @if($user->latest_msg->sender_id == auth()->id())
                                    <span class="me-1">
                                        @if($user->latest_msg->read_at || $isMe)
                                            {{-- Blue Ticks --}}
                                            <i class="bi bi-check2-all text-info"></i>
                                        @elseif($user->latest_msg->delivered_at)
                                            {{-- Double Grey Ticks --}}
                                            <i class="bi bi-check2-all"></i>
                                        @else
                                            {{-- Single Grey Tick --}}
                                            <i class="bi bi-check2"></i>
                                        @endif
                                    </span>
                                @endif
                                {{ Str::limit($user->latest_msg->message, 25) }}
                            @else
                                <span class="fst-italic opacity-75">No messages yet</span>
                            @endif
                        </span>
                    @endif
                </div>

                {{-- Unread Badge --}}
                @if($user->unread_count > 0 && !$isMe)
                    <span class="badge rounded-pill bg-success shadow-sm" style="font-size: 0.65rem;">
                        {{ $user->unread_count }}
                    </span>
                @endif
            </div>
        </div>
    </a>
    @endforeach
</div>

<style>
    .chat-list-item:hover { background-color: #f8f9fa; transition: 0.2s; }
    .chat-list-item.active { background-color: #e9ecef; border-left: 4px solid #0d6efd; }
    .blink { animation: blinker 1.5s linear infinite; } 
    @keyframes blinker { 50% { opacity: 0.4; } }
    .italic { font-style: italic; }
</style>