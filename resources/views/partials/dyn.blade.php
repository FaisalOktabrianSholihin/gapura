{{-- daftar dinamis: $k (key), $label, $ph --}}
<div>
  <div class="mb-1 flex items-center justify-between"><span class="lbl !mb-0">{{ $label }}</span>
    <button type="button" @click="add('{{ $k }}')" class="text-xs font-semibold text-leaf hover:underline">+ Tambah</button></div>
  <div class="space-y-2">
    <template x-for="(v,i) in lists.{{ $k }}" :key="i">
      <div class="flex items-center gap-2">
        <span class="w-5 text-right text-xs text-forest/50" x-text="i+1+'.'"></span>
        <input name="{{ $k }}[]" x-model="lists.{{ $k }}[i]" placeholder="{{ $ph }}" class="inp" @if($k==='rombongan') list="dl-karyawan" @endif>
        <button type="button" @click="del('{{ $k }}',i)" class="rounded-lg px-2 py-1 text-red-600 hover:bg-red-50" aria-label="Hapus">✕</button>
      </div>
    </template>
  </div>
</div>
