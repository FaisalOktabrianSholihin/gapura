@extends('layouts.app')
@section('title', $role->exists ? 'Atur Role' : 'Tambah Role')
@section('content')
@php $semua = $role->slug==='super_admin'; $pilih = old('izin', $role->permissions ?? []); $grup=['sppd'=>'SPPD','lingkungan'=>'Monitoring Lingkungan','user'=>'User','role'=>'Roles','log'=>'Log Aktivitas']; @endphp
<a href="{{ route('roles.index') }}" class="text-sm font-semibold text-leaf hover:underline">← Daftar role</a>
<h1 class="mb-5 mt-1 text-2xl font-extrabold">{{ $role->exists ? 'Atur Role: '.$role->nama : 'Tambah Role' }}</h1>
<form method="post" action="{{ $role->exists ? route('roles.update',$role) : route('roles.store') }}" class="max-w-3xl space-y-5">@csrf @if($role->exists) @method('PUT') @endif
  <div class="card grid gap-4 sm:grid-cols-2"><div><label class="lbl">Nama role *</label><input name="nama" value="{{ old('nama',$role->nama) }}" required class="inp"></div>
    <div><label class="lbl">Deskripsi</label><input name="deskripsi" value="{{ old('deskripsi',$role->deskripsi) }}" class="inp"></div></div>
  @if($semua)<div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-900">Super Admin selalu memiliki semua izin dan tidak dapat dibatasi.</div>
  @else
  <div class="card" x-data="{all(v){document.querySelectorAll('.izin-cb').forEach(c=>c.checked=v)}}">
    <div class="mb-3 flex items-center justify-between"><div class="font-bold">Izin akses</div><div class="flex gap-3 text-xs font-semibold text-leaf"><button type="button" @click="all(true)">Pilih semua</button><button type="button" @click="all(false)">Kosongkan</button></div></div>
    @foreach($grup as $k=>$nama)@php $items=collect(config('gapura.izin'))->filter(fn($l,$key)=>str_starts_with($key,$k.'.')); @endphp
    <div class="border-t border-gray-100 py-3 first:border-0"><div class="mb-2 text-xs font-bold uppercase tracking-wide text-forest/50">{{ $nama }}</div>
     <div class="grid gap-2 sm:grid-cols-2">@foreach($items as $key=>$label)<label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-gray-200 px-3 py-2 text-sm transition hover:border-leaf/50 has-[:checked]:border-leaf has-[:checked]:bg-green-50"><input type="checkbox" class="izin-cb h-4 w-4 rounded border-gray-300 text-leaf focus:ring-leaf" name="izin[]" value="{{ $key }}" @checked(in_array($key,$pilih))>{{ $label }}</label>@endforeach</div></div>
    @endforeach</div>
  @endif
  <div class="flex gap-3"><button class="btn">Simpan</button><a href="{{ route('roles.index') }}" class="btn-ghost">Batal</a></div>
</form>
@endsection
