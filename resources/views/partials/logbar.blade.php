{{-- tombol aksi per tabel log: $key, $title (opsional) --}}
@php $u=auth()->user(); @endphp
<div class="mb-3 mt-2 flex flex-wrap items-center justify-between gap-2">
  <h2 class="text-base font-bold">{{ $title ?? config("gapura.logs.$key.title") }}</h2>
  <div class="flex flex-wrap gap-2">
    @if($u->izin('lingkungan.import')||$u->izin('lingkungan.kelola'))
      <a href="{{ route('import.template',$key) }}" class="btn-ghost !py-1.5" title="Unduh template Excel (.xlsx)"><x-icon n="sheet"/>Template</a>
      <a href="{{ route('import.form',$key) }}" class="btn-ghost !py-1.5"><x-icon n="upload"/>Import Excel</a>
    @endif
    @if($u->izin('lingkungan.kelola'))<a href="{{ route('log.create',$key) }}" class="btn !py-1.5"><x-icon n="plus"/>Tambah Data</a>@endif
  </div>
</div>
