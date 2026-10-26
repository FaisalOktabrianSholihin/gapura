@extends('layouts.app')
@section('title','Hasil Pemeriksaan')
@section('content')
<a href="{{ route('cemaran.index') }}" class="text-sm text-leaf hover:underline">← Riwayat</a>
<div class="mb-4 mt-1 flex flex-wrap items-end justify-between gap-3"><div><h1 class="font-display text-2xl font-bold text-forest">Checklist Deteksi Cemaran B3</h1>
<p class="text-sm text-forest/60">{{ \Illuminate\Support\Carbon::parse($h->tanggal)->translatedFormat('j F Y') }} · Petugas {{ $h->petugas }} · Shift {{ $h->shift ?: '-' }}</p></div>
@if(auth()->user()->izin('lingkungan.kelola'))<form method="post" action="{{ route('cemaran.destroy',$h->id) }}" onsubmit="return confirm('Hapus pemeriksaan ini?')">@csrf @method('DELETE')<button class="btn-danger">Hapus</button></form>@endif</div>
<div class="mb-5 grid gap-3 sm:grid-cols-3"><div class="card"><div class="text-xs text-forest/60">Area bersih</div><div class="font-display text-3xl font-bold text-green-600">{{ $stat['bersih'] }}</div></div>
<div class="card"><div class="text-xs text-forest/60">Area tercemar</div><div class="font-display text-3xl font-bold text-red-600">{{ $stat['tercemar'] }}</div></div>
<div class="card"><div class="text-xs text-forest/60">% Kebersihan</div><div class="font-display text-3xl font-bold">{{ number_format($stat['persen'],2,',','.') }}%</div></div></div>
<div class="card overflow-x-auto !p-0"><table class="w-full"><thead><tr><th class="th">No</th><th class="th">Area/Zona</th><th class="th">Parameter</th><th class="th">Status</th><th class="th">Foto</th><th class="th">Tindakan segera</th><th class="th">Rekomendasi</th><th class="th">PIC</th></tr></thead>
@foreach($items as $i)<tr class="{{ $i->status==='tercemar'?'bg-red-50':'' }}"><td class="td">{{ $i->no }}</td><td class="td">{{ $i->area }}</td><td class="td">{{ $i->parameter }}</td>
<td class="td"><span class="badge {{ $i->status==='bersih'?'bg-green-500 text-white':'bg-red-500 text-white' }}">{{ $i->status }}</span></td>
<td class="td">@if($i->foto)<a href="{{ asset('storage/'.$i->foto) }}" target="_blank"><img src="{{ asset('storage/'.$i->foto) }}" class="h-14 w-14 rounded-lg object-cover" alt="Foto temuan"></a>@endif</td>
<td class="td">{{ $i->tindakan }}</td><td class="td">{{ $i->rekomendasi }}</td><td class="td">{{ $i->pic }}</td></tr>@endforeach</table></div>
@endsection
