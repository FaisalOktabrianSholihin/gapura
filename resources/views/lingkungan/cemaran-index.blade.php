@extends('layouts.app')
@section('title','Checklist Cemaran B3')
@section('content')
<div class="mb-5 flex flex-wrap items-end justify-between gap-3"><div><h1 class="text-2xl font-extrabold">Checklist Deteksi Cemaran B3</h1><p class="text-sm text-forest/60">Pemeriksaan potensi pencemaran limbah berbahaya di area kerja.</p></div>
<div class="flex gap-2">@if(auth()->user()->izin('lingkungan.import'))<a href="{{ route('import.form','cemaran') }}" class="btn-ghost"><x-icon n="upload"/>Import</a>@endif @if(auth()->user()->izin('lingkungan.kelola'))<a href="{{ route('cemaran.create') }}" class="btn"><x-icon n="plus"/>Pemeriksaan baru</a>@endif</div></div>
<div class="card overflow-x-auto !p-0"><table class="w-full"><thead><tr><th class="th">Tanggal</th><th class="th">Petugas</th><th class="th">Shift</th><th class="th">Bersih</th><th class="th">Tercemar</th><th class="th">% Kebersihan</th><th class="th"></th></tr></thead>
@forelse($rows as $r)<tr class="hover:bg-leaf-100/40"><td class="td">{{ \Illuminate\Support\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td><td class="td">{{ $r->petugas }}</td><td class="td">{{ $r->shift }}</td>
<td class="td text-green-700">{{ $r->bersih }}</td><td class="td {{ $r->tercemar?'font-bold text-red-600':'' }}">{{ $r->tercemar }}</td><td class="td">{{ number_format($r->persen,2,',','.') }}%</td>
<td class="td text-right"><a href="{{ route('cemaran.show',$r->id) }}" class="font-semibold text-leaf hover:underline">Lihat</a></td></tr>
@empty<tr><td colspan="7" class="td py-8 text-center text-forest/50">Belum ada pemeriksaan.</td></tr>@endforelse</table></div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
