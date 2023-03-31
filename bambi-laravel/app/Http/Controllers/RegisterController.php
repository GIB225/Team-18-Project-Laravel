<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Mail\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function index() {
        return view('login');
    }

    public function store(Request $request) {
        //validate user information
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()
            ],
        ]);

        //store user
        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        auth()->attempt($request->only('email', 'password'));
        //Set user id in session
        session(['id' => auth()->user()->id]);
        session(['firstName' => auth()->user()->first_name]);

        try {
            Mail::to($request->email)->queue(new Register($request->first_name, $request->last_name));
        } catch (Exception $exception) {
        }
        return redirect()->route('welcome');
    }
}
