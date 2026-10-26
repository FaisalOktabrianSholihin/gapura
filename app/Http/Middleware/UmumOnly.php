<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class UmumOnly
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->isUmum(), 403, 'Menu ini khusus Bagian Umum.');
        return $next($request);
    }
}
