@extends('layouts.app')
@section('title','Limbah Non-B3')
@section('content')
<h1 class="text-2xl font-extrabold">Logbook Limbah Non-B3</h1>
<p class="mb-4 text-sm text-forest/60">Limbah organik, domestik, dan non-berbahaya.</p>
<div class="mb-6 grid gap-3 sm:grid-cols-2"><div class="card"><div class="text-xs text-forest/60">Total limbah organik</div><div class="font-display text-3xl font-bold text-leaf">{{ number_format($totOrg,2,',','.') }} <span class="text-base">kg</span></div></div>
<div class="card"><div class="text-xs text-forest/60">Total limbah domestik</div><div class="font-display text-3xl font-bold text-forest">{{ number_format($totDom,2,',','.') }} <span class="text-base">m³</span></div></div></div>
@include('partials.logbar',['key'=>'organik']) @include('partials.logtable',['key'=>'organik','rows'=>$organik])
<div class="mt-8"></div>@include('partials.logbar',['key'=>'domestik']) @include('partials.logtable',['key'=>'domestik','rows'=>$domestik])
@endsection
