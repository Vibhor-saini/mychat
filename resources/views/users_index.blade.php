@extends('layout')

@section('content')
    <div class="chat-header">
        <h5 class="fw-bold"><i class="bi bi-people-fill me-2"></i>Team Directory</h5>
    </div>

    <div class="p-4 bg-white h-100" style="overflow-y: auto;">
        
        {{-- Form: Sirf Admin ko dikhega --}}
        @if(Auth::user()->role == 'admin')
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
        @else
            {{-- Normal user ko ek welcome message dikha sakte hain --}}
            <div class="alert alert-info border-0 shadow-sm mb-4">
                <i class="bi bi-info-circle me-2"></i> View all your teammates below. To start a chat, go to the Chat tab.
            </div>
        @endif

        {{-- Table: Ye Sabko dikhegi --}}
        <div class="table-responsive">
            <table class="table align-middle border">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Member</th>
                        <th>Email</th>
                        <th>Status</th>
                        @if(Auth::user()->role == 'admin') <th>Joined</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach(App\Models\User::orderBy('name', 'asc')->get() as $u)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-secondary text-white me-2" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $u->name }}</div>
                                    <div class="small text-muted">{{ ucfirst($u->role) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge bg-success-subtle text-success border border-success-subtle">Member</span></td>
                        @if(Auth::user()->role == 'admin')
                            <td class="text-muted">{{ $u->created_at->format('d M, Y') }}</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection