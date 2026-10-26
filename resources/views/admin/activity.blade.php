@extends('layouts.app')
@section('title','Log Aktivitas')
@section('content')
@php $warna=['login'=>'bg-blue-100 text-blue-800','logout'=>'bg-gray-100 text-gray-700','buat'=>'bg-green-100 text-green-800','ubah'=>'bg-amber-100 text-amber-800','hapus'=>'bg-red-100 text-red-700','setujui'=>'bg-emerald-100 text-emerald-800','revisi'=>'bg-orange-100 text-orange-800','tolak'=>'bg-red-100 text-red-700','import'=>'bg-violet-100 text-violet-800','cetak'=>'bg-sky-100 text-sky-800']; @endphp
<div class="mb-5"><h1 class="text-2xl font-extrabold">Log Aktivitas</h1><p class="text-sm text-forest/55">Jejak seluruh aktivitas pengguna di GAPURA.</p></div>
<form method="get" class="mb-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-6">
  <input name="q" value="{{ request('q') }}" placeholder="Cari user / deskripsi" class="inp lg:col-span-2">
  <select name="modul" class="inp"><option value="">Semua modul</option>@foreach($moduls as $m)<option @selected(request('modul')===$m)>{{ $m }}</option>@endforeach</select>
  <select name="aksi" class="inp"><option value="">Semua aksi</option>@foreach($aksis as $a)<option @selected(request('aksi')===$a)>{{ $a }}</option>@endforeach</select>
  <input type="date" name="dari" value="{{ request('dari') }}" class="inp" title="Dari tanggal"><input type="date" name="sampai" value="{{ request('sampai') }}" class="inp" title="Sampai tanggal">
  <button class="btn-ghost sm:col-span-2 lg:col-span-6 lg:w-fit"><x-icon n="search"/>Terapkan filter</button>
</form>
<div class="card overflow-x-auto !p-0"><table class="w-full">
  <thead><tr><th class="th">Waktu</th><th class="th">User</th><th class="th">Aksi</th><th class="th">Modul</th><th class="th">Deskripsi</th><th class="th">IP</th></tr></thead>
  @forelse($rows as $r)<tr class="hover:bg-green-50/40"><td class="td whitespace-nowrap text-xs">{{ \Illuminate\Support\Carbon::parse($r->created_at)->format('d/m/Y H:i:s') }}</td><td class="td font-semibold">{{ $r->user_nama ?? '—' }}</td>
   <td class="td"><span class="badge {{ $warna[$r->aksi] ?? 'bg-gray-100 text-gray-700' }}">{{ $r->aksi }}</span></td><td class="td">{{ $r->modul }}</td><td class="td text-forest/75">{{ $r->deskripsi }}</td><td class="td text-xs text-forest/45">{{ $r->ip }}</td></tr>
  @empty<tr><td colspan="6" class="td py-8 text-center text-forest/50">Belum ada aktivitas.</td></tr>@endforelse
</table></div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
