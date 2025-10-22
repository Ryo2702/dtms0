<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

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

        $key = $this->throttleKey($request);
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            
            return back()->withErrors([
                'employee_id' => 'Too many login attempts. Please try again in ' . gmdate('i:s', $seconds) . ' minutes.'
            ])->withInput($request->only('employee_id'));
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            $request->session()->regenerate();
            
            RateLimiter::clear($key);

            return redirect()->route('dashboard');
        }

        // Increment failed attempts
        RateLimiter::hit($key, 300); // 300 seconds = 5 minutes

        return back()->withErrors([
            'employee_id' => 'Invalid Credentials',
        ])->withInput($request->only('employee_id'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function throttleKey(Request $request)
    {
        return strtolower($request->input('employee_id')) . '|' . $request->ip();
    }

    public function getRemainingTime(Request $request)
    {
        $key = $this->throttleKey($request);
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json([
                'locked' => true,
                'remaining_time' => RateLimiter::availableIn($key)
            ]);
        }

        return response()->json([
            'locked' => false,
            'attempts' => RateLimiter::attempts($key)
        ]);
    }
}
