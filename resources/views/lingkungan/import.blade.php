@extends('layouts.app')
@section('title','Import Data')
@section('content')
@php $hasil=session('hasil'); @endphp
<a href="{{ route($kembali) }}" class="text-sm font-semibold text-leaf hover:underline">← Kembali</a>
<h1 class="mb-1 mt-1 text-2xl font-extrabold">Import Data</h1>
<p class="mb-4 text-sm text-forest/55">{{ $judul }} — suntik banyak data sekaligus dari file Excel (.xlsx) atau CSV.</p>

<div class="mb-5 grid gap-3 sm:grid-cols-3">
 @foreach([['1','Unduh template','Template Excel sudah berisi judul kolom, dropdown pilihan, dan petunjuk.'],['2','Isi datanya','Isi mulai baris ke-2 pada sheet “Data”. Judul hijau wajib diisi.'],['3','Unggah file','Pilih file di bawah lalu klik Import. Baris yang salah dilaporkan.']] as [$n,$t,$d])
 <div class="flex gap-3 rounded-2xl border border-gray-200/80 bg-white p-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-leaf text-sm font-extrabold text-white">{{ $n }}</span><div class="leading-snug"><div class="text-sm font-bold">{{ $t }}</div><div class="text-xs text-forest/55">{{ $d }}</div></div></div>
 @endforeach
</div>

@if($hasil)
<div class="card mb-5 {{ $hasil['ok']>0 ? '!border-green-300 !bg-green-50/60' : '!border-red-200 !bg-red-50/60' }}">
  <div class="flex items-center gap-2 font-bold"><x-icon :n="$hasil['ok']>0?'check':'alert'" c="h-5 w-5"/>{{ $hasil['ok'] }} baris berhasil diimpor{{ $hasil['errTotal']?', '.$hasil['errTotal'].' dilewati':'' }}</div>
  @if(!empty($hasil['info']))<p class="mt-1 text-sm text-forest/70">{{ $hasil['info'] }}</p>@endif
  @if($hasil['err'])<ul class="mt-3 max-h-60 list-inside list-disc overflow-y-auto text-sm text-red-800">@foreach($hasil['err'] as $e)<li>{{ $e }}</li>@endforeach</ul>@if($hasil['errTotal']>count($hasil['err']))<p class="mt-1 text-xs text-forest/50">…dan {{ $hasil['errTotal']-count($hasil['err']) }} lainnya.</p>@endif @endif
  @if($hasil['ok']>0)<a href="{{ route($kembali) }}" class="btn mt-4">Lihat data</a>@endif
</div>
@endif

<div class="grid gap-5 lg:grid-cols-5">
 <form method="post" action="{{ route('import.store',$key) }}" enctype="multipart/form-data" class="card space-y-4 lg:col-span-3" x-data="{n:''}">@csrf
  @if($key==='cemaran')
   <div class="grid gap-3 sm:grid-cols-3"><div><label class="lbl">Tanggal pemeriksaan *</label><input type="date" name="tanggal" value="{{ old('tanggal',now()->toDateString()) }}" required class="inp"></div>
    <div><label class="lbl">Petugas *</label><input name="petugas" value="{{ old('petugas',auth()->user()->name) }}" required class="inp"></div>
    <div><label class="lbl">Shift</label><select name="shift" class="inp"><option>Pagi</option><option>Siang</option><option>Malam</option></select></div></div>
  @endif
  <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-green-300 bg-green-50/40 px-4 py-10 text-center transition hover:bg-green-50">
   <span class="text-leaf"><x-icon n="upload" c="h-8 w-8"/></span><span class="text-sm font-semibold" x-text="n||'Pilih file .xlsx atau .csv'"></span><span class="text-xs text-forest/50">Maksimal 10 MB</span>
   <input type="file" name="file" accept=".xlsx,.csv,.txt" required class="sr-only" @change="n=$event.target.files[0]?.name"></label>
  <div><label class="lbl">Nama sheet (opsional, khusus .xlsx)</label><input name="sheet" value="{{ old('sheet') }}" placeholder="Kosongkan untuk sheet pertama" class="inp"></div>
  <div class="flex flex-wrap gap-3"><button class="btn"><x-icon n="upload"/>Import sekarang</button><a href="{{ route('import.template',$key) }}" class="btn-ghost"><x-icon n="sheet"/>Unduh template Excel</a><a href="{{ route('import.template',[$key,'format'=>'csv']) }}" class="btn-ghost !border-transparent !bg-transparent !px-2 text-forest/60">atau versi CSV</a></div>
 </form>
 <div class="card lg:col-span-2 text-sm"><div class="mb-2 font-bold">Kolom yang dikenali</div>
  <ul class="space-y-1 text-forest/75">@foreach($fields as $f)<li class="flex justify-between gap-2"><span>{{ $f[1] }}</span><span class="text-xs {{ in_array('req',$f)?'font-semibold text-red-600':'text-forest/40' }}">{{ in_array('req',$f)?'wajib':'opsional' }}</span></li>@endforeach</ul>
  <div class="mt-4 space-y-1.5 border-t border-gray-100 pt-3 text-xs leading-relaxed text-forest/60">
   <p>• Baris judul kolom dicari otomatis, jadi file Excel lama Anda bisa langsung dipakai.</p>
   <p>• Tanggal: <b>dd/mm/yyyy</b> atau tanggal Excel. Angka: koma atau titik desimal.</p>
   <p>• Baris kosong atau yang hanya berisi nama jenis tanpa tanggal/angka dilewati.</p>
   @if($key==='cemaran')<p>• Cocokkan lewat kolom <b>No</b> (1–{{ count(config('gapura.cemaran_items')) }}) atau <b>Parameter</b>. Status "tercemar" atau "bersih".</p>@endif
  </div></div>
</div>
@endsection
