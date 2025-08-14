<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Satpam;
use App\Models\RecentActivity;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = \App\Models\Satpam::where('username', $request->username)->first();

        if ($user && \Hash::check($request->password, $user->password)) {
            Auth::login($user);

            // Simpan aktivitas login
            if ($user->role === 'Admin') {
                \App\Models\RecentActivity::create([
                    'user_id' => Auth::id(),
                    'description' => 'Login ke sistem',
                    'severity' => 'info'
                ]);
            }

            // Redirect berdasarkan role
            if ($user->role === 'Admin') {
                return redirect()->route('dashboard.admin');
            } elseif ($user->role === 'Satpam') {
                return redirect()->route('dashboard.satpam');
            } elseif ($user->role === 'Kepala Satpam') {
                return redirect()->route('dashboard.kepala'); // nanti jika dibuat
            }

            // Default jika role tidak dikenali
            return redirect('/login')->withErrors(['username' => 'Role tidak dikenali.']);
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ]);
    }
}