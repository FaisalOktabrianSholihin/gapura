<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'GAPURA') · GAPURA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif'],display:['"Plus Jakarta Sans"','sans-serif']},
colors:{forest:{DEFAULT:'#0f2a1d',600:'#15803d'},leaf:{DEFAULT:'#16a34a',100:'#dcfce7'},lime:'#a3e635',paper:'#f5f8f6'}}}}</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<style type="text/tailwindcss">
 @layer components{
  .btn{@apply inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-[#22a35a] to-[#15803d] px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-green-900/20 transition hover:brightness-110 active:scale-[.97] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-leaf}
  .btn-ghost{@apply inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-forest transition hover:border-leaf/50 hover:bg-leaf-100/50 active:scale-[.97]}
  .btn-danger{@apply inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700 active:scale-[.97]}
  .card{@apply rounded-2xl border border-gray-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,.04)]}
  .inp{@apply w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-leaf focus:ring-4 focus:ring-leaf/15}
  .lbl{@apply mb-1 block text-xs font-semibold text-forest/70}
  .th{@apply bg-green-50/80 px-3 py-2.5 text-left text-[11px] font-bold uppercase tracking-wide text-forest/65}
  .td{@apply border-t border-gray-100 px-3 py-2.5 text-sm}
  .badge{@apply inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold}
  .stat{@apply relative overflow-hidden rounded-2xl border border-gray-200/80 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md}
  .stat:after{content:'';position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--c)}
 }
</style>
<style>
 body{background:#f5f8f6}
 @keyframes rise{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}} .rise{animation:rise .45s both}
 [x-cloak]{display:none!important}
 @media (prefers-reduced-motion:reduce){.rise{animation:none}}
</style>
</head>
<body class="font-sans text-forest antialiased" x-data="{nav:false}">
@php
 $u=auth()->user(); $on=fn(...$p)=>request()->routeIs(...$p);
 $ling=$on('lingkungan','ipal','cemaran.*','b3*','nonb3','log.*','import.*');
 $grup=$on('home')?'Dashboard':($on('sppd.*')?'Surat Perjalanan Dinas':($on('users.*','roles.*','activity')?'Administrasi':'Monitoring Lingkungan'));
@endphp
<div x-show="nav" x-cloak @click="nav=false" class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>
<aside class="fixed inset-y-0 left-0 z-40 flex w-60 -translate-x-full flex-col p-3 transition lg:translate-x-0" :class="nav&&'!translate-x-0'" style="background:linear-gradient(180deg,#0c6b36 0%,#074d27 100%)">
  <a href="{{ route('home') }}" class="mb-4 flex items-center gap-3 px-2 py-2">
    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white ring-1 ring-white/25"><x-icon n="gate" c="h-6 w-6"/></span>
    <span class="leading-tight"><span class="block text-base font-extrabold tracking-wide text-white">GAPURA</span><span class="block text-[10px] text-white/60">Pelayanan Umum Terpadu</span></span>
    <span class="ml-auto rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-bold text-lime">v1.0</span>
  </a>
  <nav class="flex-1 space-y-1 overflow-y-auto pb-2">
    <x-nav :href="route('home')" n="dashboard" :on="$on('home')">Dashboard</x-nav>

    <div class="px-3 pb-1 pt-5 text-[10px] font-bold tracking-[.14em] text-white/45">SURAT DINAS</div>
    <x-nav :href="route('sppd.index')" n="plane" :on="$on('sppd.*')">SPPD</x-nav>

    @if($u->izin('lingkungan.lihat'))
    <div class="px-3 pb-1 pt-5 text-[10px] font-bold tracking-[.14em] text-white/45">LINGKUNGAN</div>
    <div x-data="{o:{{ $ling?'true':'false' }}}">
      <button @click="o=!o" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-[13px] font-medium text-white/75 transition hover:bg-white/10 hover:text-white"><x-icon n="leaf" c="h-4 w-4"/>Monitoring Lingkungan<x-icon n="chevron" c="ml-auto h-4 w-4 transition" /></button>
      <div x-show="o" x-cloak class="mt-1 space-y-0.5">
        <x-nav :href="route('lingkungan')" n="dashboard" sub :on="$on('lingkungan')">Dashboard</x-nav>
        <x-nav :href="route('ipal')" n="droplets" sub :on="$on('ipal')||request()->is('lingkungan/log/ipal*','lingkungan/import/ipal*')">Monitoring IPAL</x-nav>
        <x-nav :href="route('cemaran.index')" n="clipboard" sub :on="$on('cemaran.*')||request()->is('lingkungan/import/cemaran*')">Checklist Cemaran B3</x-nav>
        <x-nav :href="route('b3')" n="archive" sub :on="$on('b3*')||request()->is('lingkungan/log/b3*','lingkungan/import/b3*')">Logbook Limbah B3</x-nav>
        <x-nav :href="route('nonb3')" n="leaf" sub :on="$on('nonb3')||request()->is('lingkungan/log/organik*','lingkungan/log/domestik*','lingkungan/import/organik*','lingkungan/import/domestik*')">Limbah Non-B3</x-nav>
      </div>
    </div>
    @endif

    @if($u->izin('user.kelola')||$u->izin('role.kelola')||$u->izin('log.lihat'))
    <div class="px-3 pb-1 pt-5 text-[10px] font-bold tracking-[.14em] text-white/45">ADMINISTRASI</div>
    @if($u->izin('user.kelola'))<x-nav :href="route('users.index')" n="user" :on="$on('users.*')">User</x-nav>@endif
    @if($u->izin('role.kelola'))<x-nav :href="route('roles.index')" n="shield" :on="$on('roles.*')">Roles</x-nav>@endif
    @if($u->izin('log.lihat'))<x-nav :href="route('activity')" n="history" :on="$on('activity')">Log Aktivitas</x-nav>@endif
    @endif
  </nav>
  <div class="flex items-center gap-3 rounded-xl bg-white/10 p-3 ring-1 ring-white/10">
    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-lime to-leaf text-sm font-extrabold text-forest">{{ strtoupper(substr($u->name,0,1)) }}</span>
    <span class="min-w-0 leading-tight"><span class="block truncate text-[13px] font-semibold text-white">{{ $u->name }}</span><span class="block truncate text-[11px] text-white/60">{{ $u->peran?->nama ?? 'Tanpa role' }}</span></span>
  </div>
</aside>

<div class="lg:pl-60">
  <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-gray-200/80 bg-white/90 px-4 py-2.5 backdrop-blur sm:px-8">
    <button @click="nav=true" class="rounded-lg border border-gray-200 p-2 lg:hidden" aria-label="Buka menu"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
    <div class="leading-tight"><div class="text-[15px] font-bold">{{ $grup }}</div><div class="hidden text-xs text-forest/55 sm:block">{{ config('gapura.perusahaan.nama') }} · Bagian Umum</div></div>
    <div class="relative ml-auto" x-data="{o:false}" @click.outside="o=false">
      <button @click="o=!o" class="flex items-center gap-2.5 rounded-xl border border-gray-200 bg-white py-1.5 pl-1.5 pr-3 transition hover:border-leaf/50">
        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-lime to-leaf text-xs font-extrabold text-forest">{{ strtoupper(substr($u->name,0,1)) }}</span>
        <span class="hidden text-left leading-tight sm:block"><span class="block text-[13px] font-semibold">{{ \Illuminate\Support\Str::limit($u->name,22) }}</span><span class="block text-[11px] text-forest/55">{{ $u->peran?->nama }}</span></span>
        <x-icon n="chevron" c="h-4 w-4 text-forest/50"/>
      </button>
      <div x-show="o" x-cloak x-transition.origin.top.right class="absolute right-0 mt-2 w-64 rounded-2xl border border-gray-200 bg-white p-3 shadow-xl">
        <div class="px-2 pb-2"><div class="text-sm font-bold">{{ $u->name }}</div><div class="truncate text-xs text-forest/55">{{ $u->nik }}</div><span class="badge mt-2 bg-leaf-100 text-forest">{{ $u->peran?->nama ?? 'Tanpa role' }}</span></div>
        <form method="post" action="{{ route('logout') }}" class="border-t border-gray-100 pt-2">@csrf<button class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-sm font-semibold text-red-600 hover:bg-red-50"><x-icon n="logout"/>Keluar</button></form>
      </div>
    </div>
  </header>
  <main class="mx-auto max-w-6xl rise p-4 sm:p-8">
    @if(session('ok'))<div x-data="{o:true}" x-show="o" x-transition class="mb-5 flex items-center justify-between rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-900"><span class="flex items-center gap-2"><x-icon n="check"/>{{ session('ok') }}</span><button @click="o=false" aria-label="Tutup">✕</button></div>@endif
    @if($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    @yield('content')
  </main>
</div>
</body></html>
