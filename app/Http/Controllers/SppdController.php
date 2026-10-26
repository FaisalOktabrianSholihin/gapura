<?php
namespace App\Http\Controllers;
use App\Models\{Sppd, SppdItem};
use App\Support\{Aktivitas, Setting};
use BaconQrCode\Renderer\{ImageRenderer, RendererStyle\RendererStyle};
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\{Str, Facades\DB};

class SppdController extends Controller
{
    private function akses(Sppd $s): void
    {
        abort_unless(auth()->user()->isUmum() || $s->user_id === auth()->id(), 403);
    }

    public function index(Request $r)
    {
        $u = auth()->user();
        $list = Sppd::query()->when(!$u->isUmum(), fn($q) => $q->where('user_id', $u->id))
            ->when($r->status, fn($q, $v) => $q->where('status', $v))
            ->when($r->divisi, fn($q, $v) => $q->where('divisi', $v))
            ->when($r->q, fn($q, $v) => $q->where(fn($w) => $w->where('nama', 'like', "%$v%")->orWhere('nomor', 'like', "%$v%")->orWhere('nik', 'like', "%$v%")))
            ->latest()->paginate(15)->withQueryString();
        $divisis = $u->isUmum() ? DB::connection('indukk')->table('divisis')->orderBy('nama_divisi')->pluck('nama_divisi') : collect();
        return view('sppd.index', compact('list', 'divisis'));
    }

    public function create()
    {
        abort_unless(auth()->user()->izin('sppd.ajukan'), 403);
        return view('sppd.form', ['sppd' => new Sppd(['fasilitas' => []]), 'k' => auth()->user()->karyawan?->load(['jabatan', 'divisi'])]);
    }

    public function store(Request $r)
    {
        abort_unless(auth()->user()->izin('sppd.ajukan'), 403);
        $d = $this->validasi($r); $k = auth()->user()->karyawan?->load(['jabatan', 'divisi']);
        $s = Sppd::create($d['head'] + [
            'user_id' => auth()->id(), 'karyawan_id' => $k?->id, 'token' => (string) Str::uuid(), 'status' => 'diajukan',
            'nama' => $k->nama ?? auth()->user()->name, 'nik' => $k->nik ?? auth()->user()->nik,
            'jabatan' => $k?->jabatan?->nama_jabatan, 'divisi' => $k?->divisi?->nama_divisi,
        ]);
        $this->simpanItems($s, $d['items']);
        Aktivitas::catat('buat', 'SPPD', "Mengajukan SPPD #{$s->id} a.n. {$s->nama}");
        return redirect()->route('sppd.show', $s)->with('ok', 'SPPD berhasil diajukan. Menunggu persetujuan Bagian Umum.');
    }

    public function show(Sppd $sppd)
    {
        $this->akses($sppd); $sppd->load('items');
        return view('sppd.show', ['s' => $sppd, 'nomorSaran' => $this->nomorBaru()]);
    }

    public function edit(Sppd $sppd)
    {
        $this->akses($sppd); abort_if(!$sppd->bisa_diubah || $sppd->user_id !== auth()->id(), 403);
        return view('sppd.form', ['sppd' => $sppd->load('items'), 'k' => null]);
    }

    public function update(Request $r, Sppd $sppd)
    {
        $this->akses($sppd); abort_if(!$sppd->bisa_diubah || $sppd->user_id !== auth()->id(), 403);
        $d = $this->validasi($r);
        $sppd->update($d['head'] + ['status' => 'diajukan']); // diajukan ulang setelah revisi
        $this->simpanItems($sppd, $d['items']);
        Aktivitas::catat('ubah', 'SPPD', "Merevisi & mengajukan ulang SPPD #{$sppd->id}");
        return redirect()->route('sppd.show', $sppd)->with('ok', 'Perubahan tersimpan & diajukan ulang.');
    }

    public function destroy(Sppd $sppd)
    {
        $this->akses($sppd); abort_if($sppd->status === 'disetujui' && !auth()->user()->isUmum(), 403);
        Aktivitas::catat('hapus', 'SPPD', "Menghapus SPPD #{$sppd->id} a.n. {$sppd->nama}");
        $sppd->items()->delete(); $sppd->delete();
        return redirect()->route('sppd.index')->with('ok', 'SPPD dihapus.');
    }

    public function keputusan(Request $r, Sppd $sppd)
    {
        $r->validate(['aksi' => 'required|in:setuju,revisi,tolak', 'catatan' => 'required_if:aksi,revisi,tolak|nullable|string|max:1000', 'nomor' => 'nullable|string|max:100|unique:sppds,nomor,' . $sppd->id]);
        if ($r->aksi === 'setuju') {
            $sppd->update(['status' => 'disetujui', 'nomor' => $r->nomor ?: ($sppd->nomor ?: $this->nomorBaru()), 'catatan_admin' => $r->catatan,
                'approved_by' => auth()->id(), 'approved_at' => now(), 'tanggal_terbit' => now()->toDateString()]);
            $msg = 'SPPD disetujui. Pemohon sekarang dapat mencetak surat.';
        } else {
            $sppd->update(['status' => $r->aksi === 'revisi' ? 'revisi' : 'ditolak', 'catatan_admin' => $r->catatan, 'nomor' => null, 'approved_at' => null]);
            $msg = $r->aksi === 'revisi' ? 'SPPD dikembalikan untuk revisi.' : 'SPPD ditolak.';
        }
        Aktivitas::catat($r->aksi === 'setuju' ? 'setujui' : $r->aksi, 'SPPD', "Keputusan SPPD #{$sppd->id} a.n. {$sppd->nama}: " . $sppd->status_label);
        return back()->with('ok', $msg);
    }

    public function cetak(Sppd $sppd)
    {
        $this->akses($sppd); abort_unless($sppd->status === 'disetujui', 403, 'SPPD belum disetujui.');
        $sppd->load('items'); Aktivitas::catat('cetak', 'SPPD', 'Mencetak SPPD ' . $sppd->nomor);
        $svg = (new Writer(new ImageRenderer(new RendererStyle(110, 0), new SvgImageBackEnd())))->writeString(route('sppd.verifikasi', $sppd->token));
        return view('sppd.print', ['s' => $sppd, 'qr' => preg_replace('/^<\?xml.*?\?>\s*/s', '', $svg), 'ttd' => Setting::ttd()]);
    }

    public function verifikasi(string $token)
    {
        $s = Sppd::with('items')->where('token', $token)->first();
        return view('sppd.verify', ['s' => $s, 'valid' => $s && $s->status === 'disetujui', 'ttd' => Setting::ttd()]);
    }

    // ---------- helper ----------
    private function validasi(Request $r): array
    {
        $r->validate([
            'nomor_surat_tugas' => 'nullable|string|max:100',
            'jenis_perjalanan' => 'required|in:' . implode(',', config('gapura.jenis_perjalanan')),
            'tanggal_berangkat' => 'required|date', 'tanggal_kembali' => 'required|date|after_or_equal:tanggal_berangkat',
            'transportasi' => 'nullable|string|max:100',
            'tujuan' => 'required|array|min:1', 'keperluan' => 'required|array|min:1',
            'rombongan' => 'nullable|array', 'perusahaan' => 'required|array|min:1',
            'fasilitas' => 'nullable|array',
        ], ['tujuan.required' => 'Isi minimal satu tujuan dinas.', 'perusahaan.required' => 'Isi minimal satu perusahaan/instansi yang dituju.']);
        $bersih = fn($a) => collect($a)->map(fn($v) => trim((string) $v))->filter()->values()->all();
        $fas = []; foreach (array_keys(config('gapura.fasilitas')) as $k) $fas[$k] = ($r->input("fasilitas.$k") === 'ya') ? 'ya' : 'tidak';
        return [
            'head' => $r->only(['nomor_surat_tugas', 'jenis_perjalanan', 'tanggal_berangkat', 'tanggal_kembali', 'transportasi']) + ['fasilitas' => $fas],
            'items' => ['tujuan' => $bersih($r->tujuan), 'keperluan' => $bersih($r->keperluan), 'rombongan' => $bersih($r->rombongan), 'perusahaan' => $bersih($r->perusahaan)],
        ];
    }

    private function simpanItems(Sppd $s, array $items): void
    {
        $s->items()->delete();
        foreach ($items as $tipe => $arr) foreach ($arr as $i => $t) SppdItem::create(['sppd_id' => $s->id, 'tipe' => $tipe, 'urutan' => $i, 'teks' => $t]);
    }

    private function nomorBaru(): string
    {
        $roman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $n = Sppd::whereYear('tanggal_terbit', now()->year)->whereNotNull('nomor')->count() + 1;
        while (Sppd::where('nomor', $no = sprintf('%03d/SPPD/MT27/%s/%d', $n, $roman[now()->month], now()->year))->exists()) $n++;
        return $no;
    }
}
