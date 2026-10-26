<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Verifikasi SPPD</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>@keyframes pop{0%{transform:scale(.4);opacity:0}70%{transform:scale(1.08)}100%{transform:scale(1);opacity:1}}.pop{animation:pop .55s cubic-bezier(.3,1.4,.5,1) both}@media (prefers-reduced-motion:reduce){.pop{animation:none}}</style></head>
<body class="min-h-screen bg-slate-100 p-5 text-slate-800"><div class="mx-auto mt-6 max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
 <div class="p-6 text-center text-white {{ $valid?'bg-green-700':'bg-red-700' }}">
  <div class="pop mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-white text-4xl {{ $valid?'text-green-700':'text-red-700' }}">{{ $valid?'✓':'✕' }}</div>
  <h1 class="text-xl font-bold">{{ $valid?'TELAH DIVALIDASI':'TIDAK VALID' }}</h1>
  <p class="text-sm opacity-90">{{ $valid?'Surat Perjalanan Dinas ini sah dan diterbitkan oleh '.config('gapura.perusahaan.nama'):'Dokumen tidak ditemukan atau belum/tidak lagi disetujui.' }}</p></div>
 @if($valid)<dl class="space-y-3 p-6 text-sm">
  @foreach([['Nomor',$s->nomor],['Nama',$s->nama.' ('.$s->nik.')'],['Jabatan / Divisi',$s->jabatan.' · '.$s->divisi],['Tujuan',$s->daftar('tujuan')->implode(', ')],
   ['Periode',$s->tanggal_berangkat->translatedFormat('j M Y').' – '.$s->tanggal_kembali->translatedFormat('j M Y')],['Diterbitkan',$s->tanggal_terbit?->translatedFormat('j F Y').' · '.config('gapura.perusahaan.kota_terbit')],['Penandatangan',$ttd['nama'].', '.$ttd['jabatan']]] as [$l,$v])
  <div><dt class="text-xs text-slate-500">{{ $l }}</dt><dd class="font-semibold">{{ $v }}</dd></div>@endforeach</dl>@endif
</div></body></html>
