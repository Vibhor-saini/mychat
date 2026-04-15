@extends('layout')

{{-- 1. Sidebar Content (Only for Normal User) --}}
@if(Auth::user()->role !== 'admin')
    @section('sidebar_content')
        <div class="p-3 text-muted small fw-bold text-uppercase" style="letter-spacing: 1px;">Teammates</div>
        @foreach(App\Models\User::orderBy('name', 'asc')->get() as $u)
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
    @if(Auth::user()->role == 'admin')
        {{-- ADMIN VIEW: Full Management Dashboard --}}
        <div class="chat-header">
            <h5 class="fw-bold"><i class="bi bi-shield-lock-fill me-2 text-primary"></i>Admin: User Management</h5>
        </div>

        <div class="p-4 bg-white h-100" style="overflow-y: auto;">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
            @endif

            <div class="card border-0 shadow-sm mb-4 bg-light">
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Full Name" required></div>
                        <div class="col-md-4"><input type="email" name="email" class="form-control" placeholder="Email Address" required></div>
                        <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                        <div class="col-md-1"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i></button></div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle border">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Member</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Models\User::orderBy('name', 'asc')->get() as $u)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-secondary text-white me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="fw-bold small">{{ $u->name }}</div>
                                </div>
                            </td>
                            <td class="small">{{ $u->email }}</td>
                            <td><span class="badge {{ $u->role == 'admin' ? 'bg-danger' : 'bg-primary' }} opacity-75">{{ ucfirst($u->role) }}</span></td>
                            <td class="text-muted small">{{ $u->created_at->format('d M, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- USER VIEW: Clean Welcome/Placeholder Screen --}}
        <div class="d-flex align-items-center justify-content-center h-100 bg-light">
            <div class="text-center p-5">
                <div class="mb-4">
                    <i class="bi bi-people text-muted opacity-25" style="font-size: 6rem;"></i>
                </div>
                <h3 class="fw-bold text-dark">Team Directory</h3>
                <p class="text-muted mx-auto" style="max-width: 400px;">
                    Find your colleagues in the sidebar to view their profiles or start a private conversation. 
                </p>
                <div class="mt-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-chat-dots me-2"></i> Go to My Chats
                    </a>
                </div>
            </div>
        </div>
    @endif
@endsection