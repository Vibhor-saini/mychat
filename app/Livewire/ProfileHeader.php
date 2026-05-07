<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileHeader extends Component
{
    use WithFileUploads;

    public $availability;
    public $image; // Linked to wire:model="image"

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        $this->availability = Auth::user()->availability ?? 'available';
    }

    public function updateAvailability($status)
    {
        $this->availability = $status;
        Auth::user()->update(['availability' => $status]);
        $this->dispatch('refreshComponent');
    }

    public function render()
    {
        
        return view('livewire.profile-header');
    }
}
