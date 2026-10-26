@extends('layouts.app')
@section('title','User')
@section('content')
<div class="mb-5 flex flex-wrap items-end justify-between gap-3">
  <div><h1 class="text-2xl font-extrabold">User</h1><p class="text-sm text-forest/55">Akun login GAPURA. Username = NIK karyawan atau email.</p></div>
  <div class="flex flex-wrap gap-2">
    <form method="post" action="{{ route('users.sinkron') }}" onsubmit="return confirm('Buat akun untuk semua karyawan yang belum punya akun? Password awal = NIK.')">@csrf<button class="btn-ghost"><x-icon n="users"/>Sinkron dari Karyawan</button></form>
    <a href="{{ route('users.create') }}" class="btn"><x-icon n="plus"/>Tambah User</a>
  </div>
</div>
<form method="get" class="mb-4 flex flex-wrap gap-2">
  <input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIK / email" class="inp max-w-xs">
  <select name="role_id" class="inp max-w-[12rem]" onchange="this.form.submit()"><option value="">Semua role</option>@foreach($roles as $r)<option value="{{ $r->id }}" @selected(request('role_id')==$r->id)>{{ $r->nama }}</option>@endforeach</select>
  <button class="btn-ghost"><x-icon n="search"/>Cari</button>
</form>
<div class="card overflow-x-auto !p-0"><table class="w-full">
  <thead><tr><th class="th">Nama</th><th class="th">Username</th><th class="th">Role</th><th class="th">Terhubung karyawan</th><th class="th"></th></tr></thead>
  @forelse($users as $u)
  <tr class="transition hover:bg-green-50/40"><td class="td"><div class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-xs font-extrabold text-green-800">{{ strtoupper(substr($u->name,0,1)) }}</span><b>{{ $u->name }}</b></div></td>
   <td class="td">{{ $u->nik }}</td><td class="td"><span class="badge bg-green-100 text-green-800">{{ $u->peran?->nama ?? '—' }}</span></td>
   <td class="td">{!! $u->karyawan_id ? '<span class="text-green-700">Ya</span>' : '<span class="text-forest/40">Tidak</span>' !!}</td>
   <td class="td whitespace-nowrap text-right"><a href="{{ route('users.edit',$u) }}" class="mr-3 font-semibold text-leaf hover:underline">Ubah</a>
    @if($u->id!==auth()->id())<form method="post" action="{{ route('users.destroy',$u) }}" class="inline" onsubmit="return confirm('Hapus user {{ $u->name }}?')">@csrf @method('DELETE')<button class="font-semibold text-red-600 hover:underline">Hapus</button></form>@endif</td></tr>
  @empty<tr><td colspan="5" class="td py-8 text-center text-forest/50">Belum ada user.</td></tr>@endforelse
</table></div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
