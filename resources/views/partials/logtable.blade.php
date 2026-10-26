@php $cfg=config("gapura.logs.$key"); $cols=$cfg['fields']; $ph=config('gapura.ipal'); @endphp
<div class="card overflow-x-auto !p-0"><table class="w-full">
  <thead><tr><th class="th">No</th>@foreach($cols as $f)<th class="th whitespace-nowrap">{{ $f[1] }}</th>@endforeach @if($key==='ipal')<th class="th">Status</th>@endif<th class="th"></th></tr></thead>
  @forelse($rows as $i=>$r)
  <tr class="hover:bg-leaf-100/40"><td class="td">{{ $i+1 }}</td>
    @foreach($cols as $f)@php $v=$r->{$f[0]}; @endphp
    <td class="td whitespace-nowrap {{ $f[2]==='number'?'text-right':'' }}">@if($f[2]==='date'){{ \Illuminate\Support\Carbon::parse($v)->format('d/m/Y') }}@elseif($f[2]==='time'){{ substr((string)$v,0,5) }}@elseif($f[2]==='number'){{ $v===null?'':rtrim(rtrim(number_format($v,2,',','.'),'0'),',') }}@else{{ $v }}@endif</td>@endforeach
    @if($key==='ipal')@php $bad=$r->ph_outlet!==null && ($r->ph_outlet<$ph['ph_min']||$r->ph_outlet>$ph['ph_max']); @endphp
      <td class="td"><span class="badge {{ $bad?'bg-red-100 text-red-700':'bg-leaf-100 text-forest' }}">{{ $bad?'Di luar baku mutu':'Normal' }}</span></td>@endif
    <td class="td whitespace-nowrap text-right">@if(auth()->user()->izin('lingkungan.kelola'))<a href="{{ route('log.edit',[$key,$r->id]) }}" class="mr-3 font-semibold text-leaf hover:underline">Ubah</a><form method="post" action="{{ route('log.destroy',[$key,$r->id]) }}" onsubmit="return confirm('Hapus data ini?')" class="inline">@csrf @method('DELETE')<button class="font-semibold text-red-600 hover:underline">Hapus</button></form>@endif</td></tr>
  @empty<tr><td colspan="{{ count($cols)+3 }}" class="td py-6 text-center text-forest/50">Belum ada data.</td></tr>@endforelse
</table></div>
