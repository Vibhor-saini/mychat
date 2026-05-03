@extends('layouts.layout')

{{-- 1. Sidebar Content (Only for Normal User) --}}
@if(Auth::user()->role !== 'admin')
    @section('sidebar_content')
        <div class="p-3 text-muted small fw-bold text-uppercase" style="letter-spacing: 1px;">Teammates</div>
        @foreach(App\Models\User::where('id', '!=', Auth::id())->orderBy('name', 'asc')->get() as $u)
            <a href="{{ route('chat.start', $u->id) }}" 
               class="chat-list-item {{ isset($receiver) && $receiver->id == $u->id ? 'active' : '' }}">
                <div class="avatar bg-info text-white">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                <div>
                    <div class="fw-bold text-dark small">{{ $u->name }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">{{ ucfirst($u->role) }}</div>
                </div>
            </a>
        @endforeach
    @endsection
@endif

{{-- 2. Main Content Area --}}
@section('content')
    <style>
        .admin-stat-card { border-radius: 12px; transition: all 0.3s ease; border: none; background: #fff; }
        .admin-stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.06) !important; }
        .table-container { border-radius: 12px; overflow: hidden; border: 1px solid #edebe9; }
        .avatar-initials { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; }
        .btn-teams-sm { background-color: #6264a7; border: none; color: white; padding: 6px 12px; border-radius: 4px; transition: 0.2s; }
        .btn-teams-sm:hover { background-color: #464775; color: white; }
        .status-indicator { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .bg-offline { background-color: #a1a1a1; }
    </style>

    @if(Auth::user()->role == 'admin')
        {{-- ADMIN VIEW --}}
        <div class="chat-header d-flex justify-content-between align-items-center bg-white border-bottom px-4 py-3">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Admin Console</h5>
                <p class="text-muted small mb-0">Platform performance and member directory</p>
            </div>
            <div>
                <span class="text-muted small fw-bold">
                    <i class="bi bi-clock-history me-1"></i> System Time: {{ date('h:i A') }}
                </span>
            </div>
        </div>

        <div class="p-4 bg-light h-100" style="overflow-y: auto;">
            
            {{-- Corrected Stats Section --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card admin-stat-card shadow-sm p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-primary bg-opacity-10 rounded-3 me-3 text-primary">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-dark">{{ App\Models\User::count() }}</h4>
                                <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Total Members</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card admin-stat-card shadow-sm p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-warning bg-opacity-10 rounded-3 me-3 text-warning">
                                <i class="bi bi-collection-play-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center">
                                    <h4 class="fw-bold mb-0 text-dark">0</h4>
                                    <span class="badge bg-warning text-dark ms-2" style="font-size: 0.5rem;">UPCOMING</span>
                                </div>
                                <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Team Groups</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card admin-stat-card shadow-sm p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-success bg-opacity-10 rounded-3 me-3 text-success">
                                <i class="bi bi-cpu-fill fs-4"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-dark">
                                    @php
                                        // Real server load check for "Optimized" status
                                        $load = function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 0;
                                        echo ($load < 1.0) ? 'Optimized' : 'Active';
                                    @endphp
                                </h4>
                                <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Server Engine Status</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Bar --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <form action="{{ route('users.store') }}" method="POST" class="row g-2 align-items-center">
                        @csrf
                        <div class="col-md-3"><input type="text" name="name" class="form-control form-control-sm bg-light border-0" placeholder="Full Name" required></div>
                        <div class="col-md-4"><input type="email" name="email" class="form-control form-control-sm bg-light border-0" placeholder="Work Email" required></div>
                        <div class="col-md-3"><input type="password" name="password" class="form-control form-control-sm bg-light border-0" placeholder="Set Password" required></div>
                        <div class="col-md-2"><button type="submit" class="btn btn-teams-sm w-100 fw-bold shadow-sm">Add Member</button></div>
                    </form>
                </div>
            </div>

            {{-- User Table with Presence Logic --}}
            <div class="table-container bg-white shadow-sm">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold">MEMBER</th>
                            <th class="py-3 text-muted small fw-bold">EMAIL ADDRESS</th>
                            <th class="py-3 text-muted small fw-bold text-center">ROLE</th>
                            <th class="py-3 text-muted small fw-bold text-end pe-4">JOINED DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Models\User::orderBy('name', 'asc')->get() as $u)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    @php
                                        $colors = ['#6264a7', '#2da3ba', '#f29b11', '#e84c3d', '#27ae60'];
                                        $bg = $colors[$u->id % count($colors)];
                                        $isOnline = Cache::has('user-is-online-' . $u->id); 
                                    @endphp
                                    <div class="avatar-initials me-3 shadow-sm" style="background-color: {{ $bg }};">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $u->name }}</div>
                                        <div class="text-muted small">
                                            <span class="status-indicator {{ $isOnline ? 'bg-success' : 'bg-offline' }}"></span> 
                                            {{ $isOnline ? 'Online' : 'Offline' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-secondary small">{{ $u->email }}</td>
                            <td class="text-center">
                                <span class="badge {{ $u->role == 'admin' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary' }} px-3 py-2 rounded-pill fw-bold" style="font-size: 0.65rem;">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>
                            <td class="text-end pe-4 text-muted small">{{ $u->created_at->format('d M, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- USER VIEW --}}
        <div class="d-flex align-items-center justify-content-center h-100 bg-white">
            <div class="text-center p-5">
                <div class="p-5 bg-light rounded-circle d-inline-block mb-4">
                    <i class="bi bi-people text-primary opacity-50" style="font-size: 4rem;"></i>
                </div>
                <h3 class="fw-bold text-dark">Global Directory</h3>
                <p class="text-muted mb-4">Access the organization member list. Contact an admin to request account changes.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-teams-sm px-4 py-2">Back to Conversations</a>
            </div>
        </div>
    @endif
@endsection

