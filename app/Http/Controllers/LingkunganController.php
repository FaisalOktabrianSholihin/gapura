<?php
namespace App\Http\Controllers;
use App\Support\Aktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class LingkunganController extends Controller
{
    private function persenBersih(int $id): array
    {
        $it = DB::table('env_cemaran_items')->where('cemaran_id', $id)->get();
        $b = $it->where('status', 'bersih')->count(); $t = $it->where('status', 'tercemar')->count();
        return ['bersih' => $b, 'tercemar' => $t, 'persen' => $it->count() ? round($b / $it->count() * 100, 2) : 0];
    }

    private function neraca(): array
    {
        $masuk = DB::table('env_b3_masuk')->selectRaw('jenis, SUM(jumlah) t')->groupBy('jenis')->pluck('t', 'jenis');
        $keluar = DB::table('env_b3_keluar')->selectRaw('jenis, SUM(jumlah) t')->groupBy('jenis')->pluck('t', 'jenis');
        return collect(config('gapura.jenis_b3'))->map(fn($j) => [
            'jenis' => $j, 'masuk' => (float) ($masuk[$j] ?? 0), 'keluar' => (float) ($keluar[$j] ?? 0),
            'sisa' => (float) ($masuk[$j] ?? 0) - (float) ($keluar[$j] ?? 0),
        ])->all();
    }

    public function dashboard()
    {
        $ipal = DB::table('env_ipal')->orderByDesc('tanggal')->orderByDesc('id')->first();
        $seri = DB::table('env_ipal')->orderByDesc('tanggal')->orderByDesc('id')->limit(14)->get()->reverse()->values();
        $tersimpan = collect($this->neraca())->sum('sisa');
        $cem = DB::table('env_cemaran')->orderByDesc('tanggal')->orderByDesc('id')->first();
        return view('lingkungan.dashboard', [
            'ipal' => $ipal, 'seri' => $seri, 'tersimpan' => $tersimpan, 'cem' => $cem,
            'cemStat' => $cem ? $this->persenBersih($cem->id) : null,
            'organik' => DB::table('env_organik')->sum('berat'), 'domestik' => DB::table('env_domestik')->sum('volume'),
            'periode' => now()->translatedFormat('F Y'),
        ]);
    }

    public function ipal()
    {
        $rows = DB::table('env_ipal')->orderByDesc('tanggal')->orderByDesc('id')->limit(60)->get();
        return view('lingkungan.ipal', ['rows' => $rows, 'seri' => $rows->reverse()->values()->take(-30)->values()]);
    }

    // ---- Checklist cemaran B3 ----
    public function cemaranIndex()
    {
        $rows = DB::table('env_cemaran')->orderByDesc('tanggal')->orderByDesc('id')->paginate(15);
        $rows->getCollection()->transform(fn($r) => (object) ((array) $r + $this->persenBersih($r->id)));
        return view('lingkungan.cemaran-index', compact('rows'));
    }

    public function cemaranCreate() { return view('lingkungan.cemaran-form'); }

    public function cemaranStore(Request $r)
    {
        $r->validate(['tanggal' => 'required|date', 'petugas' => 'required|string|max:100', 'shift' => 'nullable|string|max:30',
            'status.*' => 'in:bersih,tercemar', 'foto.*' => 'nullable|image|max:4096']);
        $id = DB::transaction(function () use ($r) {
            $id = DB::table('env_cemaran')->insertGetId(['tanggal' => $r->tanggal, 'petugas' => $r->petugas, 'shift' => $r->shift,
                'created_by' => auth()->id(), 'created_at' => now(), 'updated_at' => now()]);
            foreach (config('gapura.cemaran_items') as $i => [$area, $param, $pic]) {
                $tercemar = ($r->input("status.$i") === 'tercemar');
                $foto = ($tercemar && $r->hasFile("foto.$i")) ? $r->file("foto.$i")->store('cemaran', 'public') : null;
                DB::table('env_cemaran_items')->insert(['cemaran_id' => $id, 'no' => $i + 1, 'area' => $area, 'parameter' => $param,
                    'status' => $tercemar ? 'tercemar' : 'bersih', 'foto' => $foto,
                    'tindakan' => $tercemar ? $r->input("tindakan.$i") : null, 'rekomendasi' => $tercemar ? $r->input("rekomendasi.$i") : null, 'pic' => $pic]);
            }
            return $id;
        });
        Aktivitas::catat('buat', 'Cemaran B3', "Pemeriksaan cemaran B3 tanggal {$r->tanggal} (petugas {$r->petugas})");
        return redirect()->route('cemaran.show', $id)->with('ok', 'Hasil pemeriksaan tersimpan.');
    }

    public function cemaranShow(int $id)
    {
        $h = DB::table('env_cemaran')->find($id); abort_unless($h, 404);
        return view('lingkungan.cemaran-show', ['h' => $h, 'items' => DB::table('env_cemaran_items')->where('cemaran_id', $id)->orderBy('no')->get(), 'stat' => $this->persenBersih($id)]);
    }

    public function cemaranDestroy(int $id)
    {
        Aktivitas::catat('hapus', 'Cemaran B3', "Menghapus pemeriksaan cemaran #$id");
        foreach (DB::table('env_cemaran_items')->where('cemaran_id', $id)->whereNotNull('foto')->pluck('foto') as $f) Storage::disk('public')->delete($f);
        DB::table('env_cemaran_items')->where('cemaran_id', $id)->delete(); DB::table('env_cemaran')->where('id', $id)->delete();
        return redirect()->route('cemaran.index')->with('ok', 'Pemeriksaan dihapus.');
    }

    // ---- Logbook B3 ----
    public function b3()
    {
        $neraca = $this->neraca();
        return view('lingkungan.b3', [
            'masuk' => DB::table('env_b3_masuk')->orderByDesc('tanggal')->orderByDesc('id')->limit(100)->get(),
            'keluar' => DB::table('env_b3_keluar')->orderByDesc('tanggal')->orderByDesc('id')->limit(100)->get(),
            'neraca' => $neraca, 'tersimpan' => collect($neraca)->sum('sisa'),
            'tps' => DB::table('env_b3_tps')->orderByDesc('tanggal')->orderByDesc('id')->first(),
        ]);
    }

    public function b3Tps(Request $r)
    {
        $items = []; foreach (config('gapura.tps_items') as $i => $label) $items[$label] = $r->input("tps.$i") === 'Tidak Baik' ? 'Tidak Baik' : 'Baik';
        DB::table('env_b3_tps')->insert(['tanggal' => $r->date('tanggal') ?? now()->toDateString(), 'petugas' => $r->petugas, 'items' => json_encode($items), 'created_at' => now(), 'updated_at' => now()]);
        Aktivitas::catat('buat', 'Limbah B3', 'Checklist fasilitas TPS B3');
        return redirect()->route('b3')->with('ok', 'Checklist fasilitas TPS tersimpan.');
    }

    public function tpsForm()
    {
        $tps = DB::table('env_b3_tps')->orderByDesc('tanggal')->orderByDesc('id')->first();
        return view('lingkungan.tps-form', ['tpsVal' => $tps ? json_decode($tps->items, true) : []]);
    }

    // ---- Non-B3 ----
    public function nonb3()
    {
        $q = fn($t) => DB::table($t)->orderByDesc('tanggal')->orderByDesc('id')->limit(100)->get();
        return view('lingkungan.nonb3', ['organik' => $q('env_organik'), 'domestik' => $q('env_domestik'),
            'totOrg' => DB::table('env_organik')->sum('berat'), 'totDom' => DB::table('env_domestik')->sum('volume')]);
    }
}
