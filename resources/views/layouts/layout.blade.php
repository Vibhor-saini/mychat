<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Teams Clone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --teams-purple: #6264a7; --activity-bg: #33344a; --sidebar-bg: #f5f5f5; --border: #edebe9; }
        body { height: 100vh; overflow: hidden; font-family: 'Segoe UI', sans-serif; background: #ebebeb; }
        .wrapper { display: flex; height: 100vh; width: 100vw; }
        .activity-bar { width: 68px; background: var(--activity-bg); display: flex; flex-direction: column; align-items: center; padding-top: 15px; flex-shrink: 0; }
        .activity-item { color: #d1d1d1; font-size: 1.4rem; margin-bottom: 20px; text-decoration: none; width: 100%; text-align: center; opacity: 0.7; }
        .activity-item.active { color: white; opacity: 1; border-left: 3px solid white; }
        .chat-sidebar { width: 300px; background: var(--sidebar-bg); border-right: 1px solid var(--border); display: flex; flex-direction: column; }
        .main-panel { flex: 1; display: flex; flex-direction: column; background: white; min-width: 0; position: relative; }
        .header-top { height: 48px; display: flex; justify-content: space-between; align-items: center; padding: 0 20px; background: #f3f2f1; border-bottom: 1px solid var(--border); z-index: 1050; }
        .content-area { flex-grow: 1; overflow: hidden; display: flex; flex-direction: column; }
        [wire\:id] { display: flex; flex-direction: column; flex-grow: 1; height: 100%; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <div class="wrapper">
        <div class="activity-bar">
            <a href="{{ route('dashboard') }}" wire:navigate class="activity-item {{ request()->is('dashboard') || request()->is('chat*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i>
            </a>
            <a href="{{ route('users.index') }}" wire:navigate class="activity-item {{ request()->is('users*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
            </a>
            <div class="mt-auto mb-3 text-center">
                <form action="{{ route('logout') }}" method="POST">@csrf
                    <button class="btn btn-link text-white-50 p-0"><i class="bi bi-box-arrow-right fs-4"></i></button>
                </form>
            </div>
        </div>

        @if(!request()->is('users*') || (request()->is('users*') && Auth::user()->role !== 'admin'))
        <div class="chat-sidebar">
            <div class="p-3 shadow-sm bg-white d-flex justify-content-between align-items-center" style="height: 48px;">
                <h6 class="fw-bold mb-0">Chat</h6>
            </div>
            <div class="overflow-auto flex-grow-1">@yield('sidebar_content')</div>
        </div>
        @endif

        <div class="main-panel">
            <header class="header-top">
                <div class="d-flex align-items-center bg-white border rounded px-2" style="height: 30px; width: 340px;">
                    <i class="bi bi-search text-muted small"></i>
                    <input type="text" class="form-control form-control-sm border-0 bg-transparent shadow-none" placeholder="Search">
                </div>
                {{-- Only One Profile Header Here --}}
                <div class="ms-auto">
                    @livewire('user-profile')
                </div>
            </header>
            <main class="content-area">@yield('content')</main>
        </div>
    </div>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.onlineUsersList = window.onlineUsersList || [];
        document.addEventListener('livewire:init', () => {
            let channel = Echo.join('chat-presence');
            const syncOnlineUsers = (users) => {
                window.onlineUsersList = users;
                Livewire.dispatch('online-users-updated', { users: users });
                window.dispatchEvent(new CustomEvent('instant-online-status', { detail: { users: users } }));
            };
            channel.here((users) => syncOnlineUsers(users.map(u => u.id)))
                   .joining((user) => {
                       if (!window.onlineUsersList.includes(user.id)) {
                           window.onlineUsersList.push(user.id);
                           syncOnlineUsers(window.onlineUsersList);
                       }
                   })
                   .leaving((user) => {
                       let newList = window.onlineUsersList.filter(id => id !== user.id);
                       syncOnlineUsers(newList);
                   });
            channel.listenForWhisper('typing', (e) => { Livewire.dispatch('typing-received', { data: e }); });
            
            window.addEventListener('user-typing', () => {
                let compElement = document.querySelector('[wire\\:id]');
                if (compElement) {
                    let comp = Livewire.find(compElement.getAttribute('wire:id'));
                    if (comp && comp.receiverId) {
                        channel.whisper('typing', { sender_id: {{ auth()->id() }}, receiver_id: comp.receiverId, typing: true });
                    }
                }
            });

            window.addEventListener('user-stopped-typing', () => {
                let compElement = document.querySelector('[wire\\:id]');
                if (compElement) {
                    let comp = Livewire.find(compElement.getAttribute('wire:id'));
                    if (comp && comp.receiverId) {  
                        channel.whisper('typing', { sender_id: {{ auth()->id() }}, receiver_id: comp.receiverId, typing: false });
                    }
                }
            });

            document.addEventListener('livewire:navigated', () => {
                Livewire.dispatch('online-users-updated', { users: window.onlineUsersList });
            });
        });
    </script>
</body>
</html>