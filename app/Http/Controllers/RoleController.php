<?php
namespace App\Http\Controllers;
use App\Models\Role;
use App\Support\Aktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index() { return view('admin.roles-index', ['roles' => Role::withCount('users')->orderBy('id')->get()]); }
    public function create() { return view('admin.roles-form', ['role' => new Role(['permissions' => []])]); }

    private function data(Request $r, ?Role $role = null): array
    {
        $r->validate(['nama' => 'required|string|max:60', 'deskripsi' => 'nullable|string|max:200', 'izin' => 'nullable|array', 'izin.*' => 'in:' . implode(',', array_keys(config('gapura.izin')))]);
        $perm = $role?->slug === 'super_admin' ? ['*'] : array_values($r->input('izin', []));
        return ['nama' => $r->nama, 'deskripsi' => $r->deskripsi, 'permissions' => $perm];
    }

    public function store(Request $r)
    {
        $d = $this->data($r); $slug = Str::slug($r->nama, '_') ?: 'role'; $i = 1; $base = $slug;
        while (Role::where('slug', $slug)->exists()) $slug = $base . '_' . $i++;
        $role = Role::create($d + ['slug' => $slug, 'is_sistem' => false]);
        Aktivitas::catat('buat', 'Roles', "Membuat role {$role->nama}");
        return redirect()->route('roles.index')->with('ok', 'Role dibuat.');
    }

    public function edit(Role $role) { return view('admin.roles-form', compact('role')); }

    public function update(Request $r, Role $role)
    {
        $role->update($this->data($r, $role));
        Aktivitas::catat('ubah', 'Roles', "Mengubah role {$role->nama}");
        return redirect()->route('roles.index')->with('ok', 'Role diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_sistem || $role->users()->exists()) return back()->withErrors(['role' => 'Role sistem atau yang masih dipakai user tidak bisa dihapus.']);
        Aktivitas::catat('hapus', 'Roles', "Menghapus role {$role->nama}");
        $role->delete();
        return back()->with('ok', 'Role dihapus.');
    }
}
