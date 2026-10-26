@extends('layouts.app')
@section('title','Checklist Fasilitas TPS B3')
@section('content')
<a href="{{ route('b3') }}" class="text-sm font-semibold text-leaf hover:underline">← Logbook Limbah B3</a>
<h1 class="mb-1 mt-1 text-2xl font-extrabold">Checklist Fasilitas TPS B3</h1>
<p class="mb-5 text-sm text-forest/55">Nilai awal diambil dari pemeriksaan terakhir.</p>
<form method="post" action="{{ route('b3.tps') }}" class="card max-w-3xl">@csrf
 <div class="mb-4 grid gap-3 sm:grid-cols-2"><div><label class="lbl">Tanggal</label><input type="date" name="tanggal" value="{{ now()->toDateString() }}" class="inp"></div><div><label class="lbl">Petugas</label><input name="petugas" value="{{ auth()->user()->name }}" class="inp"></div></div>
 <div class="divide-y divide-gray-100">@foreach(config('gapura.tps_items') as $i=>$l)@php $cur=$tpsVal[$l]??'Baik'; @endphp
  <div class="flex items-center justify-between gap-3 py-2.5 text-sm"><span>{{ $l }}</span><select name="tps[{{ $i }}]" class="inp !w-36"><option @selected($cur==='Baik')>Baik</option><option @selected($cur==='Tidak Baik')>Tidak Baik</option></select></div>@endforeach</div>
 <div class="mt-5 flex gap-3"><button class="btn">Simpan checklist</button><a href="{{ route('b3') }}" class="btn-ghost">Batal</a></div>
</form>
@endsection
