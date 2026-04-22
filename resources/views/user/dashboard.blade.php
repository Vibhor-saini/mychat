@extends('layouts.layout')


@section('sidebar_content')
@persist('sidebar')
@php 
    $authId = Auth::id();
    $users = App\Models\User::orderByRaw('id = ? DESC', [$authId])
              ->orderBy('name', 'asc')
              ->get()
              ->map(function($user) use ($authId) {
                  // Fetch the absolute latest message between Auth User and this specific $user
                  $user->latest_msg = App\Models\Message::where(function($q) use ($authId, $user) {
                      $q->where('sender_id', $authId)->where('receiver_id', $user->id);
                  })
                  ->orWhere(function($q) use ($authId, $user) {
                      $q->where('sender_id', $user->id)->where('receiver_id', $authId);
                  })
                  ->latest('id') // ID se sort karna zyada accurate hota hai
                  ->first();
                  
                  return $user;
              });
@endphp
@foreach($users as $user)
<a href="{{ route('chat.start', $user->id) }}" wire:navigate
    class="chat-list-item {{ isset($receiver) && $receiver->id == $user->id ? 'active' : '' }}">
    
    <div class="position-relative">
        <div class="avatar {{ $user->id == Auth::id() ? 'bg-info' : 'bg-secondary' }}">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <span id="status-dot-{{ $user->id }}"
            class="position-absolute bottom-0 end-0 p-1 {{ $user->id == Auth::id() ? 'bg-success' : 'bg-secondary' }} border border-2 border-white rounded-circle"
            style="width: 12px; height: 12px; transform: translate(25%, 25%);">
        </span>
    </div>

    <div class="flex-grow-1 ms-3">
        <div class="d-flex justify-content-between">
            <span class="fw-bold text-dark">
                {{ $user->name }} 
                {{-- Add 'You' label for personal chat --}}
                @if($user->id == Auth::id()) <span class="text-primary small">(You)</span> @endif
            </span>
            <small id="time-{{ $user->id }}" class="text-muted">
                {{ $user->latest_message ? $user->latest_message->created_at->format('h:i A') : '' }}
            </small>
        </div>
        
        {{-- Rest of your sidebar logic (typing-indicator, latest-msg) --}}
        <div id="typing-indicator-{{ $user->id }}" class="text-success small fw-bold d-none">Typing...</div>
<div id="latest-msg-{{ $user->id }}" class="text-muted small text-truncate">
    @if($user->latest_msg)
        {{ $user->latest_msg->sender_id == auth()->id() ? 'You: ' : '' }}
        {{ $user->latest_msg->message }}
    @else
        {{-- Conditional placeholder based on whether it's me or someone else --}}
        @if($user->id == auth()->id())
            <span class="fst-italic text-secondary">Message yourself...</span>
        @else
            <span class="fst-italic text-secondary">Start a conversation...</span>
        @endif
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