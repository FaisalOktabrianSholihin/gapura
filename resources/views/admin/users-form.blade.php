@extends('layouts.app')
@section('title', $user->exists ? 'Ubah User' : 'Tambah User')
@section('content')
@php $self = $user->exists && $user->id===auth()->id(); @endphp
<a href="{{ route('users.index') }}" class="text-sm font-semibold text-leaf hover:underline">← Daftar user</a>
<h1 class="mb-5 mt-1 text-2xl font-extrabold">{{ $user->exists ? 'Ubah User' : 'Tambah User' }}</h1>
<form method="post" action="{{ $user->exists ? route('users.update',$user) : route('users.store') }}" class="card grid max-w-3xl gap-4 sm:grid-cols-2"
  x-data='{map:@json($karyawans),nik:@json(old("nik",$user->nik)),name:@json(old("name",$user->name)),sync(){ if(this.map[this.nik]) this.name=this.map[this.nik] }}'>@csrf @if($user->exists) @method('PUT') @endif
  <datalist id="dl-nik">@foreach($karyawans as $nik=>$nama)<option value="{{ $nik }}">{{ $nama }}</option>@endforeach</datalist>
  <div><label class="lbl">Username (NIK karyawan atau email) *</label><input name="nik" x-model="nik" @input="sync()" list="dl-nik" required class="inp"><p class="mt-1 text-xs text-forest/50">Jika cocok dengan NIK di data karyawan, akun otomatis terhubung ke jabatan &amp; divisi.</p></div>
  <div><label class="lbl">Nama *</label><input name="name" x-model="name" required class="inp"></div>
  <div><label class="lbl">Role *</label><select name="role_id" class="inp" @disabled($self)>@foreach($roles as $r)<option value="{{ $r->id }}" @selected(old('role_id',$user->role_id)==$r->id)>{{ $r->nama }}</option>@endforeach</select>
    @if($self)<p class="mt-1 text-xs text-forest/50">Role akun sendiri tidak bisa diubah.</p>@endif</div>
  <div><label class="lbl">Password {{ $user->exists ? '(kosongkan jika tidak diganti)' : '*' }}</label><input type="password" name="password" @required(!$user->exists) autocomplete="new-password" class="inp"></div>
  <div class="flex gap-3 sm:col-span-2"><button class="btn">Simpan</button><a href="{{ route('users.index') }}" class="btn-ghost">Batal</a></div>
</form>
@endsection
