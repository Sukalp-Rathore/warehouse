<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    //
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
        $user = User::where('username', $request->username)->first();
        if (!$user) {
            return response()->json(['error' => 'Username does not exist.'], 422);
        }
    
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 422);
        }
        Session::put('user', $user);
        Session::put('user_id', $user->_id);
        Session::put('user_name', $user->username);
        $userId = Session::get('user_id');
        Auth::loginUsingId($userId);
        
        return response()->json(['success' => true , 'message' => 'Login Successfull'], 200);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::flush();
        return redirect()->route('login');
    }
}
