<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
    
class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['employee_id' => $request->employee_id, 'password' => $request->password])) {
            session(['user_id' => Auth::id()]);
            return redirect()->route('dashboard'); 
        }

        return back()->withErrors([
            'employee_id' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('login'); 
    }
}
