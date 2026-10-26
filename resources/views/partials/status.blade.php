@php $c=['diajukan'=>'bg-amber-100 text-amber-800','disetujui'=>'bg-leaf-100 text-forest','revisi'=>'bg-orange-100 text-orange-800','ditolak'=>'bg-red-100 text-red-800'][$s->status]??''; @endphp
<span class="badge {{ $c }}">{{ $s->status_label }}</span>
