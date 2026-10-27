<?php
namespace App\Http\Controllers;
use App\Models\Sppd;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $u = auth()->user(); $semua = $u->isUmum();
        $q = fn() => Sppd::query()->when(!$semua, fn($q) => $q->where('user_id', $u->id));
        $stat = [
            'total' => $q()->count(), 'disetujui' => $q()->where('status', 'disetujui')->count(),
            'diajukan' => $q()->where('status', 'diajukan')->count(), 'revisi' => $q()->whereIn('status', ['revisi', 'ditolak'])->count(),
        ];
        // Tautan kartu statistik: ke daftar SPPD terfilter; bila hanya 1 surat, langsung ke surat tersebut.
        $tautan = [];
        foreach (['total' => null, 'disetujui' => ['disetujui'], 'diajukan' => ['diajukan'], 'revisi' => ['revisi', 'ditolak']] as $k => $st) {
            $tautan[$k] = $stat[$k] === 1
                ? route('sppd.show', $q()->when($st, fn($x) => $x->whereIn('status', $st))->value('id'))
                : route('sppd.index', $st ? ['status' => implode(',', $st)] : []);
        }
        $bulan = collect(range(5, 0))->map(fn($i) => now()->subMonths($i)->startOfMonth());
        $seri = ['label' => $bulan->map(fn($m) => $m->translatedFormat('M Y'))->all(),
                 'data' => $bulan->map(fn($m) => $q()->whereBetween('created_at', [$m, $m->copy()->endOfMonth()])->count())->all()];
        $komposisi = $q()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        return view('home', [
            'stat' => $stat, 'tautan' => $tautan, 'menunggu' => $u->isPenyetuju() ? Sppd::where('status', 'diajukan')->count() : 0, 'seri' => $seri, 'komposisi' => $komposisi, 'terbaru' => $q()->latest()->limit(5)->get(),
            'perlu' => $semua ? collect() : Sppd::where('user_id', $u->id)->whereIn('status', ['revisi', 'ditolak'])->latest()->get(),
            'aktivitas' => $u->izin('log.lihat') ? DB::table('activity_logs')->orderByDesc('id')->limit(6)->get() : collect(),
        ]);
    }
}
