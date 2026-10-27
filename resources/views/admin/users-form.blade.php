@extends('layouts.app')
@section('title', $user->exists ? 'Ubah User' : 'Tambah User')
@section('content')
@php $self = $user->exists && $user->id===auth()->id(); @endphp
<a href="{{ route('users.index') }}" class="text-sm font-semibold text-leaf hover:underline">← Daftar user</a>
<h1 class="mb-5 mt-1 text-2xl font-extrabold">{{ $user->exists ? 'Ubah User' : 'Tambah User' }}</h1>
<form method="post" action="{{ $user->exists ? route('users.update',$user) : route('users.store') }}" class="card grid max-w-3xl gap-4 sm:grid-cols-2"
  x-data="{map:@js($karyawans),nik:@js((string) old('nik',$user->nik)),name:@js((string) old('name',$user->name)),sync(){ if(this.map[this.nik]) this.name=this.map[this.nik] }}">@csrf @if($user->exists) @method('PUT') @endif
  <datalist id="dl-nik">@foreach($karyawans as $nik=>$nama)<option value="{{ $nik }}">{{ $nama }}</option>@endforeach</datalist>
  <div><label class="lbl">Username (NIK karyawan atau email) *</label><input name="nik" x-model="nik" @input="sync()" list="dl-nik" required class="inp"><p class="mt-1 text-xs text-forest/50">Jika cocok dengan NIK di data karyawan, akun otomatis terhubung ke jabatan &amp; divisi.</p></div>
  <div><label class="lbl">Nama *</label><input name="name" x-model="name" required class="inp"></div>
  <div><label class="lbl">Role *</label><select name="role_id" class="inp" @disabled($self)>@foreach($roles as $r)<option value="{{ $r->id }}" @selected(old('role_id',$user->role_id)==$r->id)>{{ $r->nama }}</option>@endforeach</select>
    @if($self)<p class="mt-1 text-xs text-forest/50">Role akun sendiri tidak bisa diubah.</p>@endif</div>
  <div><label class="lbl">Password {{ $user->exists ? '(kosongkan jika tidak diganti)' : '*' }}</label><input type="password" name="password" @required(!$user->exists) autocomplete="new-password" class="inp"></div>
  @if($user->exists && auth()->user()->isSuperAdmin())
  {{-- khusus Super Admin: lihat password saat ini (didekripsi dari salinan terenkripsi; tiap pembukaan tercatat di Log Aktivitas) --}}
  <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 sm:col-span-2" x-data="lihatPw('{{ route('users.password',$user) }}')">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-amber-600 ring-1 ring-amber-200"><x-icon n="key" c="h-5 w-5"/></span>
        <div class="leading-tight"><div class="text-sm font-bold text-amber-950">Password saat ini</div><div class="text-xs text-amber-900/70">Hanya terlihat oleh Super Admin. Setiap kali dibuka tercatat di Log Aktivitas.</div></div></div>
      <div class="flex flex-wrap items-center gap-2">
        <code x-show="pw!==null&&ada" x-cloak x-text="pw" class="rounded-lg bg-white px-3 py-2 font-mono text-sm font-bold tracking-wide text-forest ring-1 ring-amber-200"></code>
        <button type="button" x-show="pw!==null&&ada" x-cloak @click="salin()" class="btn-ghost !py-2" x-text="copied?'Tersalin ✓':'Salin'"></button>
        <button type="button" @click="buka()" :disabled="loading" class="btn-ghost !py-2" x-text="loading?'Memuat…':(pw!==null?'Sembunyikan':'Tampilkan password')"></button>
      </div>
    </div>
    <p x-show="pw!==null&&!ada" x-cloak class="mt-3 text-sm text-amber-900">Password lama user ini belum tercatat (dibuat sebelum fitur ini ada). Isi “Password” di bawah dan simpan agar password barunya tercatat dan bisa dilihat di sini.</p>
  </div>
  @endif
  <div class="flex gap-3 sm:col-span-2"><button class="btn">Simpan</button><a href="{{ route('users.index') }}" class="btn-ghost">Batal</a></div>
</form>
<script>
function lihatPw(url){return{pw:null,ada:true,loading:false,copied:false,
 async buka(){ if(this.pw!==null){this.pw=null;return}
  this.loading=true;
  try{const r=await fetch(url,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},credentials:'same-origin'});
   if(!r.ok) throw new Error(r.status); const d=await r.json(); this.ada=d.ada; this.pw=d.password??''}
  catch(e){alert('Gagal mengambil password. Coba muat ulang halaman.')}
  this.loading=false},
 async salin(){ try{await navigator.clipboard.writeText(this.pw);this.copied=true;setTimeout(()=>this.copied=false,1500)}catch(e){} }
}}
</script>
@endsection
