@extends('layout')

{{-- CRITICAL: Name must match @yield('sidebar_content') in layout --}}
@section('sidebar_content')
    @php $users = App\Models\User::where('id', '!=', Auth::id())->get(); @endphp
    @foreach($users as $user)
        <a href="{{ route('chat.start', $user->id) }}" 
           class="chat-list-item {{ isset($receiver) && $receiver->id == $user->id ? 'active' : '' }}">
            <div class="avatar bg-secondary">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold text-dark">{{ $user->name }}</span>
                    <small class="text-muted">10:58 AM</small>
                </div>
                <div class="text-muted small text-truncate" style="max-width: 180px;">Start a new conversation...</div>
            </div>
        </a>
    @endforeach
@endsection

{{-- Only ONE @section('content') block --}}
@section('content')
    @if(isset($receiver))
        <div class="chat-header">
            <div class="d-flex align-items-center">
                <div class="avatar bg-primary">{{ strtoupper(substr($receiver->name, 0, 1)) }}</div>
                <h5 class="mb-0 fw-bold">{{ $receiver->name }}</h5>
            </div>
        </div>

        <div class="chat-messages" id="chatBox">
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
            <form action="{{ route('message.send') }}" method="POST" class="d-flex gap-2">
                @csrf
                <input type="hidden" name="receiver_id" value="{{ $receiver->id }}">
                <input type="text" name="message" class="form-control" placeholder="Type a message..." required autocomplete="off">
                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
            </form>
        </div>
    @else
        <div class="chat-header">
            <div class="d-flex align-items-center">
                <div class="avatar bg-primary">?</div>
                <h5 class="mb-0 fw-bold">Select a user to chat</h5>
            </div>
        </div>
        <div class="chat-messages d-flex align-items-center justify-content-center text-muted text-center">
            <div>
                <i class="bi bi-chat-dots" style="font-size: 4rem;"></i>
                <p>Your messages will appear here</p>
            </div>
        </div>
    @endif
@endsection