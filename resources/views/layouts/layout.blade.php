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
        :root {
            --activity-bg: #ebebeb;
            --sidebar-bg: #f5f5f5;
            --chat-active: #ffffff;
            --teams-purple: #6264a7;
        }

        /* 1. Body ko lock karna taaki layout bahr na jaye */
        body {
            height: 100vh;
            width: 100vw;
            margin: 0;
            overflow: hidden; 
            display: flex;
            flex-direction: column; /* Vertical stacking: Header then Wrapper */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* 2. Header Fix */
        header {
            height: 48px;
            flex-shrink: 0; /* Header apni height se chota nahi hoga */
            z-index: 1050;
        }

        /* 3. Wrapper bachi hui poori screen lega */
        .wrapper {
            display: flex;
            flex-grow: 1; 
            overflow: hidden; 
        }

        .activity-bar {
            width: 68px;
            background: var(--activity-bg);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            border-right: 1px solid #ddd;
            flex-shrink: 0;
        }

        .chat-sidebar {
            width: 300px;
            background: var(--sidebar-bg);
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .main-panel {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: white;
            overflow: hidden; 
        }

        /* Activity Items Styling */
        .activity-item {
            color: #616161;
            font-size: 1.5rem;
            margin-bottom: 25px;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .activity-item.active {
            color: var(--teams-purple);
            border-left: 3px solid var(--teams-purple);
        }

        .activity-item span {
            font-size: 0.65rem;
            font-weight: 500;
        }

        /* Essential for Livewire SPA feel */
        [wire\:id] {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            height: 100%;
        }

        /* Custom Scrollbar */
        .overflow-auto::-webkit-scrollbar { width: 6px; }
        .overflow-auto::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body>

    <header class="d-flex align-items-center justify-content-between px-3 bg-white border-bottom shadow-sm">
        <div class="d-flex align-items-center" style="width: 250px;">
            <i class="bi bi-chat-dots-fill text-primary fs-5 me-2"></i>
            <span class="fw-bold" style="font-size: 0.9rem;">ChatHub</span>
        </div>

        <div class="flex-grow-1 d-flex justify-content-center">
            <div class="position-relative" style="width: 100%; max-width: 500px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="font-size: 0.8rem;"></i>
                <input type="text"
                    class="form-control form-control-sm bg-light border-0 ps-4 shadow-none"
                    placeholder="Search"
                    style="border-radius: 4px; height: 28px; font-size: 0.85rem;">
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end" style="width: 250px;">
            <livewire:profile-header />
        </div>
    </header>

    <div class="wrapper">
        <div class="activity-bar">
            <a href="{{ route('dashboard') }}" wire:navigate class="activity-item {{ request()->is('dashboard') || request()->is('chat*') ? 'active' : '' }}">
                <i class="bi bi-chat-fill"></i>
                <span>Chat</span>
            </a>
            <a href="{{ route('users.index') }}" wire:navigate class="activity-item {{ request()->is('users*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>People</span>
            </a>

            <div class="mt-auto mb-3 text-center">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-link text-dark p-0"><i class="bi bi-box-arrow-right fs-5"></i></button>
                </form>
            </div>
        </div>

        @if(!request()->is('users*') || (request()->is('users*') && Auth::user()->role !== 'admin'))
        <div class="chat-sidebar">
            <div class="p-3 shadow-sm bg-white d-flex justify-content-between align-items-center" style="height: 55px; flex-shrink: 0;">
                <h5 class="fw-bold mb-0" style="font-size: 1.1rem;">
                    {{ request()->is('users*') ? 'People' : 'Chat' }}
                </h5>
                <i class="bi {{ request()->is('users*') ? 'bi-search' : 'bi-pencil-square' }}"></i>
            </div>
            <div class="overflow-auto flex-grow-1">
                @yield('sidebar_content')
            </div>
        </div>
        @endif

        <div class="main-panel">
            @yield('content')
        </div>
    </div>

    @livewireScripts
</body>

</html>