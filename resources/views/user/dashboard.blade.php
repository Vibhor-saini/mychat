@extends('layouts.layout')


@section('sidebar_content')
@persist('sidebar')
@php $users = App\Models\User::where('id', '!=', Auth::id())->get(); @endphp
@foreach($users as $user)
<a href="{{ route('chat.start', $user->id) }}" wire:navigate
    class="chat-list-item {{ isset($receiver) && $receiver->id == $user->id ? 'active' : '' }}">

    {{-- Wrap avatar in a relative div to position the dot --}}
    <div class="position-relative">
        <div class="avatar bg-secondary">{{ strtoupper(substr($user->name, 0, 1)) }}</div>

        {{--
        ADJUSTMENT: This is the Presence Indicator.
                    1. We give it a unique ID: status-dot-{{ $user->id }}
        2. Default class is 'bg-secondary' (Offline)
        --}}
        <span id="status-dot-{{ $user->id }}"
            class="position-absolute bottom-0 end-0 p-1 bg-secondary border border-2 border-white rounded-circle"
            style="width: 12px; height: 12px; transform: translate(25%, 25%);">
        </span>
    </div>

    <div class="flex-grow-1 ms-3">
        <div class="d-flex justify-content-between">
            <span class="fw-bold text-dark">{{ $user->name }}</span>
            {{-- Show time of latest message --}}
            <small class="text-muted">
                {{ $user->latest_message ? $user->latest_message->created_at->format('h:i A') : '' }}
            </small>
        </div>

        {{-- Typing Indicator Placeholder --}}
        <div id="typing-indicator-{{ $user->id }}" class="text-success small fw-bold d-none">
            Typing...
        </div>

        {{-- Latest Message Text --}}
        <div id="latest-msg-{{ $user->id }}" class="text-muted small text-truncate">
            @if($user->latest_message)
            {{ $user->latest_message->sender_id == auth()->id() ? 'You: ' : '' }}
            {{ $user->latest_message->message }}
            @else
            Start a new conversation...
            @endif
        </div>
    </div>
</a>
@endforeach
@endpersist
@endsection

@section('content')
{{-- We check if $receiver exists before accessing its properties --}}
<livewire:chat-component
    :receiverId="isset($receiver) ? $receiver->id : null"
    :key="'chat-' . (isset($receiver) ? $receiver->id : 'empty')" />
@endsection