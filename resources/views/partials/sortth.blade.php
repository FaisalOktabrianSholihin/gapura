{{-- Header kolom yang bisa diurutkan asc/desc. Param: $col (kunci sort), $label --}}
@php
  $aktif = request('sort') === $col; $arah = $aktif ? (request('dir') === 'asc' ? 'asc' : 'desc') : null;
  $berikut = ($aktif && $arah === 'asc') ? 'desc' : 'asc';
  $url = request()->fullUrlWithQuery(['sort' => $col, 'dir' => $berikut, 'page' => null]);
@endphp
<th class="th whitespace-nowrap" aria-sort="{{ $aktif ? ($arah === 'asc' ? 'ascending' : 'descending') : 'none' }}">
  <a href="{{ $url }}" class="group inline-flex items-center gap-1.5 transition hover:text-forest" title="Urutkan {{ $label }} {{ $berikut === 'asc' ? 'A → Z / terlama' : 'Z → A / terbaru' }}">{{ $label }}
    <svg viewBox="0 0 10 14" class="h-3.5 w-2.5" aria-hidden="true"><path d="M5 1 9 6H1z" fill="{{ $arah === 'asc' ? '#15803d' : '#b6c2bb' }}"/><path d="M5 13 1 8h8z" fill="{{ $arah === 'desc' ? '#15803d' : '#b6c2bb' }}"/></svg></a>
</th>
