<?php

namespace App\Support;

use App\Models\{Notifikasi, Role, Sppd, User};

// Notifikasi SPPD. Kegagalan kirim notifikasi tidak boleh menggagalkan proses utama.
class Notif
{
    /** Semua user ber-role Penyetuju (izin sppd.setujui eksplisit), kecuali $kecuali. */
    public static function daftarPenyetuju(?int $kecuali = null)
    {
        $roleIds = Role::all()->filter(fn($r) => in_array('sppd.setujui', $r->permissions ?? [], true))->pluck('id');
        return User::whereIn('role_id', $roleIds)->when($kecuali, fn($q) => $q->where('id', '!=', $kecuali))->get();
    }

    public static function kePenyetuju(Sppd $s, string $tipe, string $judul, string $pesan): void
    {
        try {
            $now = now();
            $rows = self::daftarPenyetuju($s->user_id)->map(fn($u) => [
                'user_id' => $u->id,
                'sppd_id' => $s->id,
                'tipe' => $tipe,
                'judul' => $judul,
                'pesan' => $pesan,
                'created_at' => $now,
                'updated_at' => $now
            ])->all();
            if ($rows) Notifikasi::insert($rows);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function kePemohon(Sppd $s, string $tipe, string $judul, string $pesan): void
    {
        try {
            Notifikasi::create(['user_id' => $s->user_id, 'sppd_id' => $s->id, 'tipe' => $tipe, 'judul' => $judul, 'pesan' => $pesan]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
