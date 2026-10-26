@php $cfg=config("gapura.logs.$key"); @endphp
<div x-data="{o:false}" class="mb-3">
  <button type="button" @click="o=!o" class="btn-ghost" x-text="o?'Tutup form':'+ Tambah data'"></button>
  <form x-show="o" x-cloak x-transition.origin.top method="post" action="{{ route('log.store',$key) }}" class="card mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@csrf
    @foreach($cfg['fields'] as $f)
      @php [$col,$label,$type]=$f; $req=in_array('req',$f); @endphp
      <div><label class="lbl">{{ $label }}{!! $req?' <span class="text-red-500">*</span>':'' !!}</label>
      @if(str_starts_with($type,'select:'))
        <select name="{{ $col }}" class="inp" @required($req)><option value="">— pilih —</option>@foreach(config('gapura.'.substr($type,7)) as $o)<option>{{ $o }}</option>@endforeach</select>
      @else
        <input name="{{ $col }}" type="{{ $type }}" @if($type==='number') step="any" @endif @if($type==='date') value="{{ now()->toDateString() }}" @endif @if($type==='time') value="{{ now()->format('H:i') }}" @endif @required($req) class="inp">
      @endif</div>
    @endforeach
    <div class="flex items-end"><button class="btn w-full">Simpan</button></div>
  </form>
</div>
