<?php
namespace App\Http\Controllers;
use App\Support\{Aktivitas, Sheet, XlsxWriter};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Import Excel (.xlsx) / CSV ke log lingkungan. Judul kolom dikenali otomatis (tidak harus sama persis dengan template).
class ImportController extends Controller
{
    private const CEMARAN = [['no', 'No', 'number'], ['parameter', 'Parameter', 'text'], ['status', 'Status', 'text'], ['tindakan', 'Tindakan Segera', 'text'], ['rekomendasi', 'Rekomendasi Tindakan', 'text']];

    private function fields(string $key): array
    {
        if ($key === 'cemaran') return self::CEMARAN;
        $c = config("gapura.logs.$key"); abort_unless($c, 404);
        return $c['fields'];
    }
    private function judul(string $key): string { return $key === 'cemaran' ? 'Checklist Cemaran B3' : config("gapura.logs.$key.title"); }
    private function kembali(string $key): string { return $key === 'cemaran' ? 'cemaran.index' : config("gapura.logs.$key.back"); }
    private function norm($s): string { return strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $s)); }

    public function form(string $key)
    {
        $this->fields($key);
        return view('lingkungan.import', ['key' => $key, 'judul' => $this->judul($key), 'kembali' => $this->kembali($key), 'fields' => $this->fields($key)]);
    }

    // Template Excel (.xlsx) berformat: judul kolom, dropdown pilihan, format tanggal, plus sheet "Petunjuk". ?format=csv untuk CSV lama.
    public function template(Request $r, string $key)
    {
        $fields = $this->fields($key);
        if ($r->query('format') === 'csv') {
            return response()->streamDownload(function () use ($key, $fields) {
                $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF");
                fputcsv($o, array_column($fields, 1), ';', '"', '');
                if ($key === 'cemaran') foreach (config('gapura.cemaran_items') as $i => $it) fputcsv($o, [$i + 1, $it[1], 'bersih', '', ''], ';', '"', '');
                fclose($o);
            }, "template-import-$key.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
        }
        $file = XlsxWriter::buat($this->lembarTemplate($key, $fields));
        return response()->download($file, "template-import-$key.xlsx", ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
    }

    private function lembarTemplate(string $key, array $fields): array
    {
        $judul = $this->judul($key);
        $tipeNama = fn($t) => match (true) { $t === 'date' => 'Tanggal (dd/mm/yyyy)', $t === 'time' => 'Jam (hh:mm)', $t === 'number' => 'Angka', str_starts_with($t, 'select:') => 'Pilih dari daftar', default => 'Teks' };
        $contoh = function (array $f) {
            [$col, , $t] = $f;
            return match (true) { $t === 'date' => '26/10/2026', $t === 'time' => '08:00', $t === 'number' => '12,5',
                str_starts_with($t, 'select:') => (string) (config('gapura.' . substr($t, 7))[0] ?? ''), $col === 'keterangan' => '-', default => 'contoh' };
        };

        // ---- sheet "Data"
        if ($key === 'cemaran') {
            $rows = [array_column($fields, 1)];
            foreach (config('gapura.cemaran_items') as $i => $it) $rows[] = [$i + 1, $it[1], 'bersih', '', ''];
            $data = ['name' => 'Data', 'rows' => $rows, 'header' => [0 => 'wajib', 1 => 'wajib', 2 => 'wajib', 3 => 'opsional', 4 => 'opsional'],
                'widths' => [6, 62, 14, 32, 32], 'list' => [2 => ['bersih', 'tercemar']], 'listTo' => count($rows)];
        } else {
            $head = []; $hdr = []; $widths = []; $fmt = []; $list = [];
            foreach ($fields as $i => $f) {
                [$col, $label, $t] = $f;
                $head[] = $label; $hdr[$i] = in_array('req', $f) ? 'wajib' : 'opsional';
                $widths[] = match (true) { $t === 'date' => 16, $t === 'time' => 10, $t === 'number' => 14, str_starts_with($t, 'select:') => 28, $col === 'keterangan' => 36, default => 24 };
                if ($t === 'date' || $t === 'time') $fmt[$i] = $t;
                if (str_starts_with($t, 'select:')) $list[$i] = config('gapura.' . substr($t, 7));
            }
            $data = ['name' => 'Data', 'rows' => [$head], 'header' => $hdr, 'widths' => $widths, 'format' => $fmt, 'list' => $list];
        }

        // ---- sheet "Petunjuk"
        $B = XlsxWriter::S_BOLD;
        $p = [["Petunjuk pengisian — $judul"], [], [['Cara pakai', $B]],
            [['1. Isi data pada sheet "Data" mulai baris ke-2. Jangan mengubah atau menghapus judul kolom di baris 1.', 0]],
            [['2. Judul kolom hijau = wajib diisi, judul abu-abu = opsional.', 0]],
            [['3. Tanggal: dd/mm/yyyy (mis. 26/10/2026) atau tanggal biasa Excel. Jam: hh:mm. Angka: boleh memakai koma atau titik desimal.', 0]],
            [['4. Kolom yang memiliki panah dropdown: pilih salah satu nilai dari daftar.', 0]],
            [['5. Baris kosong dilewati. Setelah selesai, simpan sebagai .xlsx lalu unggah di halaman Import. Baris yang salah dilaporkan lengkap dengan nomor barisnya.', 0]]];
        if ($key === 'cemaran') $p[] = [['6. Satu file = satu pemeriksaan. Tanggal, petugas, dan shift diisi pada form Import. Parameter yang tidak ada di file otomatis berstatus bersih.', 0]];
        $p[] = [];
        $p[] = [['Kolom', $B], ['Wajib?', $B], ['Format', $B], ['Pilihan nilai', $B]];
        foreach ($fields as $f) {
            [$col, $label, $t] = $f;
            $p[] = [$label, in_array('req', $f) ? 'Wajib' : 'Opsional', $tipeNama($t), str_starts_with($t, 'select:') ? implode(', ', config('gapura.' . substr($t, 7))) : ''];
        }
        if ($key !== 'cemaran') { $p[] = []; $p[] = [['Contoh pengisian', $B], implode('  |  ', array_map($contoh, $fields))]; }
        $petunjuk = ['name' => 'Petunjuk', 'plain' => true, 'rows' => $p, 'widths' => [30, 12, 24, 80]];

        return [$data, $petunjuk];
    }

    public function store(Request $r, string $key)
    {
        $fields = $this->fields($key);
        $r->validate(['file' => 'required|file|max:10240', 'sheet' => 'nullable|string|max:100'] +
            ($key === 'cemaran' ? ['tanggal' => 'required|date', 'petugas' => 'required|string|max:100', 'shift' => 'nullable|string|max:30'] : []));
        $ext = strtolower($r->file('file')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'csv', 'txt'])) return back()->withErrors(['file' => 'Format file harus .xlsx atau .csv (file .xls lama: simpan ulang sebagai .xlsx).']);
        try { $rows = Sheet::read($r->file('file')->getRealPath(), $ext === 'xlsx' ? 'xlsx' : 'csv', $r->sheet); }
        catch (\Throwable $e) { return back()->withErrors(['file' => $e->getMessage()]); }

        $hasil = $key === 'cemaran' ? $this->importCemaran($r, $rows, $fields) : $this->importLog($key, $rows, $fields);
        Aktivitas::catat('import', $this->judul($key), "Import {$r->file('file')->getClientOriginalName()}: {$hasil['ok']} baris berhasil, {$hasil['errTotal']} dilewati");
        return back()->with('hasil', $hasil);
    }

    // ---------- pemetaan judul kolom ----------
    private function cocok(string $cell, array $f): bool
    {
        $c = $this->norm($cell); if ($c === '') return false;
        $col = $this->norm($f[0]); $lab = $this->norm($f[1]);
        return $c === $lab || $c === $col || (strlen($col) >= 3 && str_starts_with($c, $col));
    }
    private function petakan(array $row, array $fields): array
    {
        $map = []; $pakai = [];
        foreach ($fields as $f) foreach ($row as $i => $cell) {
            if (!isset($pakai[$i]) && $this->cocok((string) $cell, $f)) { $map[$f[0]] = $i; $pakai[$i] = true; break; }
        }
        return $map;
    }
    private function cariHeader(array $rows, array $fields): array
    {
        $best = null; $map = []; $skor = 1;
        foreach (array_slice($rows, 0, 60, true) as $i => $row) {
            $m = $this->petakan($row, $fields);
            if (count($m) > $skor) { $skor = count($m); $best = $i; $map = $m; }
        }
        return [$best, $map];
    }

    // ---------- konversi nilai ----------
    private function konversi(string $type, string $v)
    {
        if ($type === 'date') {
            if (is_numeric($v) && !str_contains($v, '/')) { $n = (float) $v; return ($n > 20000 && $n < 80000) ? Carbon::create(1899, 12, 30)->addDays((int) floor($n))->toDateString() : false; }
            if (preg_match('#^(\d{1,2})[/.-](\d{1,2})[/.-](\d{2,4})$#', $v, $m)) { $y = (int) $m[3] + ($m[3] < 100 ? 2000 : 0); return checkdate((int) $m[2], (int) $m[1], $y) ? sprintf('%04d-%02d-%02d', $y, $m[2], $m[1]) : false; }
            if (preg_match('#^(\d{4})-(\d{2})-(\d{2})#', $v, $m)) return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? "$m[1]-$m[2]-$m[3]" : false;
            return false;
        }
        if ($type === 'time') {
            if (is_numeric($v) && (float) $v < 1) return gmdate('H:i', (int) round((float) $v * 86400));
            return preg_match('#^(\d{1,2})[:.](\d{2})#', $v, $m) ? sprintf('%02d:%02d', $m[1], $m[2]) : false;
        }
        if ($type === 'number') {
            $s = str_replace([' ', "\xC2\xA0"], '', $v);
            $s = preg_match('/^-?\d{1,3}(\.\d{3})+(,\d+)?$/', $s) ? str_replace(',', '.', str_replace('.', '', $s)) : str_replace(',', '.', $s);
            return is_numeric($s) ? round((float) $s, 2) : false;
        }
        if (str_starts_with($type, 'select:')) foreach (config('gapura.' . substr($type, 7)) as $o) if (strcasecmp($o, $v) === 0) return $o;
        return mb_substr($v, 0, 255);
    }

    private function importLog(string $key, array $rows, array $fields): array
    {
        $cfg = config("gapura.logs.$key");
        [$h, $map] = $this->cariHeader($rows, $fields);
        if ($h === null) return ['ok' => 0, 'errTotal' => 1, 'err' => ['Baris judul kolom tidak ditemukan. Gunakan template atau pastikan judul kolom sesuai (mis. "Tanggal", "Jenis Limbah").']];
        $batch = []; $errs = [];
        for ($i = $h + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (count($this->petakan($row, $fields)) >= 2) break; // masuk ke tabel lain di sheet yang sama
            $data = []; $kosong = true;
            foreach ($fields as $f) {
                $v = isset($map[$f[0]]) ? trim((string) ($row[$map[$f[0]]] ?? '')) : ''; $data[$f[0]] = $v;
                if ($v !== '' && in_array($f[2], ['date', 'number'])) $kosong = false;
            }
            if ($kosong) continue; // baris kosong / placeholder
            $out = []; $msg = null;
            foreach ($fields as $f) {
                $v = $data[$f[0]];
                if ($v === '') { if (in_array('req', $f)) { $msg = $f[1] . ' kosong'; break; } $out[$f[0]] = null; continue; }
                $c = $this->konversi($f[2], $v);
                if ($c === false) { $msg = "{$f[1]} tidak valid ($v)"; break; }
                $out[$f[0]] = $c;
            }
            if ($msg) { $errs[] = 'Baris ' . ($i + 1) . ': ' . $msg; continue; }
            $batch[] = $out + ['created_at' => now(), 'updated_at' => now()];
        }
        DB::transaction(fn() => collect($batch)->chunk(200)->each(fn($c) => DB::table($cfg['table'])->insert($c->all())));
        return ['ok' => count($batch), 'errTotal' => count($errs), 'err' => array_slice($errs, 0, 30)];
    }

    private function importCemaran(Request $r, array $rows, array $fields): array
    {
        [$h, $map] = $this->cariHeader($rows, $fields);
        if ($h === null || !isset($map['status'])) return ['ok' => 0, 'errTotal' => 1, 'err' => ['Kolom "Status" dan "No"/"Parameter" tidak ditemukan.']];
        $master = config('gapura.cemaran_items'); $hasil = [];
        for ($i = $h + 1; $i < count($rows); $i++) {
            $row = $rows[$i]; $cell = fn($k) => isset($map[$k]) ? trim((string) ($row[$map[$k]] ?? '')) : '';
            $idx = null;
            if (ctype_digit($cell('no')) && (int) $cell('no') >= 1 && (int) $cell('no') <= count($master)) $idx = (int) $cell('no') - 1;
            elseif ($cell('parameter') !== '') foreach ($master as $j => $m) if ($this->norm($m[1]) === $this->norm($cell('parameter'))) { $idx = $j; break; }
            if ($idx === null) continue;
            $hasil[$idx] = ['status' => str_contains(strtolower($cell('status')), 'cemar') ? 'tercemar' : 'bersih', 'tindakan' => $cell('tindakan') ?: null, 'rekomendasi' => $cell('rekomendasi') ?: null];
        }
        if (!$hasil) return ['ok' => 0, 'errTotal' => 1, 'err' => ['Tidak ada baris parameter yang cocok. Pastikan kolom No (1–' . count($master) . ') atau Parameter terisi.']];
        DB::transaction(function () use ($r, $master, $hasil) {
            $id = DB::table('env_cemaran')->insertGetId(['tanggal' => $r->tanggal, 'petugas' => $r->petugas, 'shift' => $r->shift, 'created_by' => auth()->id(), 'created_at' => now(), 'updated_at' => now()]);
            foreach ($master as $i => [$area, $param, $pic]) {
                $x = $hasil[$i] ?? ['status' => 'bersih', 'tindakan' => null, 'rekomendasi' => null];
                DB::table('env_cemaran_items')->insert(['cemaran_id' => $id, 'no' => $i + 1, 'area' => $area, 'parameter' => $param, 'status' => $x['status'], 'tindakan' => $x['tindakan'], 'rekomendasi' => $x['rekomendasi'], 'pic' => $pic]);
            }
        });
        return ['ok' => count($hasil), 'errTotal' => 0, 'err' => [], 'info' => '1 pemeriksaan dibuat; parameter yang tidak ada di file otomatis berstatus Bersih. Foto tidak ikut diimpor.'];
    }
}
