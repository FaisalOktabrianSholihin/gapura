@extends('layouts.app')
@section('title','Roles')
@section('content')
<div class="mb-5 flex flex-wrap items-end justify-between gap-3"><div><h1 class="text-2xl font-extrabold">Roles</h1><p class="text-sm text-forest/55">Atur apa saja yang boleh dilakukan tiap peran.</p></div>
  <a href="{{ route('roles.create') }}" class="btn"><x-icon n="plus"/>Tambah Role</a></div>
<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
@foreach($roles as $r)
  <div class="card flex flex-col"><div class="flex items-start justify-between gap-2"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-leaf"><x-icon n="shield" c="h-5 w-5"/></span>
    <div class="leading-tight"><div class="font-bold">{{ $r->nama }}</div><div class="text-xs text-forest/45">{{ $r->users_count }} user</div></div></div>
    @if($r->is_sistem)<span class="badge bg-gray-100 text-gray-600">Bawaan</span>@endif</div>
   <p class="mt-3 flex-1 text-sm text-forest/60">{{ $r->deskripsi }}</p>
   <div class="mt-3 text-xs text-forest/55">{{ in_array('*',$r->permissions)?'Semua izin':count($r->permissions).' izin' }}</div>
   <div class="mt-4 flex gap-3 border-t border-gray-100 pt-3 text-sm"><a href="{{ route('roles.edit',$r) }}" class="font-semibold text-leaf hover:underline">Atur izin</a>
    @if(!$r->is_sistem && !$r->users_count)<form method="post" action="{{ route('roles.destroy',$r) }}" onsubmit="return confirm('Hapus role ini?')">@csrf @method('DELETE')<button class="font-semibold text-red-600 hover:underline">Hapus</button></form>@endif</div></div>
@endforeach
</div>
@endsection
