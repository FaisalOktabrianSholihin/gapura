<?php
namespace App\Http\Controllers;
use App\Support\Aktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function form() { return view('auth.login'); }

    public function login(Request $r)
    {
        $c = $r->validate(['nik' => 'required', 'password' => 'required']);
        if (Auth::attempt($c, $r->boolean('remember'))) {
            $r->session()->regenerate();
            Aktivitas::catat('login', 'Auth', 'Masuk ke sistem');
            return redirect()->intended(route('home'));
        }
        return back()->withErrors(['nik' => 'Email/NIK atau password tidak cocok.'])->onlyInput('nik');
    }

    public function logout(Request $r)
    {
        Aktivitas::catat('logout', 'Auth', 'Keluar dari sistem');
        Auth::logout(); $r->session()->invalidate(); $r->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function password(Request $r)
    {
        $r->validate(['password' => 'required|min:6|confirmed']);
        $r->user()->update(['password' => $r->password]);
        Aktivitas::catat('ubah', 'Auth', 'Mengganti password sendiri');
        return back()->with('ok', 'Password berhasil diganti.');
    }
}
