<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>SPPD {{ $s->nomor }}</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}"><link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
<style>
 @page{size:A4;margin:14mm 16mm}
 *{box-sizing:border-box} body{font:11px/1.35 Tahoma,Verdana,sans-serif;color:#000;margin:0;background:#e5e7eb}
 .page{width:210mm;min-height:297mm;margin:0 auto;background:#fff;padding:14mm 16mm}
 .bar{position:fixed;top:10px;right:10px;display:flex;gap:6px} .bar button{padding:8px 14px;border:0;border-radius:6px;background:#0f3d2a;color:#fff;font-weight:600;cursor:pointer}
 .hd{display:flex;align-items:flex-start;margin-bottom:6px} .hd img{height:42px} .ttl{flex:1;text-align:center;margin-right:42px}
 .ttl b{font-size:12px;text-decoration:underline;display:block;margin-top:4px}
 table.f td{vertical-align:top;padding:0 0 1px} table.f td:first-child{width:118px;padding-left:2px} table.f td.c{width:14px}
 ol{margin:0;padding-left:15px;list-style:none} ol li{counter-increment:n} ol li:before{content:counter(n)'. ';margin-left:-15px;display:inline-block;width:15px}
 ol{counter-reset:n} p.j{text-align:justify;margin:10px 0}
 h4{margin:12px 0 4px;font-size:11px;text-decoration:underline;padding-left:14px}
 .blk{display:flex;gap:6px;margin-bottom:6px;break-inside:avoid;align-items:flex-end} .blk>b{font-weight:400;width:12px;align-self:flex-start} .blk .bd{flex:1}
 .ar{display:flex;align-items:flex-end;gap:16px;margin-right:4px}
 .sg{width:150px;text-align:center} .sg .sp{height:58px;border-bottom:1px solid #000} .sg .lb{font-size:9px;margin-top:2px}
 .st{width:64px;height:64px;border:1px dashed #555;border-radius:50%;display:flex;align-items:center;justify-content:center;text-align:center;font-size:8px;line-height:1.2;color:#555}
 .ket{font-size:9px;font-style:italic;color:#444;padding-left:14px;margin:0 0 5px}
 .foot{display:flex;justify-content:space-between;align-items:flex-end;margin-top:18px;break-inside:avoid}
 table.fs{border-collapse:collapse;width:300px;font-size:10.5px} table.fs td{border:1px solid #000;padding:0 4px;height:15px} table.fs td.y{text-align:center;font-weight:700;width:84px}
 .o{border:1.3px solid #000;border-radius:50%;padding:0 3px}
 .ttd{text-align:center;width:200px} .ttd svg{width:92px;height:92px}
 @media print{body{background:#fff}.page{margin:0;padding:0;width:auto;min-height:0}.bar{display:none}}
</style></head><body>
<div class="bar"><button onclick="print()">Cetak / Simpan PDF</button></div>
<div class="page">
 <div class="hd"><img src="{{ asset('images/logo.png') }}" alt="M27"><div class="ttl"><b>SURAT PERJALANAN DINAS</b>Nomor: {{ $s->nomor }}</div></div>
 <div>Perintah perjalanan dinas ini diberikan kepada:</div>
 <table class="f" style="margin-top:6px">
  <tr><td>Nama Karyawan</td><td class="c">:</td><td>{{ $s->nama }}</td></tr>
  <tr><td>NIK</td><td class="c">:</td><td>{{ $s->nik }}</td></tr>
  <tr><td>Nomor Surat Tugas</td><td class="c">:</td><td>{{ $s->nomor_surat_tugas }}</td></tr>
  <tr><td>Jenis Perjalanan</td><td class="c">:</td><td>{{ $s->jenis_perjalanan }}</td></tr>
  <tr><td>Jabatan</td><td class="c">:</td><td>{{ $s->jabatan }}</td></tr>
  <tr><td>Divisi</td><td class="c">:</td><td>{{ $s->divisi }}</td></tr>
  <tr><td>Tujuan Dinas</td><td class="c">:</td><td><ol>@foreach($s->daftar('tujuan') as $x)<li>{{ $x }}</li>@endforeach</ol></td></tr>
  <tr><td>Keperluan Dinas</td><td class="c">:</td><td><ol>@foreach($s->daftar('keperluan') as $x)<li>{{ $x }}</li>@endforeach</ol></td></tr>
  <tr><td>Tanggal Berangkat</td><td class="c">:</td><td>{{ $s->tanggal_berangkat->translatedFormat('j F Y') }}</td></tr>
  <tr><td>Tanggal Kembali</td><td class="c">:</td><td>{{ $s->tanggal_kembali->translatedFormat('j F Y') }}</td></tr>
  <tr><td>Transportasi</td><td class="c">:</td><td>{{ $s->transportasi }}</td></tr>
  <tr><td>Rombongan</td><td class="c">:</td><td><ol>@foreach($s->daftar('rombongan') as $x)<li>{{ $x }}</li>@endforeach</ol></td></tr>
 </table>
 <p class="j">Diharapkan pihak - pihak yang berkaitan dengan keperluan Dinas tersebut dapat memberikan bantuan yang berkenaan pelaksanaan tugas ini. Selain itu untuk keperluan administrasi perjalanan dinas kami memerlukan tanda tangan, stempel, nama terang jabatan, dan nama instansi sebagai tanda bukti perusahaan dinas, dan keterangan penggunaan fasilitas kendaraan dinas dari instansi/perusahaan yang dikunjungi.</p>

 <h4>Pengesahan Instansi/Perusahaan yang Dikunjungi</h4>
 <p class="ket">Tanda tangan basah di atas garis; stempel basah pada lingkaran putus-putus.</p>
 @php
  $blok=collect([config('gapura.perusahaan.nama')])->merge($s->daftar('perusahaan'));
  $tglSah=($s->tanggal_terbit ?? $s->tanggal_berangkat)->translatedFormat('j F Y');
 @endphp
 @foreach($blok as $i=>$inst)
 @php $milik = $i === 0; /* nomor 1 = perusahaan penerbit (sendiri): tanggal, nama, jabatan terisi otomatis */ @endphp
 <div class="blk"><b>{{ $i+1 }}</b>
  <div class="bd"><table class="f"><tr><td style="width:50px">Tanggal</td><td class="c">:</td><td>{{ $milik ? $tglSah : '' }}</td></tr><tr><td>Nama</td><td class="c">:</td><td>{{ $milik ? $ttd['nama'] : '' }}</td></tr>
  <tr><td>Jabatan</td><td class="c">:</td><td>{{ $milik ? $ttd['jabatan'] : '' }}</td></tr><tr><td>Instansi</td><td class="c">:</td><td>{{ $inst }}</td></tr></table></div>
  <div class="ar">
   <div class="sg"><div class="sp"></div><div class="lb">Tanda Tangan</div></div>
   <div class="st"><span>Cap /<br>Stempel</span></div>
  </div>
 </div>
 @endforeach

 <div class="foot">
  <div>
   <div style="text-align:center;font-weight:700;margin-bottom:2px">Penggunaan Fasilitas Kendaraan Dinas dari<br>Instansi/Perusahaan yang Dikunjungi<br><i style="font-weight:400;text-decoration:underline">(Lingkari yang Berlaku)</i></div>
   <table class="fs">@foreach(config('gapura.fasilitas') as $k=>$l)@php $ya=($s->fasilitas[$k]??'tidak')==='ya'; @endphp
    <tr><td>- {{ $l }}</td><td class="y"><span class="{{ $ya?'o':'' }}">Ya</span> / <span class="{{ !$ya?'o':'' }}">Tidak</span></td></tr>@endforeach</table>
  </div>
  <div class="ttd">Dikeluarkan di : {{ config('gapura.perusahaan.kota_terbit') }}<br>Tanggal : {{ $s->tanggal_terbit?->translatedFormat('j F Y') }}
   <div style="margin:6px 0 2px">{!! $qr !!}</div>
   <div style="font-size:8px;color:#444">Dokumen divalidasi digital · scan QR untuk verifikasi</div>
   <b style="text-decoration:underline;display:block;margin-top:4px">{{ $ttd['nama'] }}</b>{{ $ttd['jabatan'] }}</div>
 </div>
</div></body></html>
