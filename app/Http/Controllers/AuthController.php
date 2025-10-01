<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Region;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = auth()->user();

            // Pracownik z przypisanym regionem → od razu do skanera
            if ($user->role === 'pracownik' && $user->region_id) {
                return redirect()->route('produkt_skany.index');
            }

            // Pozostali użytkownicy → standardowo
            return redirect()->route('welcome');
        }

        return back()->withErrors([
            'email' => 'Nieprawidłowy email lub hasło.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showRegister()
    {
        $roles = ['pracownik', 'ksiegowy', 'kierownik', 'admin'];
        $regions = Region::all();
        return view('auth.register', compact('roles', 'regions'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
            'role' => ['required', 'in:pracownik,ksiegowy,kierownik,admin'],
            'region_id' => ['nullable', 'exists:regions,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'region_id' => $validated['region_id'] ?? null,
        ]);

        Auth::login($user);

        return redirect()->route('welcome');
    }
}
