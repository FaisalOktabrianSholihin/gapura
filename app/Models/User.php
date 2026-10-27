<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    protected $table = 'akun_users';
    protected $guarded = [];
    protected $hidden = ['password', 'password_enc', 'remember_token'];
    protected $casts = ['password' => 'hashed'];

    public function karyawan() { return $this->belongsTo(Karyawan::class, 'karyawan_id'); }
    public function peran()    { return $this->belongsTo(Role::class, 'role_id'); } // bukan "role" agar tidak bentrok dengan kolom lama `role`

    public function izin(string $p): bool
    {
        $perms = $this->peran?->permissions ?? [];
        return in_array('*', $perms, true) || in_array($p, $perms, true);
    }

    /** Boleh melihat SPPD semua divisi (Bagian Umum, Penyetuju, Super Admin). */
    public function isUmum(): bool { return $this->izin('sppd.semua') || $this->isPenyetuju(); }

    /** Hanya role yang secara eksplisit memiliki izin sppd.setujui (role "Penyetuju"). Bagian Umum & Super Admin (bintang) TIDAK otomatis. */
    public function isPenyetuju(): bool { return in_array('sppd.setujui', $this->peran?->permissions ?? [], true); }

    public function isSuperAdmin(): bool { return in_array('*', $this->peran?->permissions ?? [], true); }

    /** Set password sekaligus menyimpan salinan terenkripsi (APP_KEY) agar Super Admin bisa melihatnya bila user lupa. Belum di-save(). */
    public function aturPassword(string $plain): static
    {
        $this->password = $plain; // di-hash otomatis oleh cast 'hashed'
        $this->password_enc = Crypt::encryptString($plain);
        return $this;
    }

    public function passwordTercatat(): ?string
    {
        try { return $this->password_enc ? Crypt::decryptString($this->password_enc) : null; }
        catch (\Throwable $e) { return null; }
    }
}
