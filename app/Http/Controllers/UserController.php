<?php

namespace App\Http\Controllers;

use App\Models\{Karyawan, Role, User};
use App\Support\Aktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $r)
    {
        $users = User::with('peran')
            ->when($r->q, fn($q, $v) => $q->where(fn($w) => $w->where('name', 'like', "%$v%")->orWhere('nik', 'like', "%$v%")))
            ->when($r->role_id, fn($q, $v) => $q->where('role_id', $v))
            ->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.users-index', ['users' => $users, 'roles' => Role::orderBy('id')->get()]);
    }

    private function form(User $user)
    {
        return view('admin.users-form', [
            'user' => $user,
            'roles' => Role::orderBy('id')->get(),
            'karyawans' => Karyawan::orderBy('nama')->get(['nik', 'nama'])->pluck('nama', 'nik')
        ]);
    }

    public function create()
    {
        return $this->form(new User);
    }
    public function edit(User $user)
    {
        return $this->form($user);
    }

    private function simpan(Request $r, User $user)
    {
        $r->validate([
            'nik' => ['required', 'string', 'max:100', Rule::unique('akun_users', 'nik')->ignore($user->id)],
            'name' => 'required|string|max:150',
            'role_id' => 'required|exists:roles,id',
            'password' => ($user->exists ? 'nullable' : 'required') . '|min:6',
        ], ['nik.unique' => 'Username (NIK/email) sudah dipakai.']);
        $user->nik = $r->nik;
        $user->name = $r->name;
        $user->karyawan_id = Karyawan::where('nik', $r->nik)->value('id');
        if (!($user->exists && $user->id === auth()->id())) $user->role_id = $r->role_id; // tidak bisa mengubah role sendiri
        if ($r->filled('password')) $user->aturPassword($r->password);
        $user->save();
        return $user;
    }

    public function store(Request $r)
    {
        $u = $this->simpan($r, new User);
        Aktivitas::catat('buat', 'User', "Membuat user {$u->name} ({$u->nik})");
        return redirect()->route('users.index')->with('ok', 'User dibuat.');
    }

    public function update(Request $r, User $user)
    {
        $this->simpan($r, $user);
        Aktivitas::catat('ubah', 'User', "Mengubah user {$user->name} ({$user->nik})");
        return redirect()->route('users.index')->with('ok', 'User diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->withErrors(['user' => 'Anda tidak bisa menghapus akun sendiri.']);
        Aktivitas::catat('hapus', 'User', "Menghapus user {$user->name} ({$user->nik})");
        $user->delete();
        return back()->with('ok', 'User dihapus.');
    }

    // Khusus Super Admin: tampilkan password user (didekripsi dari salinan terenkripsi). Setiap akses dicatat di Log Aktivitas.
    public function lihatPassword(User $user)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Khusus Super Admin.');
        Aktivitas::catat('lihat', 'User', "Melihat password user {$user->name} ({$user->nik})");
        $p = $user->passwordTercatat();
        return response()->json(['ada' => $p !== null, 'password' => $p]);
    }

    public function sinkron()
    {
        Artisan::call('gapura:sync-users');
        Aktivitas::catat('sinkron', 'User', 'Sinkronisasi akun dari data karyawan');
        return back()->with('ok', trim(Artisan::output()) . ' Akun baru memakai password = NIK.');
    }
}
