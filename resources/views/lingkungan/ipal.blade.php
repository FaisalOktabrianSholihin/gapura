@extends('layouts.app')
@section('title','Monitoring IPAL')
@section('content')
<h1 class="text-2xl font-extrabold">Monitoring IPAL</h1>
<p class="mb-4 text-sm text-forest/60">Data harian kualitas air limbah. Nilai pH di luar {{ config('gapura.ipal.ph_min') }}–{{ config('gapura.ipal.ph_max') }} otomatis ditandai merah (melebihi baku mutu).</p>
@include('partials.logbar',['key'=>'ipal'])
@if($seri->count())<div class="card mb-5"><div class="h-52"><canvas id="ch"></canvas></div></div>@endif
@include('partials.logtable',['key'=>'ipal','rows'=>$rows])
@if($seri->count())<script>
new Chart(document.getElementById('ch'),{type:'line',data:{labels:@json($seri->map(fn($r)=>\Illuminate\Support\Carbon::parse($r->tanggal)->format('d/m'))),
 datasets:[{label:'pH outlet',data:@json($seri->pluck('ph_outlet')),borderColor:'#2e9e4f',tension:.3},{label:'pH inlet',data:@json($seri->pluck('ph_inlet')),borderColor:'#94a3b8',tension:.3},
 {label:'Debit (m³)',data:@json($seri->pluck('debit')),borderColor:'#0f3d2a',yAxisID:'y2',tension:.3}]},
 options:{maintainAspectRatio:false,scales:{y:{title:{display:true,text:'pH'}},y2:{position:'right',grid:{display:false},title:{display:true,text:'m³'}}},plugins:{legend:{position:'bottom'}}}});
</script>@endif
@endsection
