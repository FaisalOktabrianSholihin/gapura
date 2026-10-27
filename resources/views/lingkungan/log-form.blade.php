@extends('layouts.app')
@section('title', ($row?'Ubah':'Tambah').' Data')
@section('content')
@php
  $u = auth()->user(); $ph = config('gapura.ipal');
  $fields = collect($cfg['fields'])->keyBy(0);
  $groups = $cfg['groups'] ?? [[$cfg['title'], $cfg['icon'] ?? 'file', array_column($cfg['fields'], 0)]];
  $val = function ($f) use ($row) {
    [$col, , $type] = $f;
    $v = old($col, $row->$col ?? ($type==='date' ? now()->toDateString() : ($type==='time' ? now()->format('H:i') : null)));
    if ($row && $type==='time') $v = old($col, substr((string) $row->$col, 0, 5));
    return $v;
  };
@endphp
<a href="{{ route($cfg['back']) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-leaf hover:underline">← Kembali</a>

<div class="mb-5 mt-2 flex items-center gap-4">
  <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-green-50 to-green-100 text-leaf ring-1 ring-green-200"><x-icon :n="$cfg['icon'] ?? 'file'" c="h-7 w-7"/></span>
  <div class="leading-tight"><h1 class="text-2xl font-extrabold">{{ $row ? 'Ubah Data' : 'Tambah Data' }}</h1><p class="mt-0.5 text-sm text-forest/55">{{ $cfg['title'] }} · {{ $cfg['sub'] ?? '' }}</p></div>
</div>

<div class="grid items-start gap-5 lg:grid-cols-3">
<form method="post" action="{{ $row ? route('log.update',[$key,$row->id]) : route('log.store',$key) }}" class="space-y-5 lg:col-span-2"
  @if($key==='ipal') x-data="{phOut:@js((string) $val($fields['ph_outlet'])),phIn:@js((string) $val($fields['ph_inlet'])),min:{{ $ph['ph_min'] }},max:{{ $ph['ph_max'] }},bad(v){return v!==''&&(parseFloat(v)<this.min||parseFloat(v)>this.max)}}" @endif>
  @csrf @if($row) @method('PUT') @endif

  @foreach($groups as $gi => [$gjudul, $gicon, $cols])
  <section class="card">
    <div class="mb-4 flex items-center gap-3">
      <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 text-leaf"><x-icon :n="$gicon" c="h-[18px] w-[18px]"/></span>
      <div class="text-sm font-bold">{{ $gjudul }}</div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
    @foreach($cols as $col)
      @php
        $f = $fields[$col]; [$c, $label, $type] = $f; $req = in_array('req', $f); $v = $val($f);
        $unit = null; if (preg_match('/\s*\(([^)]+)\)\s*$/', $label, $m)) { $unit = $m[1]; $label = trim(preg_replace('/\s*\([^)]+\)\s*$/', '', $label)); }
        $lebar = ($c === 'keterangan') ? 'sm:col-span-2' : '';
      @endphp
      <div class="{{ $lebar }}">
        <label class="lbl" for="f-{{ $c }}">{{ $label }}{!! $req ? ' <span class="text-red-500">*</span>' : '' !!}</label>
        @if(str_starts_with($type,'select:'))
          <select id="f-{{ $c }}" name="{{ $c }}" class="inp" @required($req)><option value="">— pilih —</option>@foreach(config('gapura.'.substr($type,7)) as $o)<option @selected($v===$o)>{{ $o }}</option>@endforeach</select>
        @else
          <div class="relative">
            <input id="f-{{ $c }}" name="{{ $c }}" type="{{ $type }}" value="{{ $v }}" @if($type==='number') step="any" inputmode="decimal" @endif @required($req)
              @if($key==='ipal' && $c==='ph_outlet') x-model="phOut" @endif @if($key==='ipal' && $c==='ph_inlet') x-model="phIn" @endif
              @if($c==='keterangan') placeholder="Catatan tambahan (opsional)" @endif
              class="inp {{ $unit ? 'pr-14' : '' }}" @if($key==='ipal' && in_array($c,['ph_outlet','ph_inlet'])) :class="bad({{ $c==='ph_outlet' ? 'phOut' : 'phIn' }})&&'!border-red-400 !ring-red-100'" @endif>
            @if($unit)<span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-forest/45">{{ $unit }}</span>@endif
          </div>
          @if($key==='ipal' && in_array($c,['ph_outlet','ph_inlet']))
            <p class="mt-1 text-xs" :class="bad({{ $c==='ph_outlet' ? 'phOut' : 'phIn' }})?'font-semibold text-red-600':'text-forest/45'" x-text="bad({{ $c==='ph_outlet' ? 'phOut' : 'phIn' }})?'Di luar baku mutu ({{ $ph['ph_min'] }}–{{ $ph['ph_max'] }})':'Baku mutu {{ $ph['ph_min'] }}–{{ $ph['ph_max'] }}'"></p>
          @endif
        @endif
      </div>
    @endforeach
    </div>
  </section>
  @endforeach

  <div class="sticky bottom-3 z-10 flex items-center justify-between gap-3 rounded-2xl border border-gray-200/80 bg-white/95 p-3 shadow-lg backdrop-blur">
    <span class="hidden text-xs text-forest/50 sm:block"><span class="text-red-500">*</span> wajib diisi</span>
    <div class="flex gap-3"><a href="{{ route($cfg['back']) }}" class="btn-ghost">Batal</a><button class="btn px-6">{{ $row ? 'Simpan Perubahan' : 'Simpan Data' }}</button></div>
  </div>
</form>

<aside class="space-y-4 lg:sticky lg:top-20">
  @if(!$row && ($u->izin('lingkungan.import')||$u->izin('lingkungan.kelola')))
  <div class="card !border-green-200 !bg-gradient-to-br !from-green-50 !to-white">
    <div class="mb-1 flex items-center gap-2 font-bold"><x-icon n="sheet" c="h-5 w-5 text-leaf"/>Punya banyak data?</div>
    <p class="text-sm text-forest/65">Tidak perlu mengetik satu per satu. Isi template Excel lalu unggah sekaligus.</p>
    <div class="mt-3 flex flex-wrap gap-2"><a href="{{ route('import.template',$key) }}" class="btn-ghost !py-1.5"><x-icon n="download"/>Template</a><a href="{{ route('import.form',$key) }}" class="btn !py-1.5"><x-icon n="upload"/>Import Excel</a></div>
  </div>
  @endif
  <div class="card text-sm text-forest/65"><div class="mb-1 font-bold text-forest">Tips pengisian</div>
    <ul class="list-inside list-disc space-y-1 text-xs leading-relaxed"><li>Tanggal dan jam sudah terisi saat ini, ubah bila perlu.</li><li>Angka desimal boleh memakai titik atau koma.</li>@if($key==='ipal')<li>Nilai pH di luar {{ $ph['ph_min'] }}–{{ $ph['ph_max'] }} akan ditandai merah di daftar.</li>@endif</ul></div>
</aside>
</div>
@endsection
