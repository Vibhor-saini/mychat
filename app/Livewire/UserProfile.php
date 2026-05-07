<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserProfile extends Component
{
    use WithFileUploads;

    public $image;
    public $availability;
    public $status_message;
    public $isEditingStatus = false;

    public function mount()
    {
        $user = Auth::user();
        $this->availability = $user->availability ?? 'available';
        $this->status_message = $user->status_message;
    }

    // Photo Upload Logic
    public function updatedImage()
    {
        $this->validate(['image' => 'image|max:2048']);
        $user = Auth::user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $path = $this->image->store('profile-photos', 'public');
        $user->update(['profile_image' => $path]);

        $this->reset('image');
        // Page refresh ki zaroorat nahi, sirf UI ko update karenge
        $this->dispatch('profile-updated'); 
    }

    // DB mein Availability set karna
    public function updateAvailability($status)
    {
        $this->availability = $status;
        Auth::user()->update(['availability' => $status]);
        
        // Isse Sidebar aur Header ke dots sync honge
        $this->dispatch('status-updated', status: $status);
    }

public function saveStatus()
{
    // DB update
    Auth::user()->update(['status_message' => $this->status_message]);
    
    // UI reset
    $this->isEditingStatus = false;
    
    // Optional: Sidebar ko batane ke liye
    $this->dispatch('status-updated');
}

    public function render()
    {
        return view('livewire.user-profile');
    }
}