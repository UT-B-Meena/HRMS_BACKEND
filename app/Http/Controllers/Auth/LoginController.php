<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Session;
    
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
            $user = Auth::user();
            $token = $user->createToken('user_access')->plainTextToken;

            return redirect()->route('dashboard')->with('token', $token); 
        }

        return back()->withErrors([
            'employee_id' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::flush();
        return redirect()->route('login'); 
    }
}
