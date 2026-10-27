<?php

namespace App\Support;

// Penulis .xlsx minimal tanpa library tambahan (butuh ext-zip, sama seperti pembaca Sheet).
// Dipakai untuk template import. Cell teks ditulis sebagai inlineStr, angka sebagai numerik.
//
// $sheets = [[
//   'name'   => 'Data',
//   'rows'   => [[...baris 1 (judul kolom)...], [...], ...],
//   'header' => [0 => 'wajib'|'opsional', ...]  // gaya tiap sel judul kolom (opsional)
//   'widths' => [14, 10, ...],                   // lebar kolom
//   'format' => [0 => 'date'|'time', ...],       // format sel kosong di bawah judul (mis. dd/mm/yyyy)
//   'list'   => [1 => ['a','b'], ...],           // dropdown validasi per kolom (indeks 0)
//   'listTo' => 1000,                            // sampai baris ke-berapa dropdown berlaku
//   'plain'  => true,                            // sheet teks biasa (petunjuk): baris pertama = judul besar, tanpa freeze
// ], ...]
class XlsxWriter
{
    // indeks gaya (cellXfs) di styles.xml
    private const S_HEAD = 1, S_DATE = 2, S_TIME = 3, S_TITLE = 5, S_HEAD_OPT = 6;
    public const S_NOTE = 4, S_WRAP = 7, S_BOLD = 8; // untuk sheet 'plain': sel boleh berupa [teks, gaya]

    public static function buat(array $sheets): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gapura-' . bin2hex(random_bytes(6)) . '.xlsx';
        $z = new \ZipArchive;
        if ($z->open($path, \ZipArchive::CREATE) !== true) throw new \RuntimeException('Tidak dapat membuat file Excel sementara.');

        $ct = '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $wbSheets = '';
        $rels = '';
        foreach ($sheets as $i => $sh) {
            $n = $i + 1;
            $ct .= "<Override PartName=\"/xl/worksheets/sheet$n.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>";
            $wbSheets .= '<sheet name="' . self::x(self::namaSheet($sh['name'] ?? "Sheet$n")) . "\" sheetId=\"$n\" r:id=\"rId$n\"/>";
            $rels .= "<Relationship Id=\"rId$n\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet$n.xml\"/>";
            $z->addFromString("xl/worksheets/sheet$n.xml", self::sheet($sh, $i === 0));
        }
        $rels .= '<Relationship Id="rId' . (count($sheets) + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        $hdr = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $z->addFromString('[Content_Types].xml', $hdr . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>' . $ct . '</Types>');
        $z->addFromString('_rels/.rels', $hdr . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $z->addFromString('xl/workbook.xml', $hdr . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>' . $wbSheets . '</sheets></workbook>');
        $z->addFromString('xl/_rels/workbook.xml.rels', $hdr . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels . '</Relationships>');
        $z->addFromString('xl/styles.xml', $hdr . self::styles());
        $z->close();
        return $path;
    }

    private static function sheet(array $sh, bool $pertama): string
    {
        $rows = $sh['rows'] ?? [];
        $plain = !empty($sh['plain']);
        $widths = $sh['widths'] ?? [];
        $format = $sh['format'] ?? [];

        $cols = '';
        foreach ($widths as $i => $w) {
            $c = $i + 1;
            $st = isset($format[$i]) ? ' style="' . ($format[$i] === 'time' ? self::S_TIME : self::S_DATE) . '"' : '';
            $cols .= "<col min=\"$c\" max=\"$c\" width=\"$w\"$st customWidth=\"1\"/>";
        }

        $data = '';
        foreach ($rows as $r => $row) {
            $rn = $r + 1;
            $cells = '';
            $tinggi = '';
            foreach (array_values($row) as $c => $v) {
                if ($v === null || $v === '') continue;
                $ref = self::huruf($c) . $rn;
                if ($plain) $s = $r === 0 ? self::S_TITLE : self::S_WRAP;
                else $s = $r === 0 ? (($sh['header'][$c] ?? 'wajib') === 'wajib' ? self::S_HEAD : self::S_HEAD_OPT) : 0;
                if ($plain && is_array($v)) {
                    $s = $v[1];
                    $v = $v[0];
                } // [teks, gaya]
                $cells .= (is_int($v) || is_float($v))
                    ? "<c r=\"$ref\" s=\"$s\"><v>$v</v></c>"
                    : "<c r=\"$ref\" s=\"$s\" t=\"inlineStr\"><is><t xml:space=\"preserve\">" . self::x((string) $v) . '</t></is></c>';
            }
            if (!$plain && $r === 0) $tinggi = ' ht="30" customHeight="1"';
            $data .= "<row r=\"$rn\"$tinggi>$cells</row>";
        }

        $view = '<sheetViews><sheetView workbookViewId="0"' . ($pertama ? ' tabSelected="1"' : '') . ($plain ? ' showGridLines="0"' : '') . '>'
            . ($plain ? '' : '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>') . '</sheetView></sheetViews>';

        $dv = '';
        $nDv = 0;
        $to = (int) ($sh['listTo'] ?? 1000);
        foreach (($sh['list'] ?? []) as $i => $opsi) {
            $f = implode(',', array_map(fn($o) => str_replace(',', ' ', (string) $o), $opsi));
            if ($f === '' || mb_strlen($f) > 250) continue; // batas Excel untuk daftar langsung = 255 karakter
            $col = self::huruf($i);
            $dv .= "<dataValidation type=\"list\" allowBlank=\"1\" showErrorMessage=\"1\" errorTitle=\"Nilai tidak valid\" error=\"Pilih salah satu dari daftar.\" sqref=\"{$col}2:{$col}$to\"><formula1>\"" . self::x($f) . '"</formula1></dataValidation>';
            $nDv++;
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . $view . '<sheetFormatPr defaultRowHeight="15"/>'
            . ($cols ? "<cols>$cols</cols>" : '') . "<sheetData>$data</sheetData>" . ($nDv ? "<dataValidations count=\"$nDv\">$dv</dataValidations>" : '') . '</worksheet>';
    }

    private static function styles(): string
    {
        $b = '<left style="thin"><color rgb="FFBBBBBB"/></left><right style="thin"><color rgb="FFBBBBBB"/></right><top style="thin"><color rgb="FFBBBBBB"/></top><bottom style="thin"><color rgb="FFBBBBBB"/></bottom><diagonal/>';
        return '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="2"><numFmt numFmtId="164" formatCode="dd/mm/yyyy"/><numFmt numFmtId="165" formatCode="hh:mm"/></numFmts>'
            . '<fonts count="5">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><i/><sz val="10"/><color rgb="FF555555"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="14"/><color rgb="FF0F2A1D"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF15803D"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF64748B"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border>' . $b . '</border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="9">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'                                                                                                   // 0 biasa
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' // 1 judul wajib
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'                                                                           // 2 tanggal
            . '<xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'                                                                           // 3 jam
            . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'                        // 4 catatan
            . '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>'                                                                                     // 5 judul besar
            . '<xf numFmtId="0" fontId="1" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' // 6 judul opsional
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'                                      // 7 teks bungkus
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'                        // 8 tebal
            . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private static function huruf(int $i): string
    {
        $s = '';
        for ($n = $i + 1; $n > 0; $n = intdiv($n - 1, 26)) $s = chr(65 + ($n - 1) % 26) . $s;
        return $s;
    }

    private static function x(string $s): string
    {
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s);
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function namaSheet(string $n): string
    {
        return mb_substr(trim(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $n)) ?: 'Sheet', 0, 31);
    }
}
