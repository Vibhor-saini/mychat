@extends('layouts.layout')

{{-- 1. Sidebar Content (Modern Teams Style) --}}
@if(Auth::user()->role !== 'admin')
    @section('sidebar_content')
        <div class="chat-sidebar-list overflow-auto" style="background: #f5f5f5; height: 100%;">
            <div class="px-3 py-3 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">
                Teammates
            </div>
            
            <div class="px-2">
                @foreach(App\Models\User::where('id', '!=', Auth::id())->orderBy('name', 'asc')->get() as $u)
                    <a href="{{ route('chat.start', $u->id) }}" wire:navigate
                       class="d-flex align-items-center px-3 py-2 text-decoration-none rounded-2 mb-1 teammate-link {{ isset($receiver) && $receiver->id == $u->id ? 'active' : '' }}">
                        
                        {{-- Avatar with Online status --}}
<div class="position-relative me-3">
    @php
        $bgColors = ['#6264a7', '#2da3ba', '#f29b11', '#e84c3d'];
        $userBg = $bgColors[$u->id % count($bgColors)];
    @endphp
    
    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
         style="width: 36px; height: 36px; background-color: {{ $userBg }}; font-size: 0.8rem;">
        {{ strtoupper(substr($u->name, 0, 1)) }}
    </div>

    {{-- Presence Indicator using Alpine.js (No PHP Error) --}}
    <span class="position-absolute border border-2 border-white rounded-circle"
          x-data="{ online: window.onlineUsersList?.includes({{ $u->id }}) || false }"
          x-init="window.addEventListener('online-users-updated', (e) => { online = e.detail.users.includes({{ $u->id }}) })"
          :class="online ? 'bg-success' : 'bg-secondary'"
          style="width: 11px; height: 11px; bottom: 0; right: 0;">
    </span>
</div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-bold text-dark small text-truncate">{{ $u->name }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ ucfirst($u->role) }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <style>
            .teammate-link:hover { background-color: #edebe9; }
            .teammate-link.active { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        </style>
    @endsection
@endif

{{-- 2. Main Content Area --}}
@section('content')
    <style>
        .admin-stat-card { border-radius: 8px; transition: all 0.2s ease; border: 1px solid #edebe9; background: #fff; }
        .admin-stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important; }
        .table-container { border-radius: 8px; overflow: hidden; border: 1px solid #edebe9; background: #fff; }
        .avatar-initials { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 0.75rem; }
        .btn-teams-primary { background-color: #6264a7; border: none; color: white; padding: 5px 15px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; }
        .btn-teams-primary:hover { background-color: #464775; color: white; }
        .status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; border: 2px solid #fff; }
    </style>

    @if(Auth::user()->role == 'admin')
        {{-- ADMIN CONSOLE --}}
        <div class="d-flex flex-column h-100 bg-light">
            <div class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.1rem;">Admin Console</h5>
                    <p class="text-muted small mb-0">Manage organization members and system status</p>
                </div>
                <div class="text-muted small fw-semibold">
                    <i class="bi bi-cpu me-1"></i> System Active
                </div>
            </div>

            <div class="p-4 overflow-auto">
                {{-- Stats Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card admin-stat-card p-3 shadow-sm">
                            <div class="d-flex align-items-center">
                                <div class="p-3 bg-light rounded-3 me-3 text-primary"><i class="bi bi-people fs-4"></i></div>
                                <div>
                                    <h4 class="fw-bold mb-0 text-dark">{{ App\Models\User::count() }}</h4>
                                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.6rem;">Total Members</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- More cards can go here --}}
                </div>

                {{-- Action Bar: Add Member --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 8px;">
                    <div class="card-body p-3">
                        <h6 class="fw-bold small mb-3 text-muted">ADD NEW MEMBER</h6>
                        <form action="{{ route('users.store') }}" method="POST" class="row g-2">
                            @csrf
                            <div class="col-md-3"><input type="text" name="name" class="form-control form-control-sm border" placeholder="Name" required></div>
                            <div class="col-md-4"><input type="email" name="email" class="form-control form-control-sm border" placeholder="Email" required></div>
                            <div class="col-md-3"><input type="password" name="password" class="form-control form-control-sm border" placeholder="Password" required></div>
                            <div class="col-md-2"><button type="submit" class="btn btn-teams-primary w-100">Add</button></div>
                        </form>
                    </div>
                </div>

                {{-- Member Table --}}
                <div class="table-container shadow-sm">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th class="ps-4 py-3 text-muted small fw-bold">MEMBER</th>
                                <th class="py-3 text-muted small fw-bold">ROLE</th>
                                <th class="py-3 text-muted small fw-bold text-end pe-4">JOINED</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(App\Models\User::orderBy('name', 'asc')->get() as $u)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        @php $bg = ['#6264a7', '#2da3ba', '#f29b11', '#e84c3d'][$u->id % 4]; @endphp
                                        <div class="avatar-initials me-3" style="background-color: {{ $bg }};">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $u->name }}</div>
                                            <div class="text-muted small">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $u->role == 'admin' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary' }} px-2 py-1 rounded-1 fw-bold" style="font-size: 0.65rem;">
                                        {{ strtoupper($u->role) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4 text-muted small">{{ $u->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        {{-- USER EMPTY STATE --}}
        <div class="d-flex align-items-center justify-content-center h-100 bg-white shadow-sm mx-3 my-3 border rounded-3">
            <div class="text-center p-5">
                <div class="mb-4">
                    <i class="bi bi-people text-muted opacity-25" style="font-size: 5rem;"></i>
                </div>
                <h3 class="fw-bold text-dark">Organization Directory</h3>
                <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">Connect with your teammates across the organization. Select a profile from the sidebar to start a chat.</p>
                <a href="{{ route('dashboard') }}" wire:navigate class="btn btn-teams-primary px-4 py-2">Back to Conversations</a>
            </div>
        </div>
    @endif
@endsection