<div class="dropdown d-flex align-items-center">
    <a href="#" role="button" data-bs-toggle="dropdown" class="text-decoration-none shadow-none">
        <div class="position-relative" style="width: 32px; height: 32px;">
            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center border" 
                 style="width: 32px; height: 32px; font-size: 0.75rem; font-weight: 600;">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            
            {{-- Status Dot --}}
            <span class="position-absolute border border-white rounded-circle {{ $availability == 'available' ? 'bg-success' : ($availability == 'busy' ? 'bg-danger' : 'bg-warning') }}" 
                style="width: 10px; height: 10px; bottom: -1px; right: -1px;"></span>
        </div>
    </a>

    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3 mt-2" style="width: 300px; border-radius: 8px;" wire:ignore.self>
        
        {{-- 1. Header: Name & Sign Out --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fw-bold small text-muted">Personal</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-link btn-sm text-decoration-none text-dark p-0 fw-semibold" style="font-size: 0.8rem;">Sign out</button>
            </form>
        </div>

        {{-- 2. User Profile Info --}}
        <div class="d-flex align-items-center mb-3">
            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center border me-3" 
                 style="width: 50px; height: 50px; font-size: 1.2rem;">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="text-truncate">
                <div class="fw-bold text-dark text-truncate" style="font-size: 0.95rem;">{{ auth()->user()->name }}</div>
                <div class="text-muted small text-truncate">{{ auth()->user()->email }}</div>
            </div>
        </div>

        {{-- 3. Availability Selector --}}
        <div class="dropdown mb-3">
            <button class="btn btn-outline-light border text-dark w-100 d-flex justify-content-between align-items-center py-2" 
                    style="font-size: 0.85rem;" data-bs-toggle="dropdown">
                <span>
                    <i class="bi bi-circle-fill me-2 {{ $availability == 'available' ? 'text-success' : ($availability == 'busy' ? 'text-danger' : 'text-warning') }}"></i>
                    {{ ucfirst($availability) }}
                </span>
                <i class="bi bi-chevron-right small"></i>
            </button>
            <ul class="dropdown-menu w-100 shadow-sm border-0 mt-1">
                <li><a class="dropdown-item py-2" href="#" wire:click.prevent="updateAvailability('available')"><i class="bi bi-circle-fill text-success me-2"></i> Available</a></li>
                <li><a class="dropdown-item py-2" href="#" wire:click.prevent="updateAvailability('busy')"><i class="bi bi-circle-fill text-danger me-2"></i> Busy</a></li>
                <li><a class="dropdown-item py-2" href="#" wire:click.prevent="updateAvailability('away')"><i class="bi bi-circle-fill text-warning me-2"></i> Away</a></li>
            </ul>
        </div>

        {{-- 4. Status Message (Editable) --}}
        <div class="bg-light p-2 rounded position-relative" style="min-height: 60px;">
            @if($isEditing)
                <div class="d-flex flex-column gap-2">
                    <input type="text" wire:model.lazy="status_message" class="form-control form-control-sm border-0 bg-white" placeholder="Set a status..." autofocus>
                    <div class="d-flex justify-content-end gap-1">
                        <button wire:click="saveStatus" class="btn btn-primary btn-sm px-2 py-0" style="font-size: 0.75rem;">Save</button>
                        <button wire:click="$set('isEditing', false)" class="btn btn-light btn-sm px-2 py-0 border" style="font-size: 0.75rem;">Cancel</button>
                    </div>
                </div>
            @else
                <p class="small text-muted mb-0 pe-4" style="line-height: 1.4;">
                    {{ $status_message ?? 'Add a personal status message' }}
                </p>
                <div class="position-absolute top-0 end-0 p-1">
                    <button wire:click="$set('isEditing', true)" class="btn btn-sm text-muted border-0"><i class="bi bi-pencil small"></i></button>
                </div>
                @if($status_message)
                <div class="position-absolute bottom-0 end-0 p-1">
                    <button wire:click="clearStatus" class="btn btn-sm text-danger border-0"><i class="bi bi-trash small"></i></button>
                </div>
                @endif
            @endif
        </div>

    </div>
</div>