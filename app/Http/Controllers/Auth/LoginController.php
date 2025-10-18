<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'employee_id' => 'required',
            'password' => 'required',
        ]);

        // First, check if the user exists and verify password
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Check if the account is deactivated
            if ($user->status == 0) {
                Auth::logout(); // Log out the user immediately

                return back()->withErrors([
                    'municipal_id' => 'Your account has been deactivated. Please contact the administrator for assistance.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'municipal_id' => 'Invalid Credentials',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
