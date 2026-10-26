<?php
namespace App\Support;

// Pembaca sederhana .xlsx / .csv -> array baris (tanpa library tambahan; butuh ext-zip untuk xlsx)
class Sheet
{
    public static function read(string $path, string $type, ?string $sheet = null): array
    {
        return $type === 'xlsx' ? self::xlsx($path, $sheet) : self::csv($path);
    }

    private static function csv(string $path): array
    {
        $raw = (string) file_get_contents($path);
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        if (!mb_check_encoding($raw, 'UTF-8')) $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
        $first = strtok($raw, "\n") ?: ''; $d = ';'; $best = -1;
        foreach ([';', ',', "\t"] as $c) { $n = substr_count($first, $c); if ($n > $best) { $best = $n; $d = $c; } }
        $fh = fopen('php://temp', 'r+'); fwrite($fh, $raw); rewind($fh); $rows = [];
        while (($r = fgetcsv($fh, 0, $d, '"', '')) !== false) $rows[] = array_map(fn($v) => trim((string) $v), $r);
        fclose($fh);
        return $rows;
    }

    private static function col(string $ref): int
    {
        preg_match('/^[A-Z]+/i', $ref, $m); $n = 0;
        foreach (str_split(strtoupper($m[0] ?? 'A')) as $ch) $n = $n * 26 + (ord($ch) - 64);
        return $n - 1;
    }

    private static function xlsx(string $path, ?string $sheet): array
    {
        $z = new \ZipArchive;
        if ($z->open($path) !== true) throw new \RuntimeException('File Excel tidak dapat dibuka.');
        $shared = [];
        if (($x = $z->getFromName('xl/sharedStrings.xml')) !== false) {
            foreach (simplexml_load_string($x)->si as $si) {
                if (isset($si->t)) { $shared[] = (string) $si->t; continue; }
                $t = ''; foreach ($si->r as $r) $t .= (string) $r->t; $shared[] = $t;
            }
        }
        $target = 'worksheets/sheet1.xml'; $found = ($sheet === null || $sheet === '');
        $wb = simplexml_load_string((string) $z->getFromName('xl/workbook.xml'));
        $rels = simplexml_load_string((string) $z->getFromName('xl/_rels/workbook.xml.rels'));
        $map = []; foreach ($rels->Relationship as $rel) $map[(string) $rel['Id']] = (string) $rel['Target'];
        foreach ($wb->sheets->sheet as $s) {
            if ($found || strcasecmp((string) $s['name'], (string) $sheet) === 0) {
                $rid = (string) $s->attributes('r', true)['id']; $target = $map[$rid] ?? $target; $found = true; break;
            }
        }
        if (!$found) throw new \RuntimeException("Sheet \"$sheet\" tidak ditemukan di file.");
        $target = ltrim($target, '/'); if (!str_starts_with($target, 'xl/')) $target = 'xl/' . $target;
        $xml = $z->getFromName($target);
        if ($xml === false) throw new \RuntimeException('Isi sheet tidak dapat dibaca.');
        $rows = []; $maxRow = 0;
        foreach (simplexml_load_string($xml)->sheetData->row as $row) {
            $r = []; $line = (int) $row['r'] - 1;
            foreach ($row->c as $c) {
                $t = (string) $c['t']; $v = (string) $c->v;
                if ($t === 's') $v = $shared[(int) $v] ?? '';
                elseif ($t === 'inlineStr') $v = (string) $c->is->t;
                $r[self::col((string) $c['r'])] = trim($v);
            }
            if ($line < 20000) { $rows[$line] = $r; $maxRow = max($maxRow, $line + 1); }
        }
        $out = [];
        for ($i = 0; $i < $maxRow; $i++) {
            $r = $rows[$i] ?? []; $w = $r ? max(array_keys($r)) + 1 : 0; $d = [];
            for ($j = 0; $j < $w; $j++) $d[] = $r[$j] ?? '';
            $out[] = $d;
        }
        return $out;
    }
}
