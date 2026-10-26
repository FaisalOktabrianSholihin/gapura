@extends('layouts.app')
@section('title','Dashboard')
@section('content')
@php $u=auth()->user(); $nama=\Illuminate\Support\Str::of($u->name)->before(' '); @endphp
{{-- banner --}}
<div class="relative overflow-hidden rounded-3xl p-7 text-white" style="background:linear-gradient(120deg,#064a25 0%,#0b6a35 60%,#118043 100%)">
  <div class="absolute -right-10 -top-16 h-64 w-64 rounded-full bg-white/5"></div><div class="absolute right-24 top-20 h-40 w-40 rounded-full bg-lime/10"></div>
  <div class="relative flex flex-wrap items-end justify-between gap-6">
    <div>
      <div class="text-sm text-white/75" x-data="{t:''}" x-init="const f=()=>t=new Date().toLocaleString('id-ID',{timeZone:'Asia/Jakarta',weekday:'long',day:'numeric',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}).replace(/\./g,':').replace(' pukul',' ·')+' WIB';f();setInterval(f,1000)" x-text="t">{{ now()->translatedFormat('l, j F Y') }}</div>
      <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Halo, {{ $nama }} 👋</h1>
      <div class="mt-3 flex flex-wrap gap-2"><span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold ring-1 ring-white/20">{{ $u->peran?->nama ?? 'Tanpa role' }}</span>
        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold ring-1 ring-white/20">{{ $u->isUmum() ? 'Semua Divisi' : ($u->karyawan?->divisi?->nama_divisi ?? 'Divisi -') }}</span></div>
    </div>
    @if($u->izin('sppd.ajukan'))<a href="{{ route('sppd.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-forest shadow-lg transition hover:-translate-y-0.5 active:scale-95"><x-icon n="plus"/>Ajukan SPPD</a>@endif
  </div>
</div>

@foreach($perlu as $p)
<a href="{{ route('sppd.show',$p) }}" class="mt-4 flex items-start gap-3 rounded-2xl border border-orange-200 bg-orange-50 p-4 text-sm transition hover:border-orange-400"><x-icon n="alert" c="mt-0.5 h-5 w-5 text-orange-600"/>
  <span><b class="text-orange-900">SPPD Anda {{ $p->status==='ditolak'?'ditolak':'perlu revisi' }}.</b> <span class="text-orange-900/80">{{ \Illuminate\Support\Str::limit($p->catatan_admin,140) }}</span></span></a>
@endforeach

{{-- statistik --}}
<div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
 @foreach([['Total SPPD',$stat['total'],'Seluruh pengajuan','file','#22c55e','bg-green-50 text-green-700'],['Disetujui',$stat['disetujui'],'Siap dicetak','check','#14b8a6','bg-teal-50 text-teal-700'],
  ['Menunggu',$stat['diajukan'],'Belum diputuskan','clock','#f59e0b','bg-amber-50 text-amber-600'],['Revisi / Ditolak',$stat['revisi'],'Perlu tindak lanjut','alert','#f43f5e','bg-rose-50 text-rose-600']] as [$l,$n,$d,$i,$c,$t])
 <div class="stat" style="--c:{{ $c }}"><div class="flex items-start justify-between"><div class="text-xs font-semibold text-forest/60">{{ $l }}</div><span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $t }}"><x-icon :n="$i" c="h-5 w-5"/></span></div>
  <div class="mt-1 text-3xl font-extrabold" x-data="{v:0}" x-init="if({{ $n }}>0){let t=setInterval(()=>{v++;if(v>={{ $n }})clearInterval(t)},Math.max(12,600/{{ max($n,1) }}))}" x-text="v">0</div><div class="mt-1 text-xs text-forest/45">{{ $d }}</div></div>
 @endforeach
</div>

{{-- grafik --}}
<div class="mt-5 grid gap-5 lg:grid-cols-5">
  <div class="card lg:col-span-2"><div class="font-bold">Komposisi Status</div><div class="mb-2 text-xs text-forest/50">Jumlah SPPD per status</div>
    <div class="relative mx-auto h-52 max-w-[240px]"><canvas id="c1"></canvas></div></div>
  <div class="card lg:col-span-3"><div class="font-bold">SPPD Diajukan</div><div class="mb-2 text-xs text-forest/50">6 bulan terakhir</div><div class="h-52"><canvas id="c2"></canvas></div></div>
</div>

{{-- menu --}}
<h2 class="mb-3 mt-8 text-sm font-bold">Menu</h2>
<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
 @php $menu=[['SPPD','Ajukan & pantau surat dinas','plane','sppd.index','sppd.ajukan']];
  if($u->izin('lingkungan.lihat')) $menu=array_merge($menu,[['Monitoring IPAL','Data harian air limbah','droplets','ipal','lingkungan.lihat'],['Cemaran B3','Checklist area kerja','clipboard','cemaran.index','lingkungan.lihat'],['Logbook B3','Masuk, keluar, neraca','archive','b3','lingkungan.lihat'],['Limbah Non-B3','Organik & domestik','leaf','nonb3','lingkungan.lihat'],['Dashboard Lingkungan','Ringkasan kepatuhan','dashboard','lingkungan','lingkungan.lihat']]); @endphp
 @foreach($menu as [$l,$d,$i,$r,$iz])
 <a href="{{ route($r) }}" class="group flex items-center gap-3 rounded-2xl border border-gray-200/80 bg-white p-4 transition hover:-translate-y-0.5 hover:border-leaf/50 hover:shadow-md"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-leaf transition group-hover:bg-leaf group-hover:text-white"><x-icon :n="$i" c="h-5 w-5"/></span>
  <span class="leading-tight"><span class="block text-sm font-bold">{{ $l }}</span><span class="text-xs text-forest/50">{{ $d }}</span></span></a>
 @endforeach
</div>

<div class="mt-8 grid gap-5 lg:grid-cols-3">
  <div class="card lg:col-span-2 !p-0"><div class="flex items-center justify-between p-5 pb-3"><div class="font-bold">Pengajuan Terbaru</div><a href="{{ route('sppd.index') }}" class="text-xs font-semibold text-leaf hover:underline">Semua</a></div>
   <div class="overflow-x-auto"><table class="w-full">
    @forelse($terbaru as $s)<tr><td class="td"><b>{{ $s->nama }}</b><div class="text-xs text-forest/50">{{ $s->divisi }}</div></td><td class="td">{{ $s->daftar('tujuan')->first() }}</td><td class="td whitespace-nowrap">{{ $s->tanggal_berangkat->format('d/m/Y') }}</td><td class="td">@include('partials.status')</td><td class="td text-right"><a class="font-semibold text-leaf hover:underline" href="{{ route('sppd.show',$s) }}">Buka</a></td></tr>
    @empty<tr><td class="td py-8 text-center text-forest/50">Belum ada pengajuan.</td></tr>@endforelse</table></div></div>
  <div class="card"><div class="mb-3 flex items-center justify-between"><div class="font-bold">Aktivitas Terbaru</div>@if($u->izin('log.lihat'))<a href="{{ route('activity') }}" class="text-xs font-semibold text-leaf hover:underline">Semua</a>@endif</div>
   @forelse($aktivitas as $a)<div class="flex gap-3 border-t border-gray-100 py-2.5 first:border-0"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-leaf"></span><div class="text-xs leading-snug"><b>{{ $a->user_nama }}</b> <span class="text-forest/60">{{ \Illuminate\Support\Str::limit($a->deskripsi,70) }}</span><div class="text-forest/40">{{ \Illuminate\Support\Carbon::parse($a->created_at)->diffForHumans() }}</div></div></div>
   @empty<p class="py-6 text-center text-xs text-forest/45">{{ $u->izin('log.lihat') ? 'Belum ada aktivitas.' : 'Riwayat aktivitas hanya untuk administrator.' }}</p>@endforelse</div>
</div>

<form method="post" action="{{ route('password') }}" class="card mt-5 max-w-xl" x-data="{o:false}">@csrf
  <button type="button" @click="o=!o" class="flex items-center gap-2 text-sm font-semibold text-leaf"><x-icon n="key"/><span x-text="o?'Tutup':'Ganti password'"></span></button>
  <div x-show="o" x-cloak x-transition class="mt-3 grid gap-3 sm:grid-cols-3"><input type="password" name="password" placeholder="Password baru" class="inp"><input type="password" name="password_confirmation" placeholder="Ulangi password" class="inp"><button class="btn">Simpan</button></div>
</form>

<script>
const st={disetujui:['Disetujui','#22c55e'],diajukan:['Menunggu','#f59e0b'],revisi:['Revisi','#fb923c'],ditolak:['Ditolak','#f43f5e']}, k=@json($komposisi);
const keys=Object.keys(k);
new Chart(document.getElementById('c1'),{type:'doughnut',data:{labels:keys.map(x=>st[x]?.[0]??x),datasets:[{data:keys.map(x=>k[x]),backgroundColor:keys.map(x=>st[x]?.[1]??'#94a3b8'),borderWidth:0}]},options:{cutout:'68%',maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:10,usePointStyle:true}}}}});
new Chart(document.getElementById('c2'),{type:'line',data:{labels:@json($seri['label']),datasets:[{label:'SPPD',data:@json($seri['data']),borderColor:'#15803d',backgroundColor:'rgba(34,197,94,.14)',fill:true,tension:.4,pointBackgroundColor:'#fff',pointBorderColor:'#15803d',pointBorderWidth:2,pointRadius:4}]},options:{maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}},x:{grid:{display:false}}}}});
</script>
@endsection
