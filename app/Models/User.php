<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship to get all messages where this user is the sender or receiver
     */
    public function messages()
    {
        return $this->hasMany(\App\Models\Message::class, 'sender_id')
            ->orWhere('receiver_id', $this->id);
    }

    /**
     * Get the single latest message exchanged with the authenticated user
     */
/**
 * Get the single latest message exchanged with the authenticated user.
 */
public function getLatestMessageAttribute()
{
    $authId = Auth::id();
    if (!$authId) return null;

    // Adding fresh() ensures we get the newest data from the DB
    return \App\Models\Message::where(function ($q) use ($authId) {
        $q->where('sender_id', $authId)->where('receiver_id', $this->id);
    })->orWhere(function ($q) use ($authId) {
        $q->where('sender_id', $this->id)->where('receiver_id', $authId);
    })->latest()->first();
}
}
