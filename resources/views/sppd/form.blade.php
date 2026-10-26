@extends('layouts.app')
@section('title', $sppd->exists ? 'Revisi SPPD' : 'Ajukan SPPD')
@section('content')
@php
  $init = fn($t, $min) => old($t, $sppd->exists ? $sppd->daftar($t)->all() : ($min ? [''] : []));
  $lists = ['tujuan'=>$init('tujuan',1),'keperluan'=>$init('keperluan',1),'rombongan'=>$init('rombongan',0),'perusahaan'=>$init('perusahaan',1)];
  $nama = $sppd->exists ? $sppd->nama : ($k->nama ?? auth()->user()->name);
  $nik  = $sppd->exists ? $sppd->nik : ($k->nik ?? auth()->user()->nik);
  $jab  = $sppd->exists ? $sppd->jabatan : ($k?->jabatan?->nama_jabatan);
  $div  = $sppd->exists ? $sppd->divisi : ($k?->divisi?->nama_divisi);
@endphp
<h1 class="font-display mb-1 text-2xl font-bold text-forest">{{ $sppd->exists ? 'Revisi SPPD' : 'Ajukan Surat Perjalanan Dinas' }}</h1>
@if($sppd->catatan_admin && $sppd->exists)<div class="mb-4 rounded-xl border border-orange-200 bg-orange-50 p-3 text-sm"><b>Catatan Bagian Umum:</b> {{ $sppd->catatan_admin }}</div>@endif
<form method="post" action="{{ $sppd->exists ? route('sppd.update',$sppd) : route('sppd.store') }}" class="mt-4 space-y-5"
  x-data='{lists:@json($lists),add(k){this.lists[k].push("")},del(k,i){this.lists[k].splice(i,1); if(!this.lists[k].length && k!=="rombongan") this.lists[k].push("")}}'>
  @csrf @if($sppd->exists) @method('PUT') @endif
  <datalist id="dl-karyawan">@foreach(\App\Models\Karyawan::orderBy('nama')->pluck('nama') as $n)<option value="{{ $n }}">@endforeach</datalist>

  <div class="card grid gap-4 sm:grid-cols-2">
    <div><label class="lbl">Nama Karyawan</label><input class="inp bg-forest/5" value="{{ $nama }}" readonly></div>
    <div><label class="lbl">NIK</label><input class="inp bg-forest/5" value="{{ $nik }}" readonly></div>
    <div><label class="lbl">Jabatan</label><input class="inp bg-forest/5" value="{{ $jab }}" readonly></div>
    <div><label class="lbl">Divisi</label><input class="inp bg-forest/5" value="{{ $div }}" readonly></div>
    <div><label class="lbl">Nomor Surat Tugas</label><input name="nomor_surat_tugas" class="inp" value="{{ old('nomor_surat_tugas',$sppd->nomor_surat_tugas) }}"></div>
    <div><label class="lbl">Jenis Perjalanan</label><select name="jenis_perjalanan" class="inp">@foreach(config('gapura.jenis_perjalanan') as $j)<option @selected(old('jenis_perjalanan',$sppd->jenis_perjalanan)===$j)>{{ $j }}</option>@endforeach</select></div>
    <div><label class="lbl">Tanggal Berangkat</label><input type="date" name="tanggal_berangkat" required class="inp" value="{{ old('tanggal_berangkat',$sppd->tanggal_berangkat?->toDateString()) }}"></div>
    <div><label class="lbl">Tanggal Kembali</label><input type="date" name="tanggal_kembali" required class="inp" value="{{ old('tanggal_kembali',$sppd->tanggal_kembali?->toDateString()) }}"></div>
    <div class="sm:col-span-2"><label class="lbl">Transportasi</label><input name="transportasi" list="dl-trans" class="inp" value="{{ old('transportasi',$sppd->transportasi) }}"><datalist id="dl-trans">@foreach(config('gapura.transportasi') as $t)<option value="{{ $t }}">@endforeach</datalist></div>
  </div>

  <div class="card grid gap-6 sm:grid-cols-2">
    @include('partials.dyn',['k'=>'tujuan','label'=>'Tujuan Dinas (kota/lokasi)','ph'=>'mis. Surabaya'])
    @include('partials.dyn',['k'=>'keperluan','label'=>'Keperluan Dinas','ph'=>'mis. Audit supplier'])
    @include('partials.dyn',['k'=>'rombongan','label'=>'Rombongan (opsional)','ph'=>'Nama anggota rombongan'])
    @include('partials.dyn',['k'=>'perusahaan','label'=>'Perusahaan / Instansi yang Dituju','ph'=>'mis. PT Contoh Sejahtera'])
  </div>

  <div class="card">
    <h2 class="font-display mb-3 font-bold text-forest">Penggunaan Fasilitas Kendaraan Dinas</h2>
    <div class="divide-y divide-forest/10">
    @foreach(config('gapura.fasilitas') as $key=>$label)
      @php $val=old("fasilitas.$key", $sppd->fasilitas[$key] ?? 'tidak'); @endphp
      <div class="flex items-center justify-between gap-3 py-2 text-sm"><span>{{ $label }}</span>
        <span class="flex gap-4"><label class="flex items-center gap-1"><input type="radio" name="fasilitas[{{ $key }}]" value="ya" @checked($val==='ya')> Ya</label>
        <label class="flex items-center gap-1"><input type="radio" name="fasilitas[{{ $key }}]" value="tidak" @checked($val!=='ya')> Tidak</label></span></div>
    @endforeach</div>
  </div>
  <div class="flex gap-3"><button class="btn">{{ $sppd->exists ? 'Simpan & ajukan ulang' : 'Ajukan SPPD' }}</button><a href="{{ route('sppd.index') }}" class="btn-ghost">Batal</a></div>
</form>
@endsection
