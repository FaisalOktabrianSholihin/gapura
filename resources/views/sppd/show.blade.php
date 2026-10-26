@extends('layouts.app')
@section('title','Detail SPPD')
@section('content')
@php $umum=auth()->user()->izin('sppd.setujui'); $dl=fn($t)=>$s->daftar($t); @endphp
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
  <div><a href="{{ route('sppd.index') }}" class="text-sm text-leaf hover:underline">← Daftar SPPD</a>
    <h1 class="font-display mt-1 text-2xl font-bold text-forest">{{ $s->nomor ?? 'SPPD (belum bernomor)' }}</h1></div>
  <div class="flex items-center gap-2">@include('partials.status')
    @if($s->status==='disetujui')<a target="_blank" href="{{ route('sppd.cetak',$s) }}" class="btn">Cetak surat</a>@endif
    @if($s->bisa_diubah && $s->user_id===auth()->id())<a href="{{ route('sppd.edit',$s) }}" class="btn-ghost">{{ $s->status==='diajukan'?'Ubah':'Revisi' }}</a>@endif</div>
</div>
@if(in_array($s->status,['revisi','ditolak']) && $s->catatan_admin)<div class="mb-4 rounded-xl border border-orange-200 bg-orange-50 p-4 text-sm"><b>Catatan Bagian Umum:</b> {{ $s->catatan_admin }}</div>@endif
<div class="grid gap-5 lg:grid-cols-3">
  <div class="card lg:col-span-2"><dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
    @foreach([['Nama',$s->nama],['NIK',$s->nik],['Jabatan',$s->jabatan],['Divisi',$s->divisi],['No. Surat Tugas',$s->nomor_surat_tugas],['Jenis Perjalanan',$s->jenis_perjalanan],
      ['Berangkat',$s->tanggal_berangkat->translatedFormat('j F Y')],['Kembali',$s->tanggal_kembali->translatedFormat('j F Y')],['Transportasi',$s->transportasi]] as [$l,$v])
      <div><dt class="text-xs text-forest/55">{{ $l }}</dt><dd class="font-medium">{{ $v ?: '—' }}</dd></div>@endforeach
    @foreach([['tujuan','Tujuan Dinas'],['keperluan','Keperluan Dinas'],['rombongan','Rombongan'],['perusahaan','Perusahaan/Instansi yang Dituju']] as [$t,$l])
      <div><dt class="text-xs text-forest/55">{{ $l }}</dt><dd>@forelse($dl($t) as $i=>$x)<div>{{ $i+1 }}. {{ $x }}</div>@empty—@endforelse</dd></div>@endforeach
  </dl>
  <div class="mt-5 border-t border-forest/10 pt-4"><div class="mb-2 text-xs text-forest/55">Fasilitas kendaraan dinas</div>
    <div class="grid gap-1 text-sm sm:grid-cols-2">@foreach(config('gapura.fasilitas') as $k=>$l)<div class="flex justify-between gap-2"><span>{{ $l }}</span><b>{{ ($s->fasilitas[$k]??'tidak')==='ya'?'Ya':'Tidak' }}</b></div>@endforeach</div></div></div>

  @if($umum)
  <form method="post" action="{{ route('sppd.keputusan',$s) }}" class="card h-fit space-y-3" x-data="{aksi:'setuju'}">@csrf
    <h2 class="font-display font-bold text-forest">Keputusan Bagian Umum</h2>
    <div class="grid grid-cols-3 gap-1 rounded-lg bg-forest/5 p-1 text-xs font-semibold">
      @foreach(['setuju'=>'ACC','revisi'=>'Revisi','tolak'=>'Tolak'] as $v=>$l)
      <label class="cursor-pointer rounded-md py-1.5 text-center transition" :class="aksi==='{{ $v }}'?'bg-white shadow text-forest':'text-forest/60'"><input type="radio" class="sr-only" name="aksi" value="{{ $v }}" x-model="aksi">{{ $l }}</label>@endforeach</div>
    <div x-show="aksi==='setuju'" x-transition><label class="lbl">Nomor surat</label><input name="nomor" class="inp" value="{{ $s->nomor ?? $nomorSaran }}"></div>
    <div><label class="lbl"><span x-text="aksi==='setuju'?'Catatan (opsional)':'Catatan untuk pemohon (wajib)'"></span></label><textarea name="catatan" rows="3" class="inp"></textarea></div>
    <button class="btn w-full" x-text="aksi==='setuju'?'Setujui & terbitkan':(aksi==='revisi'?'Minta revisi':'Tolak')"></button>
    <p class="text-xs text-forest/55">Setelah di-ACC, QR pada surat otomatis menampilkan status "telah divalidasi".</p>
  </form>
  @else
  <div class="card h-fit text-sm"><h2 class="font-display mb-2 font-bold text-forest">Status</h2>
    @if($s->status==='disetujui')<p>Disetujui {{ $s->approved_at?->translatedFormat('j F Y') }}. Silakan cetak surat lalu minta tanda tangan basah &amp; stempel di instansi tujuan.</p>
    @elseif($s->status==='diajukan')<p>Menunggu persetujuan Bagian Umum.</p>@else<p>Perbaiki sesuai catatan lalu ajukan ulang.</p>@endif</div>
  @endif
</div>
@endsection
