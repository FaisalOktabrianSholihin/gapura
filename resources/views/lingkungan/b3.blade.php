@extends('layouts.app')
@section('title','Logbook Limbah B3')
@section('content')
@php $kap=config('gapura.kapasitas_tps_b3'); $pct=min(100,$kap?$tersimpan/$kap*100:0); $tpsVal=$tps?json_decode($tps->items,true):[]; @endphp
<h1 class="text-2xl font-extrabold">Logbook Limbah B3</h1>
<p class="mb-4 text-sm text-forest/60">Pelacakan masuk, keluar, dan sisa limbah B3 di TPS.</p>
<div class="card mb-6"><div class="mb-2 flex justify-between text-sm"><span>Tersimpan di TPS</span><b>{{ number_format($tersimpan,2,',','.') }} / {{ $kap }} kg</b></div>
 <div class="h-3 overflow-hidden rounded-full bg-forest/10"><div class="h-full rounded-full transition-all duration-700 {{ $pct>=90?'bg-red-500':($pct>=70?'bg-amber-400':'bg-leaf') }}" style="width:{{ $pct }}%"></div></div></div>

@include('partials.logbar',['key'=>'b3-masuk']) @include('partials.logtable',['key'=>'b3-masuk','rows'=>$masuk])
<div class="mt-8"></div>@include('partials.logbar',['key'=>'b3-keluar']) @include('partials.logtable',['key'=>'b3-keluar','rows'=>$keluar])

<h2 class="font-display mb-2 mt-8 font-bold text-forest">C. Neraca Limbah B3</h2>
<div class="card overflow-x-auto !p-0"><table class="w-full"><thead><tr><th class="th">Jenis Limbah</th><th class="th text-right">Total Masuk (kg)</th><th class="th text-right">Total Keluar (kg)</th><th class="th text-right">Sisa di TPS (kg)</th><th class="th">Status</th></tr></thead>
@foreach($neraca as $n)<tr><td class="td">{{ $n['jenis'] }}</td><td class="td text-right">{{ number_format($n['masuk'],2,',','.') }}</td><td class="td text-right">{{ number_format($n['keluar'],2,',','.') }}</td><td class="td text-right font-semibold">{{ number_format($n['sisa'],2,',','.') }}</td>
<td class="td"><span class="badge {{ $n['sisa']>0?'bg-amber-100 text-amber-800':'bg-slate-100 text-slate-600' }}">{{ $n['sisa']>0?'Tersimpan':'Kosong' }}</span></td></tr>@endforeach</table></div>

<div class="mb-3 mt-8 flex flex-wrap items-center justify-between gap-2"><h2 class="text-base font-bold">D. Checklist Fasilitas TPS B3</h2>
 @if(auth()->user()->izin('lingkungan.kelola'))<a href="{{ route('b3.tps.form') }}" class="btn !py-1.5"><x-icon n="plus"/>Isi Checklist</a>@endif</div>
<div class="card">@if($tps)<div class="mb-3 text-xs text-forest/55">Pemeriksaan terakhir: {{ \Illuminate\Support\Carbon::parse($tps->tanggal)->format('d/m/Y') }} · {{ $tps->petugas }}</div>
 <div class="divide-y divide-gray-100">@foreach(config('gapura.tps_items') as $l)@php $cur=$tpsVal[$l]??'-'; @endphp<div class="flex items-center justify-between py-2 text-sm"><span>{{ $l }}</span><span class="badge {{ $cur==='Baik'?'bg-green-100 text-green-800':'bg-red-100 text-red-700' }}">{{ $cur }}</span></div>@endforeach</div>
 @else<p class="py-4 text-center text-sm text-forest/50">Belum ada checklist.</p>@endif</div>
@endsection
