<?php

namespace App\Console\Commands;

use App\Models\{Karyawan, Role, User};
use Illuminate\Console\Command;

class SyncUsers extends Command
{
    protected $signature = 'gapura:sync-users {--umum=* : NIK yang diberi role Bagian Umum} {--super=* : NIK yang diberi role Super Admin} {--reset : reset password ke NIK}';
    protected $description = 'Buat akun login dari tabel karyawans (username = NIK, password awal = NIK)';

    public function handle(): int
    {
        $rid = fn($s) => Role::where('slug', $s)->value('id');
        $n = 0;
        Karyawan::query()->each(function ($k) use ($rid, &$n) {
            if (!$k->nik) return;
            $u = User::firstOrNew(['nik' => $k->nik]);
            $baru = !$u->exists;
            $u->karyawan_id = $k->id;
            $u->name = $k->nama;
            if ($baru || $this->option('reset')) $u->aturPassword((string) $k->nik);
            if (in_array($k->nik, $this->option('super'))) $u->role_id = $rid('super_admin');
            elseif (in_array($k->nik, $this->option('umum'))) $u->role_id = $rid('umum');
            elseif ($baru || !$u->role_id) $u->role_id = $rid('user');
            $u->save();
            $n++;
        });
        $this->info("$n akun disinkronkan.");
        return 0;
    }
}
