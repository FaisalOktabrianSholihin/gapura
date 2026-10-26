<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index(Request $r)
    {
        $rows = DB::table('activity_logs')
            ->when($r->q, fn($q, $v) => $q->where(fn($w) => $w->where('user_nama', 'like', "%$v%")->orWhere('deskripsi', 'like', "%$v%")))
            ->when($r->modul, fn($q, $v) => $q->where('modul', $v))
            ->when($r->aksi, fn($q, $v) => $q->where('aksi', $v))
            ->when($r->dari, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($r->sampai, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('id')->paginate(25)->withQueryString();
        return view('admin.activity', ['rows' => $rows,
            'moduls' => DB::table('activity_logs')->distinct()->orderBy('modul')->pluck('modul'),
            'aksis' => DB::table('activity_logs')->distinct()->orderBy('aksi')->pluck('aksi')]);
    }
}
