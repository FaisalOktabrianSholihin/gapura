<?php
namespace App\Http\Controllers;
use App\Support\Aktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// CRUD generik log lingkungan; definisi kolom di config/gapura.php ('logs'). Tambah/ubah = halaman terpisah.
class LogController extends Controller
{
    private function cfg(string $key): array { $c = config("gapura.logs.$key"); abort_unless($c, 404); return $c; }

    private function rules(array $cfg): array
    {
        $rules = [];
        foreach ($cfg['fields'] as $f) {
            [$col, , $type] = $f;
            $rules[$col] = (in_array('req', $f) ? 'required' : 'nullable') . '|' . match (true) {
                $type === 'date' => 'date', $type === 'number' => 'numeric', $type === 'time' => 'date_format:H:i', default => 'string|max:255',
            };
        }
        return $rules;
    }

    public function create(string $key) { return view('lingkungan.log-form', ['key' => $key, 'cfg' => $this->cfg($key), 'row' => null]); }

    public function store(Request $r, string $key)
    {
        $cfg = $this->cfg($key);
        DB::table($cfg['table'])->insert($r->validate($this->rules($cfg)) + ['created_at' => now(), 'updated_at' => now()]);
        Aktivitas::catat('buat', $cfg['title'], 'Menambah data ' . $cfg['title']);
        return redirect()->route($cfg['back'])->with('ok', 'Data tersimpan.');
    }

    public function edit(string $key, int $id)
    {
        $cfg = $this->cfg($key); $row = DB::table($cfg['table'])->find($id); abort_unless($row, 404);
        return view('lingkungan.log-form', compact('key', 'cfg', 'row'));
    }

    public function update(Request $r, string $key, int $id)
    {
        $cfg = $this->cfg($key);
        DB::table($cfg['table'])->where('id', $id)->update($r->validate($this->rules($cfg)) + ['updated_at' => now()]);
        Aktivitas::catat('ubah', $cfg['title'], "Mengubah data #$id " . $cfg['title']);
        return redirect()->route($cfg['back'])->with('ok', 'Perubahan tersimpan.');
    }

    public function destroy(string $key, int $id)
    {
        $cfg = $this->cfg($key);
        DB::table($cfg['table'])->where('id', $id)->delete();
        Aktivitas::catat('hapus', $cfg['title'], "Menghapus data #$id " . $cfg['title']);
        return back()->with('ok', 'Data dihapus.');
    }
}
