<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
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

        body {
            height: 100vh;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .wrapper {
            display: flex;
            height: 100vh;
        }

        /* 1. Activity Bar (Extreme Left) */
        .activity-bar {
            width: 68px;
            background: var(--activity-bg);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            border-right: 1px solid #ddd;
        }

        .activity-item {
            color: #616161;
            font-size: 1.5rem;
            margin-bottom: 25px;
            cursor: pointer;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
        }

        .activity-item.active {
            color: var(--teams-purple);
            border-left: 3px solid var(--teams-purple);
            width: 100%;
        }

        .activity-item span {
            font-size: 0.65rem;
            font-weight: 500;
        }

        /* 2. Chat Sidebar (Middle) */
        .chat-sidebar {
            width: 300px;
            background: var(--sidebar-bg);
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
        }

        .chat-list-item {
            padding: 15px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            text-decoration: none;
            color: black;
        }

        .chat-list-item:hover {
            background: #e0e0e0;
        }

        .chat-list-item.active {
            background: var(--chat-active);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* 3. Main Chat Area (Right) */
        .main-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 15px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f0f0f0;
        }

        .chat-footer {
            padding: 20px;
            border-top: 1px solid #eee;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            margin-right: 12px;
        }

        /* //=============== */
        /* Layout ki style tag mein ye check karein */
        .chat-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            /* Full screen height */
            background-color: #f8f9fa;
        }

        /* Taaki Livewire ka div bhi poori height le */
        div[livewire\:id],
        [wire\:id] {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            height: 100%;
        }
    </style>
    @livewireStyles
</head>

<body>

    <div class="wrapper">
        <div class="activity-bar">
            {{-- Chat Icon --}}
            <a href="{{ route('dashboard') }}" class="activity-item {{ request()->is('dashboard') || request()->is('chat*') ? 'active' : '' }}">
                <i class="bi bi-chat-fill"></i>
                <span>Chat</span>
            </a>
            <a href="{{ route('users.index') }}" class="activity-item {{ request()->is('users*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>People</span>
            </a>
            <!-- <a href="#" class="activity-item"><i class="bi bi-calendar-event"></i><span>Meet</span></a> -->
            <div class="mt-auto mb-3">
                <form action="{{ route('logout') }}" method="POST">@csrf
                    <button class="btn btn-link text-dark"><i class="bi bi-box-arrow-right"></i></button>
                </form>
            </div>
        </div>

        {{-- Agar URL mein 'users' nahi hai, tabhi ye sidebar dikhao --}}
        @if(!request()->is('users*') || (request()->is('users*') && Auth::user()->role !== 'admin'))
        <div class="chat-sidebar">
            <div class="p-3 shadow-sm bg-white d-flex justify-content-between">
                <h5 class="fw-bold mb-0">
                    {{ request()->is('users*') ? 'People' : 'Chat' }}
                </h5>
                <i class="bi {{ request()->is('users*') ? 'bi-search' : 'bi-pencil-square' }}"></i>
            </div>
            <div class="overflow-auto">
                @yield('sidebar_content')
            </div>
        </div>
        @endif

        <div class="main-panel">
            @yield('content') {{-- Yahan chat messages dikhenge --}}
        </div>
    </div>
    @livewireScripts
</body>

</html>