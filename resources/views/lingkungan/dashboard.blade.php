@extends('layouts.app')
@section('title','Monitoring Lingkungan')
@section('content')
@php
 $p=config('gapura.perusahaan'); $c=config('gapura.ipal'); $kap=config('gapura.kapasitas_tps_b3');
 $ph=$ipal?->ph_outlet; $phOk=$ph!==null && $ph>=$c['ph_min'] && $ph<=$c['ph_max'];
 $deb=$ipal?->debit; $debOk=$deb!==null && $deb<=$c['debit_izin'];
 $keb=$cemStat['persen']??null;
 $rows=[
  ['pH Outlet IPAL',$c['ph_min'].' - '.$c['ph_max'],$ph!==null?number_format($ph,2,',','.'):'—',$ph===null?['Belum ada data','bg-slate-200 text-slate-700']:($phOk?['✓ Normal','bg-green-500 text-white']:['Di luar baku mutu','bg-red-500 text-white'])],
  ['Debit Air Limbah','≤ '.$c['debit_izin'].' m³ (izin)',$deb!==null?number_format($deb,2,',','.').' m³':'—',$deb===null?['Belum ada data','bg-slate-200 text-slate-700']:($debOk?['Sesuai izin','bg-green-500 text-white']:['Melebihi izin','bg-red-500 text-white'])],
  ['Limbah B3 Tersimpan','< '.$kap.' kg (kapasitas TPS)',number_format($tersimpan,2,',','.').' kg',$tersimpan<$kap?['Aman','bg-green-500 text-white']:['TPS penuh','bg-red-500 text-white']],
  ['Kebersihan Area','100% Baik',$keb!==null?number_format($keb,2,',','.').'%':'—',$keb===null?['Belum ada data','bg-slate-200 text-slate-700']:($keb>=90?['Baik','bg-green-500 text-white']:($keb>=75?['Cukup','bg-amber-400 text-white']:['Kurang','bg-red-500 text-white']))],
 ];
@endphp
<div class="relative overflow-hidden rounded-3xl p-7 text-white" style="background:linear-gradient(120deg,#064a25 0%,#0b6a35 60%,#118043 100%)">
  <svg class="absolute -right-6 -top-4 h-56 opacity-20" viewBox="0 0 240 220" fill="none" stroke="#b7e34a" stroke-width="3"><path d="M30 210V90M210 210V90M20 90h40M180 90h40M30 90c0-45 40-70 90-70s90 25 90 70M60 210V120M180 210V120M60 120c0-28 25-45 60-45s60 17 60 45M20 210h200"/></svg>
  <h1 class="font-display text-3xl font-bold">Monitoring Lingkungan</h1>
  <p class="mt-1 text-white/70">Sistem monitoring kepatuhan lingkungan perusahaan</p>
  <dl class="mt-5 grid gap-x-8 gap-y-2 text-sm sm:grid-cols-2">
    <div><dt class="text-white/55">Nama Perusahaan</dt><dd class="font-semibold">{{ $p['nama'] }}</dd></div>
    <div><dt class="text-white/55">Bidang Industri</dt><dd class="font-semibold">{{ $p['bidang'] }}</dd></div>
    <div><dt class="text-white/55">Alamat</dt><dd class="font-semibold">{{ $p['alamat'] }}</dd></div>
    <div><dt class="text-white/55">Periode Monitoring</dt><dd class="font-semibold">{{ $periode }}</dd></div></dl>
</div>

<h2 class="font-display mb-3 mt-8 text-lg font-bold text-forest">Ringkasan Indikator Utama</h2>
<div class="card overflow-x-auto !p-0"><table class="w-full"><thead><tr><th class="th">Indikator</th><th class="th">Target</th><th class="th">Status Terakhir</th><th class="th">Keterangan</th></tr></thead>
 @foreach($rows as [$a,$b,$v,$k])<tr><td class="td font-semibold">{{ $a }}</td><td class="td">{{ $b }}</td><td class="td">{{ $v }}</td><td class="td"><span class="badge {{ $k[1] }}">{{ $k[0] }}</span></td></tr>@endforeach
 <tr><td class="td font-semibold">Limbah Non-B3</td><td class="td">Terkelola</td><td class="td">{{ number_format($organik,2,',','.') }} kg organik · {{ number_format($domestik,2,',','.') }} m³ domestik</td><td class="td"><span class="badge bg-leaf-100 text-forest">Tercatat</span></td></tr>
</table></div>

<div class="mt-6 grid gap-5 lg:grid-cols-3">
  <div class="card lg:col-span-2"><h3 class="font-display mb-2 font-bold text-forest">Tren pH outlet IPAL (14 data terakhir)</h3>
    @if($seri->count())<div class="h-56"><canvas id="ch"></canvas></div>@else<p class="py-10 text-center text-sm text-forest/50">Belum ada data IPAL.</p>@endif</div>
  <div class="card"><h3 class="font-display mb-3 font-bold text-forest">Menu</h3><div class="space-y-2 text-sm font-semibold">
    @foreach([['Monitoring IPAL','ipal','Data harian kualitas air limbah'],['Checklist Cemaran B3','cemaran.index','Deteksi cemaran B3 di area kerja'],['Logbook Limbah B3','b3','Pelacakan limbah berbahaya'],['Limbah Non-B3','nonb3','Organik & domestik']] as [$l,$r,$d])
    <a href="{{ route($r) }}" class="block rounded-xl border border-forest/10 p-3 transition hover:-translate-y-0.5 hover:border-leaf hover:bg-leaf-100/50">{{ $l }}<div class="text-xs font-normal text-forest/55">{{ $d }}</div></a>@endforeach</div></div>
</div>
@if($seri->count())
<script>
new Chart(document.getElementById('ch'),{type:'line',data:{labels:@json($seri->map(fn($r)=>\Illuminate\Support\Carbon::parse($r->tanggal)->format('d/m'))),
 datasets:[{label:'pH outlet',data:@json($seri->pluck('ph_outlet')),borderColor:'#2e9e4f',backgroundColor:'#2e9e4f22',fill:true,tension:.3,pointRadius:3},
 {label:'Batas min',data:@json($seri->map(fn()=>$c['ph_min'])),borderColor:'#e5484d',borderDash:[5,5],pointRadius:0},
 {label:'Batas maks',data:@json($seri->map(fn()=>$c['ph_max'])),borderColor:'#e5484d',borderDash:[5,5],pointRadius:0}]},
 options:{maintainAspectRatio:false,scales:{y:{suggestedMin:4,suggestedMax:11}},plugins:{legend:{position:'bottom'}}}});
</script>@endif
@endsection
