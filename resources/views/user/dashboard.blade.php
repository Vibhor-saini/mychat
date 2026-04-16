@extends('layouts.layout')

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

@section('content')
    {{-- Magic Line: Ab saara chat logic is component ke andar hai --}}
    <livewire:chat-component :receiverId="$receiver->id ?? null" />
@endsection