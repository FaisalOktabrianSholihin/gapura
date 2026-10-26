@extends('layouts.app')
@section('title', ($row?'Ubah':'Tambah').' Data')
@section('content')
<a href="{{ route($cfg['back']) }}" class="text-sm font-semibold text-leaf hover:underline">← Kembali</a>
<h1 class="mb-1 mt-1 text-2xl font-extrabold">{{ $row ? 'Ubah data' : 'Tambah data' }}</h1>
<p class="mb-5 text-sm text-forest/55">{{ $cfg['title'] }}</p>
<form method="post" action="{{ $row ? route('log.update',[$key,$row->id]) : route('log.store',$key) }}" class="card grid max-w-3xl gap-4 sm:grid-cols-2">@csrf @if($row) @method('PUT') @endif
 @foreach($cfg['fields'] as $f)
  @php [$col,$label,$type]=$f; $req=in_array('req',$f);
   $val=old($col, $row->$col ?? ($type==='date'?now()->toDateString():($type==='time'?now()->format('H:i'):null)));
   if($row && $type==='time') $val=old($col, substr((string)$row->$col,0,5)); @endphp
  <div class="{{ $col==='keterangan'?'sm:col-span-2':'' }}"><label class="lbl">{{ $label }}{!! $req?' <span class="text-red-500">*</span>':'' !!}</label>
  @if(str_starts_with($type,'select:'))
   <select name="{{ $col }}" class="inp" @required($req)><option value="">— pilih —</option>@foreach(config('gapura.'.substr($type,7)) as $o)<option @selected($val===$o)>{{ $o }}</option>@endforeach</select>
  @else<input name="{{ $col }}" type="{{ $type }}" value="{{ $val }}" @if($type==='number') step="any" @endif @required($req) class="inp">@endif</div>
 @endforeach
 <div class="flex gap-3 sm:col-span-2"><button class="btn">Simpan</button><a href="{{ route($cfg['back']) }}" class="btn-ghost">Batal</a></div>
</form>
@endsection
