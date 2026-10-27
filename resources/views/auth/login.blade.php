<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Masuk · GAPURA</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}"><link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{forest:'#0f2a1d',leaf:'#16a34a',lime:'#a3e635'}}}}</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<style>
 .grid-bg{background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:44px 44px}
 @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}} .float{animation:float 5s ease-in-out infinite}
 @keyframes pop{0%{transform:scale(.3) rotate(-18deg);opacity:0}70%{transform:scale(1.08) rotate(3deg)}100%{transform:scale(1) rotate(-8deg);opacity:1}} .pop{animation:pop .7s .6s cubic-bezier(.3,1.4,.5,1) both}
 @keyframes rise{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}} .rise{animation:rise .6s both}
 [x-cloak]{display:none!important}
 @media (prefers-reduced-motion:reduce){.float,.pop,.rise{animation:none}}
</style></head>
<body class="min-h-screen bg-white font-sans text-forest antialiased">
<div class="grid min-h-screen lg:grid-cols-2">
  {{-- kiri: form --}}
  <div class="flex items-center justify-center bg-gradient-to-br from-green-50/70 via-white to-white px-6 py-12">
    <form method="post" action="{{ route('login') }}" class="rise w-full max-w-[420px]" x-data="{show:false}">@csrf
      <span class="mb-5 inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-bold text-green-800"><x-icon n="gate" c="h-3.5 w-3.5"/>GAPURA Versi 1</span>
      <h1 class="text-4xl font-extrabold tracking-tight">Selamat datang <span class="inline-block origin-[70%_70%] animate-pulse">👋</span></h1>
      <p class="mb-7 mt-2 text-[15px] text-forest/60">Masuk untuk mengajukan SPPD dan mengelola monitoring lingkungan.</p>
      @error('nik')<div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div>@enderror
      <label class="mb-1.5 block text-sm font-semibold">Email / NIK</label>
      <div class="relative mb-5"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-forest/40"><x-icon n="mail" c="h-[18px] w-[18px]"/></span>
        <input name="nik" value="{{ old('nik') }}" autofocus required placeholder="nama@perusahaan.co.id atau NIK" class="w-full rounded-xl border border-gray-200 bg-white py-3 pl-11 pr-3 text-[15px] outline-none transition focus:border-leaf focus:ring-4 focus:ring-leaf/15"></div>
      <label class="mb-1.5 block text-sm font-semibold">Password</label>
      <div class="relative mb-5"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-forest/40"><x-icon n="lock" c="h-[18px] w-[18px]"/></span>
        <input :type="show?'text':'password'" name="password" required placeholder="••••••••" class="w-full rounded-xl border border-gray-200 bg-white py-3 pl-11 pr-16 text-[15px] outline-none transition focus:border-leaf focus:ring-4 focus:ring-leaf/15">
        <button type="button" @click="show=!show" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-sm text-forest/50 hover:text-forest" x-text="show?'Sembunyi':'Lihat'">Lihat</button></div>
      <label class="mb-6 flex items-center gap-2.5 text-sm text-forest/70"><input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-leaf focus:ring-leaf"> Ingat saya di perangkat ini</label>
      <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-[#22a35a] to-[#15803d] py-3.5 text-[15px] font-bold text-white shadow-lg shadow-green-900/20 transition hover:brightness-110 active:scale-[.98]">Masuk ke GAPURA <x-icon n="arrow" c="h-4 w-4"/></button>
      <p class="mt-8 text-center text-xs text-forest/45">GAPURA v1.0 · © {{ date('Y') }} · Hak akses diatur oleh administrator</p>
    </form>
  </div>

  {{-- kanan: panel brand --}}
  <div class="grid-bg relative hidden overflow-hidden text-white lg:flex lg:flex-col lg:justify-between" style="background:linear-gradient(160deg,#0f7a3d 0%,#0a5c2e 55%,#064a25 100%)">
    <div class="flex items-center gap-3 p-10">
      <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/25"><x-icon n="gate" c="h-7 w-7"/></span>
      <div class="leading-tight"><div class="text-2xl font-extrabold">GAPURA</div><div class="text-xs text-white/70">Gerbang Administrasi &amp; Pelayanan Umum Terpadu</div></div>
      <span class="ml-2 inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/20"><i class="h-1.5 w-1.5 rounded-full bg-lime"></i>Versi 1.0</span>
    </div>

    <div class="relative mx-auto h-[400px] w-[440px]">
      <div class="absolute left-14 top-6 h-[340px] w-[290px] rotate-6 rounded-3xl bg-white/20"></div>
      <div class="absolute left-10 top-0 h-[340px] w-[290px] rounded-3xl bg-white p-6 text-forest shadow-2xl">
        <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-leaf"><x-icon n="plane" c="h-5 w-5"/></span><div><div class="h-2.5 w-28 rounded bg-leaf"></div><div class="mt-1.5 h-2 w-16 rounded bg-green-200"></div></div></div>
        <div class="mt-6 space-y-2.5"><div class="h-2 w-full rounded bg-gray-200"></div><div class="h-2 w-[92%] rounded bg-gray-200"></div><div class="h-2 w-[80%] rounded bg-gray-200"></div><div class="h-2 w-24 rounded bg-green-300"></div></div>
        <div class="mt-5 space-y-2.5"><div class="h-2 w-full rounded bg-gray-200"></div><div class="h-2 w-[70%] rounded bg-gray-200"></div></div>
        <div class="absolute bottom-6 left-6 text-leaf"><x-icon n="qr" c="h-16 w-16"/></div>
        <div class="pop absolute bottom-4 right-4 flex h-[104px] w-[104px] flex-col items-center justify-center rounded-full border-[3px] border-leaf text-center text-leaf">
          <span class="absolute inset-1.5 rounded-full border border-dashed border-leaf/60"></span>
          <x-icon n="check" c="h-5 w-5"/><span class="mt-0.5 text-[10px] font-extrabold leading-tight tracking-wider">TELAH<br>DIVALIDASI</span><span class="text-[8px] font-semibold text-leaf/70">GAPURA v1</span></div>
      </div>
      @foreach([['SPPD','plane','left-0 top-12',0],['Monitoring IPAL','droplets','right-0 top-24',1.2],['Limbah B3','archive','left-2 bottom-24',2.2],['QR Validasi','qr','right-4 bottom-8',3.1]] as [$l,$i,$pos,$d])
      <div class="float absolute {{ $pos }} flex items-center gap-2 rounded-xl bg-white/10 px-3.5 py-2 text-sm font-semibold ring-1 ring-white/25 backdrop-blur" style="animation-delay:{{ $d }}s"><x-icon :n="$i" c="h-4 w-4 text-lime"/>{{ $l }}</div>
      @endforeach
    </div>

    <div class="p-10">
      <h2 class="text-4xl font-extrabold leading-tight tracking-tight">Urusan umum,<br>rapi dalam satu gerbang.</h2>
      <p class="mt-3 max-w-md text-[15px] leading-relaxed text-white/75">Ajukan SPPD dengan validasi QR, lalu pantau IPAL, cemaran B3, dan limbah B3 maupun non-B3 — lengkap dengan impor data dan jejak aktivitas.</p>
    </div>
  </div>
</div>
</body></html>
