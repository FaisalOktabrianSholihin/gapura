<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

// Pemakaian di route: ->middleware(Izin::class . ':sppd.setujui')  (boleh beberapa izin, salah satu cukup)
class Izin
{
    public function handle(Request $request, Closure $next, string ...$izin)
    {
        $u = $request->user();
        abort_unless($u && collect($izin)->contains(fn($p) => $u->izin($p)), 403, 'Anda tidak memiliki izin untuk halaman ini.');
        return $next($request);
    }
}
