{{-- Daftar dinamis bergaya kartu (kartu header + baris "X ke-N" + tombol Hapus).
     Param: $k (kunci), $icon, $label, $sub, $single (mis. "Tujuan"), $field (label input), $ph (placeholder), $min (jumlah baris minimal, 0/1) --}}
<div class="card">
  <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-leaf"><x-icon :n="$icon" c="h-5 w-5"/></span>
      <div class="leading-tight"><div class="font-bold text-forest">{{ $label }}</div><div class="text-xs text-forest/55">{{ $sub }}</div></div>
    </div>
    <button type="button" @click="add('{{ $k }}')" class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-b from-[#22a35a] to-[#15803d] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:brightness-110 active:scale-95">+ Tambah {{ $single }}</button>
  </div>

  <div class="space-y-3">
    <template x-for="(row,i) in lists.{{ $k }}" :key="row.id">
      <div class="flex flex-wrap items-end gap-3 rounded-2xl border border-gray-200 bg-slate-50/80 p-3 sm:flex-nowrap sm:p-4">
        <span class="inline-flex h-10 shrink-0 items-center whitespace-nowrap rounded-xl border border-green-300 bg-green-50 px-3.5 text-xs font-bold text-green-800">{{ $single }} ke-<span x-text="i+1" class="ml-0.5"></span></span>
        <div class="min-w-0 flex-1 basis-full sm:basis-auto">
          <label class="lbl">{{ $field }}</label>
          <input name="{{ $k }}[]" x-model="row.v" placeholder="{{ $ph }}" autocomplete="off" class="inp !rounded-xl !py-2.5" @if($k==='rombongan') list="dl-karyawan" @endif>
        </div>
        <button type="button" @click="del('{{ $k }}',i)" class="h-10 shrink-0 rounded-lg px-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Hapus</button>
      </div>
    </template>
    <p x-show="!lists.{{ $k }}.length" x-cloak class="rounded-2xl border border-dashed border-gray-300 bg-slate-50/50 py-6 text-center text-sm text-forest/50">Belum ada {{ strtolower($single) }}. Klik “+ Tambah {{ $single }}” untuk menambahkan.</p>
  </div>
</div>
