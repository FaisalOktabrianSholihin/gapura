@extends('layouts.app')
@section('title','SPPD')
@section('content')
<div class="mb-5 flex flex-wrap items-end justify-between gap-3">
  <div><h1 class="font-display text-2xl font-bold text-forest">Surat Perjalanan Dinas</h1><p class="text-sm text-forest/60">{{ auth()->user()->isUmum() ? 'Seluruh pengajuan dari semua divisi.' : 'Pengajuan milik Anda.' }}</p></div>
  @if(auth()->user()->izin('sppd.ajukan'))<a href="{{ route('sppd.create') }}" class="btn"><x-icon n="plus"/>Ajukan SPPD</a>@endif
</div>
<form class="mb-4 flex flex-wrap gap-2" method="get">
  @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}"><input type="hidden" name="dir" value="{{ request('dir') }}">@endif
  <input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIK / nomor" class="inp max-w-xs">
  <select name="status" class="inp max-w-[11rem]" onchange="this.form.submit()"><option value="">Semua status</option>@foreach(['diajukan'=>'Menunggu','disetujui'=>'Disetujui','revisi,ditolak'=>'Revisi / Ditolak','revisi'=>'Revisi saja','ditolak'=>'Ditolak saja'] as $v=>$l)<option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>@endforeach</select>
  @if($divisis->count())<select name="divisi" class="inp max-w-xs" onchange="this.form.submit()"><option value="">Semua divisi</option>@foreach($divisis as $d)<option @selected(request('divisi')===$d)>{{ $d }}</option>@endforeach</select>@endif
  <button class="btn-ghost">Cari</button>
</form>
<div class="card overflow-x-auto !p-0"><table class="w-full">
  <thead><tr>@include('partials.sortth',['col'=>'nama','label'=>'Pemohon'])@include('partials.sortth',['col'=>'divisi','label'=>'Divisi'])@include('partials.sortth',['col'=>'tujuan','label'=>'Tujuan'])@include('partials.sortth',['col'=>'berangkat','label'=>'Berangkat'])@include('partials.sortth',['col'=>'nomor','label'=>'Nomor'])@include('partials.sortth',['col'=>'status','label'=>'Status'])<th class="th"></th></tr></thead>
  @forelse($list as $s)
  <tr class="transition hover:bg-leaf-100/50"><td class="td"><b>{{ $s->nama }}</b><div class="text-xs text-forest/50">{{ $s->nik }}</div></td><td class="td">{{ $s->divisi }}</td>
    <td class="td">{{ $s->daftar('tujuan')->implode(', ') }}</td><td class="td whitespace-nowrap">{{ $s->tanggal_berangkat->format('d/m/Y') }}</td>
    <td class="td text-xs">{{ $s->nomor ?? '—' }}</td><td class="td">@include('partials.status')</td>
    <td class="td whitespace-nowrap text-right"><a class="font-semibold text-leaf hover:underline" href="{{ route('sppd.show',$s) }}">Buka</a>
      @if($s->status==='disetujui')<a class="ml-3 font-semibold text-forest hover:underline" target="_blank" href="{{ route('sppd.cetak',$s) }}">Cetak</a>@endif</td></tr>
  @empty<tr><td colspan="7" class="td py-8 text-center text-forest/50">Belum ada data.</td></tr>@endforelse
</table></div>
<div class="mt-4">{{ $list->links() }}</div>
@endsection
