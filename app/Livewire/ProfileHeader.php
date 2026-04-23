<?php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ProfileHeader extends Component
{
    public $status_message;
    public $availability;
    public $isEditing = false;

    public function mount()
    {
        $this->status_message = Auth::user()->status_message;
        $this->availability = Auth::user()->availability;
    }

    // Availability status change (Green, Red, Yellow dots)
    public function updateAvailability($status)
    {
        Auth::user()->update(['availability' => $status]);
        $this->availability = $status;
        
        // Sabko batane ke liye ki mera status badal gaya hai
        $this->dispatch('status-updated', userId: Auth::id(), status: $status);
    }

    // Status message (Text) update
    public function saveStatus()
    {
        Auth::user()->update(['status_message' => $this->status_message]);
        $this->isEditing = false;
        session()->flash('message', 'Status updated!');
    }

    public function render()
    {
        return view('livewire.profile-header');
    }
}