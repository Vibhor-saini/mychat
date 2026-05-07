<div class="dropdown">
    {{-- Header Avatar Trigger --}}
    <div class="d-flex align-items-center" data-bs-toggle="dropdown" style="cursor: pointer;">
        <div class="position-relative">
            @if(auth()->user()->profile_image)
            <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" class="rounded-circle border" style="width: 34px; height: 34px; object-fit: cover;">
            @else
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 34px; height: 34px; font-size: 11px; background-color: #6264a7; border: 1.5px solid #fff;">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            @endif
            {{-- Ye dot DB ke status se sync rahega --}}
            <span class="position-absolute rounded-circle border border-white {{ $availability == 'available' ? 'bg-success' : ($availability == 'busy' ? 'bg-danger' : 'bg-warning') }}" style="width: 10px; height: 10px; bottom: -1px; right: -1px;"></span>
        </div>
    </div>

    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0 overflow-hidden" style="width: 340px; border-radius: 12px;">
        <div class="p-4 text-center bg-light">
            <div class="position-relative d-inline-block group mb-3">
                <div class="rounded-circle shadow-sm overflow-hidden border bg-light d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">

                    {{-- Sirf image upload ke waqt dikhega --}}
                    <div wire:loading wire:target="image" class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75" style="z-index: 10;">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    </div>

                    @if(auth()->user()->profile_image)
                    <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" class="w-100 h-100" style="object-fit: cover;">
                    @else
                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-info text-white fw-bold fs-3">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    @endif
                </div>

                {{-- Teams Style Camera Overlay --}}
                <label for="p-up" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center rounded-circle bg-dark bg-opacity-0 hover-camera transition-all cursor-pointer" style="z-index: 5;">
                    <i class="bi bi-camera text-white fs-4 opacity-0 camera-icon"></i>
                    <input type="file" id="p-up" wire:model.live="image" class="d-none">
                </label>
            </div>
            <h6 class="fw-bold mb-0 text-dark">{{ auth()->user()->name }}</h6>
            <p class="text-muted small mb-0">{{ auth()->user()->email }}</p>
        </div>

        {{-- AVAILABILITY BUTTONS --}}
        <div class="px-3 py-3 border-bottom">
            <label class="small fw-bold text-muted mb-2 d-block text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Availability</label>
            <div class="d-flex gap-1">
                <button wire:click="updateAvailability('available')" class="btn btn-sm flex-grow-1 border {{ $availability == 'available' ? 'btn-success text-white' : 'btn-light' }}">Available</button>
                <button wire:click="updateAvailability('busy')" class="btn btn-sm flex-grow-1 border {{ $availability == 'busy' ? 'btn-danger text-white' : 'btn-light' }}">Busy</button>
                <button wire:click="updateAvailability('away')" class="btn btn-sm flex-grow-1 border {{ $availability == 'away' ? 'btn-warning text-dark' : 'btn-light' }}">Away</button>
            </div>
        </div>

        {{-- STATUS MESSAGE --}}
<div class="p-3 bg-white">
    <label class="small fw-bold text-muted mb-2 d-block text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Status Message</label>
    
    @if($isEditingStatus)
        {{-- Edit Mode --}}
        <div class="d-flex flex-column gap-2">
            {{-- wire:model.defer se text box mein data bana rahega --}}
            <textarea wire:model="status_message" class="form-control form-control-sm" rows="2" placeholder="What's happening?"></textarea>
            <div class="d-flex justify-content-end gap-2">
                <button wire:click="$set('isEditingStatus', false)" class="btn btn-sm btn-light border">Cancel</button>
                <button wire:click="saveStatus" class="btn btn-sm btn-primary px-3">Save</button>
            </div>
        </div>
    @else
        {{-- Display Mode --}}
        <div class="p-2 bg-light rounded border d-flex justify-content-between align-items-center">
            <span class="small text-muted text-truncate me-2">
                {{ auth()->user()->status_message ?? 'Set a status message' }}
            </span>
            {{-- Pencil click par current message textarea mein load ho jayega --}}
            <i wire:click="$set('isEditingStatus', true)" class="bi bi-pencil-square cursor-pointer text-primary" style="font-size: 1.1rem;"></i>
        </div>
    @endif
</div>

        <div class="p-2 bg-light text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-none fw-bold">Sign Out</button>
            </form>
        </div>
    </div>

    <style>
        .hover-camera:hover {
            background-color: rgba(0, 0, 0, 0.4) !important;
        }

        .hover-camera:hover .camera-icon {
            opacity: 1 !important;
        }

        .transition-all {
            transition: 0.25s ease-in-out;
        }
    </style>
</div>