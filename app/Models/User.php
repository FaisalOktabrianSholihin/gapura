<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'akun_users';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['password' => 'hashed'];

    public function karyawan() { return $this->belongsTo(Karyawan::class, 'karyawan_id'); }
    public function peran()    { return $this->belongsTo(Role::class, 'role_id'); } // bukan "role" agar tidak bentrok dengan kolom lama `role`

    public function izin(string $p): bool
    {
        $perms = $this->peran?->permissions ?? [];
        return in_array('*', $perms, true) || in_array($p, $perms, true);
    }
    public function isUmum(): bool { return $this->izin('sppd.semua'); }
}
