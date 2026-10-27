<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Hanya role Penyetuju yang boleh ACC / Revisi / Tolak SPPD (Bagian Umum tidak).
class Penyetuju
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->isPenyetuju(), 403, 'Hanya role Penyetuju yang dapat memutuskan SPPD.');
        return $next($request);
    }
}
