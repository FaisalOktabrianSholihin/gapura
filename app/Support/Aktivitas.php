<?php
namespace App\Support;
use Illuminate\Support\Facades\DB;

class Aktivitas
{
    public static function catat(string $aksi, string $modul, string $deskripsi = ''): void
    {
        try {
            DB::table('activity_logs')->insert([
                'user_id' => auth()->id(), 'user_nama' => auth()->user()?->name, 'aksi' => $aksi, 'modul' => $modul,
                'deskripsi' => $deskripsi, 'ip' => request()->ip(), 'created_at' => now(),
            ]);
        } catch (\Throwable $e) { /* log tidak boleh mengganggu proses utama */ }
    }
}
