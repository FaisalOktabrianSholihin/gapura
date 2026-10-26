@props(['href', 'n', 'on' => false, 'sub' => false])
<a href="{{ $href }}" @class(['group flex items-center gap-3 rounded-xl text-[13px] font-medium transition', 'px-3 py-2' => !$sub, 'py-1.5 pl-9 pr-3' => $sub,
  'bg-white/15 text-white ring-1 ring-white/20' => $on, 'text-white/75 hover:bg-white/10 hover:text-white' => !$on])>
  <x-icon :n="$n" c="h-4 w-4 opacity-90"/>{{ $slot }}
</a>
