@extends('layouts.app')
@section('title', $sppd->exists ? 'Revisi SPPD' : 'Ajukan SPPD')
@section('content')
@php
  $init = fn($t, $min) => old($t, $sppd->exists ? $sppd->daftar($t)->all() : ($min ? [''] : []));
  $lists = ['tujuan'=>$init('tujuan',1),'keperluan'=>$init('keperluan',1),'rombongan'=>$init('rombongan',0),'perusahaan'=>$init('perusahaan',1)];
  $orang = \App\Models\Karyawan::with(['jabatan','divisi'])->orderBy('nama')->get()->map(fn($o)=>[
      'id'=>$o->id,'n'=>$o->nama,'nik'=>(string)$o->nik,'j'=>$o->jabatan?->nama_jabatan,'d'=>$o->divisi?->nama_divisi,
      'q'=>mb_strtolower($o->nama.' '.$o->nik.' '.$o->jabatan?->nama_jabatan.' '.$o->divisi?->nama_divisi)])->values();
  $nama = $sppd->exists ? $sppd->nama : ($k->nama ?? auth()->user()->name);
  $nik  = $sppd->exists ? $sppd->nik : ($k->nik ?? auth()->user()->nik);
  $jab  = $sppd->exists ? $sppd->jabatan : ($k?->jabatan?->nama_jabatan);
  $div  = $sppd->exists ? $sppd->divisi : ($k?->divisi?->nama_divisi);
@endphp
<h1 class="font-display mb-1 text-2xl font-bold text-forest">{{ $sppd->exists ? 'Revisi SPPD' : 'Ajukan Surat Perjalanan Dinas' }}</h1>
<p class="text-sm text-forest/55">Lengkapi data perjalanan dinas. Pengajuan akan diteruskan ke Penyetuju.</p>
@if($sppd->catatan_admin && $sppd->exists)<div class="mb-4 mt-3 rounded-xl border border-orange-200 bg-orange-50 p-3 text-sm"><b>Catatan Penyetuju:</b> {{ $sppd->catatan_admin }}</div>@endif
<form method="post" action="{{ $sppd->exists ? route('sppd.update',$sppd) : route('sppd.store') }}" class="mt-4 space-y-5" x-data="sppdForm(@js($lists), @js($orang))">
  @csrf @if($sppd->exists) @method('PUT') @endif

  {{-- data pemohon & perjalanan --}}
  <div class="card grid gap-4 sm:grid-cols-2">
    <div><label class="lbl">Nama Karyawan</label><input class="inp bg-forest/5" value="{{ $nama }}" readonly></div>
    <div><label class="lbl">NIK</label><input class="inp bg-forest/5" value="{{ $nik }}" readonly></div>
    <div><label class="lbl">Jabatan</label><input class="inp bg-forest/5" value="{{ $jab }}" readonly></div>
    <div><label class="lbl">Divisi</label><input class="inp bg-forest/5" value="{{ $div }}" readonly></div>
    <div><label class="lbl">Nomor Surat Tugas</label><input name="nomor_surat_tugas" class="inp" value="{{ old('nomor_surat_tugas',$sppd->nomor_surat_tugas) }}"></div>
    <div><label class="lbl">Jenis Perjalanan</label><select name="jenis_perjalanan" class="inp">@foreach(config('gapura.jenis_perjalanan') as $j)<option @selected(old('jenis_perjalanan',$sppd->jenis_perjalanan)===$j)>{{ $j }}</option>@endforeach</select></div>
    <div><label class="lbl">Tanggal Berangkat</label><input type="date" name="tanggal_berangkat" required class="inp" value="{{ old('tanggal_berangkat',$sppd->tanggal_berangkat?->toDateString()) }}"></div>
    <div><label class="lbl">Tanggal Kembali</label><input type="date" name="tanggal_kembali" required class="inp" value="{{ old('tanggal_kembali',$sppd->tanggal_kembali?->toDateString()) }}"></div>

    {{-- transportasi: dropdown yang bisa dicari --}}
    <div class="sm:col-span-2" x-data="combo(@js(config('gapura.transportasi')), @js(old('transportasi',$sppd->transportasi) ?? ''))" @click.outside="tutup()">
      <label class="lbl">Transportasi <span class="text-red-500">*</span></label>
      <input type="hidden" name="transportasi" :value="val">
      <div class="relative">
        <input type="text" x-model="q" @focus="open=true;$event.target.select()" @input="open=true;hi=0" @keydown.down.prevent="open=true;hi=Math.min(hi+1,list.length-1)" @keydown.up.prevent="hi=Math.max(hi-1,0)"
          @keydown.enter.prevent="if(open&&list[hi])pick(list[hi])" @keydown.escape="tutup()" placeholder="Ketik untuk mencari, atau pilih transportasi…" autocomplete="off" role="combobox" :aria-expanded="open" class="inp pr-10">
        <button type="button" tabindex="-1" @click="open=!open;if(open)$nextTick(()=>$el.previousElementSibling.focus())" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-forest/45 hover:text-forest" aria-label="Buka daftar"><x-icon n="chevron" c="h-4 w-4"/></button>
        <ul x-show="open" x-cloak x-transition.origin.top class="absolute z-30 mt-1.5 max-h-64 w-full overflow-auto rounded-xl border border-gray-200 bg-white p-1.5 shadow-xl" role="listbox">
          <template x-for="(o,idx) in list" :key="o">
            <li @mousedown.prevent="pick(o)" @mouseenter="hi=idx" role="option" class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-sm" :class="idx===hi?'bg-green-50 text-forest':'text-forest/80'">
              <span x-text="o"></span><span x-show="o===val" class="text-xs font-bold text-leaf">✓ dipilih</span></li>
          </template>
          <li x-show="!list.length" class="px-3 py-3 text-center text-sm text-forest/45">Tidak ditemukan.</li>
        </ul>
      </div>
    </div>
  </div>

  {{-- daftar dinamis (kartu) --}}
  @include('partials.dyn',['k'=>'tujuan','icon'=>'pin','label'=>'Tujuan Dinas','sub'=>'Isi kota / lokasi tujuan perjalanan dinas','single'=>'Tujuan','field'=>'Kota / Lokasi Tujuan','ph'=>'mis. Surabaya'])
  @include('partials.dyn',['k'=>'keperluan','icon'=>'clipboard','label'=>'Keperluan Dinas','sub'=>'Isi maksud dan keperluan perjalanan','single'=>'Keperluan','field'=>'Keperluan','ph'=>'mis. Audit supplier'])
  @include('partials.dyn',['k'=>'rombongan','icon'=>'users','label'=>'Rombongan','sub'=>'Opsional — isi nama anggota yang ikut perjalanan','single'=>'Anggota','field'=>'Nama Anggota','ph'=>'Ketik / pilih nama karyawan'])
  @include('partials.dyn',['k'=>'perusahaan','icon'=>'building','label'=>'Perusahaan / Instansi yang Dituju','sub'=>'Isi nama perusahaan atau instansi yang dikunjungi','single'=>'Instansi','field'=>'Nama Perusahaan / Instansi','ph'=>'mis. PT Contoh Sejahtera'])

  {{-- fasilitas kendaraan dinas --}}
  <div class="card" x-ref="fas">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-leaf"><x-icon n="car" c="h-5 w-5"/></span>
        <div class="leading-tight"><div class="font-bold text-forest">Penggunaan Fasilitas Kendaraan Dinas</div><div class="text-xs text-forest/55">Tandai fasilitas yang dibutuhkan dari instansi / perusahaan yang dikunjungi</div></div>
      </div>
      <div class="flex gap-2 text-xs font-bold">
        <button type="button" @click="$refs.fas.querySelectorAll('input[value=ya]').forEach(r=>r.checked=true)" class="rounded-lg border border-green-200 bg-green-50 px-3 py-1.5 text-green-800 transition hover:bg-green-100">Semua Ya</button>
        <button type="button" @click="$refs.fas.querySelectorAll('input[value=tidak]').forEach(r=>r.checked=true)" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-forest/70 transition hover:bg-gray-50">Semua Tidak</button>
      </div>
    </div>
    <div class="space-y-2.5">
    @foreach(config('gapura.fasilitas') as $key=>$label)
      @php $val=old("fasilitas.$key", $sppd->fasilitas[$key] ?? 'tidak'); @endphp
      <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-slate-50/80 px-4 py-3 transition hover:border-leaf/40">
        <div class="flex items-center gap-3 text-sm font-medium text-forest"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-leaf ring-1 ring-gray-200"><x-icon n="car" c="h-4 w-4"/></span>{{ $label }}</div>
        <div class="inline-flex rounded-xl bg-slate-200/70 p-1 text-xs font-bold">
          <label class="cursor-pointer"><input type="radio" class="peer sr-only" name="fasilitas[{{ $key }}]" value="ya" @checked($val==='ya')><span class="block rounded-lg px-5 py-1.5 text-forest/55 transition peer-checked:bg-leaf peer-checked:text-white peer-checked:shadow peer-focus-visible:ring-2 peer-focus-visible:ring-leaf">Ya</span></label>
          <label class="cursor-pointer"><input type="radio" class="peer sr-only" name="fasilitas[{{ $key }}]" value="tidak" @checked($val!=='ya')><span class="block rounded-lg px-4 py-1.5 text-forest/55 transition peer-checked:bg-white peer-checked:text-forest peer-checked:shadow peer-focus-visible:ring-2 peer-focus-visible:ring-leaf">Tidak</span></label>
        </div>
      </div>
    @endforeach
    </div>
  </div>

  <div class="flex gap-3"><button class="btn">{{ $sppd->exists ? 'Simpan & ajukan ulang' : 'Ajukan SPPD' }}</button><a href="{{ route('sppd.index') }}" class="btn-ghost">Batal</a></div>
</form>

<script>
// Daftar dinamis: tiap baris punya id unik (kunci x-for) supaya penambahan/penghapusan baris selalu tepat.
function sppdForm(raw, orang){
  let n=0; const mk=a=>(a||[]).map(v=>({id:++n,v:v??''})); const wajib=['tujuan','keperluan','perusahaan'];
  return {
    orang:orang,
    cari(q){ const s=(q||'').toLowerCase().trim(); return (s?this.orang.filter(o=>o.q.includes(s)):this.orang).slice(0,50); },
    lists:{tujuan:mk(raw.tujuan),keperluan:mk(raw.keperluan),rombongan:mk(raw.rombongan),perusahaan:mk(raw.perusahaan)},
    add(k){this.lists[k].push({id:++n,v:''})},
    del(k,i){this.lists[k].splice(i,1); if(!this.lists[k].length && wajib.includes(k)) this.lists[k].push({id:++n,v:''})}
  };
}
// Dropdown yang bisa dicari (hanya boleh memilih dari daftar)
function combo(opts,init){
  return {
    opts, val:opts.includes(init)?init:'', q:opts.includes(init)?init:(init||''), open:false, hi:0,
    get list(){ const s=this.q.trim().toLowerCase(); if(!s||this.q===this.val) return this.opts; return this.opts.filter(o=>o.toLowerCase().includes(s)); },
    pick(o){ this.val=o; this.q=o; this.open=false; },
    tutup(){ this.open=false; this.q=this.val; }
  };
}
</script>
@endsection
