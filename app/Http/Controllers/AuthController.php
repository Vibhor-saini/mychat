<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Check karein ki email/password sahi hai?
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        // Agar galat hai toh wapas login par bhejein error ke saath
        return back()->with('error', 'Invalid email or password');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }


    public function userIndex()
    {
        $allUsers = User::orderBy('name', 'asc')->get();
        return view('users_index', compact('allUsers'));
    }

    public function userStore(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/dashboard')->with('error', 'Unauthorized action!');
        }
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user' // Hamesha user role hi rahega
        ]);

        return back()->with('success', 'User created successfully!');
    }

    public function dashboard()
    {
        // Abhi ke liye sirf view return karte hain
        return view('dashboard');
    }


    public function startChat($id)
    {
        $receiver = User::findOrFail($id);
        $sender_id = Auth::id();

        // Dono ke beech ke purane messages nikalein
        $messages = Message::where(function ($q) use ($sender_id, $id) {
            $q->where('sender_id', $sender_id)->where('receiver_id', $id);
        })->orWhere(function ($q) use ($sender_id, $id) {
            $q->where('sender_id', $id)->where('receiver_id', $sender_id);
        })->orderBy('created_at', 'asc')->get();

        return view('dashboard', compact('receiver', 'messages'));
    }

    public function sendMessage(Request $request)
    {
        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message
        ]);
        return back();
    }
}
