<?php
namespace App\Support;
use Illuminate\Support\Facades\DB;

// Membaca tabel `settings` bawaan db_indukk (key/value). Aman bila key belum ada.
class Setting
{
    public static function get(string $key, $default = null)
    {
        try { return DB::connection('indukk')->table('settings')->where('key', $key)->value('value') ?? $default; }
        catch (\Throwable $e) { return $default; }
    }
    public static function ttd(): array
    {
        return [
            'nama'    => self::get('sppd_ttd_nama', config('gapura.penandatangan.nama')),
            'jabatan' => self::get('sppd_ttd_jabatan', config('gapura.penandatangan.jabatan')),
        ];
    }
}
