@extends('layouts.app')
@section('title','Pemeriksaan Cemaran B3')
@section('content')
<h1 class="font-display text-2xl font-bold text-forest">Pemeriksaan Cemaran B3</h1>
<p class="mb-4 text-sm text-forest/60">Pilih status tiap parameter. Bila “tercemar”, isi tindakan segera, rekomendasi, dan lampirkan foto temuan.</p>
<form method="post" action="{{ route('cemaran.store') }}" enctype="multipart/form-data" x-data="{bad:0}" class="space-y-4">@csrf
 <div class="card grid gap-3 sm:grid-cols-3"><div><label class="lbl">Tanggal Pemeriksaan</label><input type="date" name="tanggal" value="{{ now()->toDateString() }}" required class="inp"></div>
  <div><label class="lbl">Petugas</label><input name="petugas" value="{{ auth()->user()->name }}" required class="inp"></div>
  <div><label class="lbl">Shift</label><select name="shift" class="inp"><option>Pagi</option><option>Siang</option><option>Malam</option></select></div></div>
 @php $last=null; @endphp
 @foreach(config('gapura.cemaran_items') as $i=>[$area,$param,$pic])
  @if($area!==$last)<h2 class="font-display pt-3 font-bold text-forest">{{ $area }}</h2>@php $last=$area; @endphp @endif
  <div class="card !p-4 transition" x-data="{st:'bersih'}" :class="st==='tercemar'&&'!border-red-300 !bg-red-50/60'">
   <div class="flex flex-wrap items-center justify-between gap-3"><div class="text-sm"><span class="mr-2 text-forest/40">{{ $i+1 }}.</span>{{ $param }} <span class="text-xs text-forest/45">· PIC {{ $pic }}</span></div>
    <div class="inline-flex rounded-lg bg-forest/5 p-1 text-xs font-bold">
     <label class="cursor-pointer rounded-md px-3 py-1.5 transition" :class="st==='bersih'?'bg-green-500 text-white':'text-forest/60'"><input class="sr-only" type="radio" name="status[{{ $i }}]" value="bersih" x-model="st" @change="bad=document.querySelectorAll('[value=tercemar]:checked').length">Bersih</label>
     <label class="cursor-pointer rounded-md px-3 py-1.5 transition" :class="st==='tercemar'?'bg-red-500 text-white':'text-forest/60'"><input class="sr-only" type="radio" name="status[{{ $i }}]" value="tercemar" x-model="st" @change="bad=document.querySelectorAll('[value=tercemar]:checked').length">Tercemar</label></div></div>
   <div x-show="st==='tercemar'" x-cloak x-transition class="mt-3 grid gap-3 sm:grid-cols-3">
    <div><label class="lbl">Tindakan segera</label><input name="tindakan[{{ $i }}]" class="inp"></div>
    <div><label class="lbl">Rekomendasi tindakan</label><input name="rekomendasi[{{ $i }}]" class="inp"></div>
    <div><label class="lbl">Foto temuan</label><input type="file" accept="image/*" capture="environment" name="foto[{{ $i }}]" class="inp !p-1.5 text-xs"></div></div>
  </div>
 @endforeach
 <div class="sticky bottom-3 flex items-center justify-between rounded-2xl bg-forest p-4 text-white shadow-xl"><span class="text-sm">Temuan tercemar: <b x-text="bad"></b></span><button class="rounded-lg bg-lime px-5 py-2 text-sm font-bold text-forest active:scale-95">Simpan pemeriksaan</button></div>
</form>
@endsection
