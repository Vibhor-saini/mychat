@extends('layouts.layout')

@section('sidebar_content')
@persist('sidebar')

<livewire:chat-sidebar :receiverId="isset($receiver) ? $receiver->id : null" />

@endpersist
@endsection


@section('content')
<livewire:chat-component
    :receiverId="isset($receiver) ? $receiver->id : null"
    :key="'chat-' . (isset($receiver) ? $receiver->id : 'empty')" />
@endsection

<script>
    window.addEventListener('refreshSidebar', () => {
    // 300ms ka delay taaki DB update ho chuka ho
    setTimeout(() => {
        Livewire.dispatch('$refresh');
    }, 300);
});
</script>