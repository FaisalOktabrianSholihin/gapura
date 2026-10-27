<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;

class NotifikasiController extends Controller
{
    // JSON untuk lonceng di header (di-poll tiap 30 detik)
    public function index()
    {
        $q = Notifikasi::where('user_id', auth()->id());
        return response()->json([
            'unread' => (clone $q)->whereNull('dibaca_at')->count(),
            'items' => $q->orderByDesc('id')->limit(15)->get()->map(fn($n) => [
                'id' => $n->id,
                'tipe' => $n->tipe,
                'judul' => $n->judul,
                'pesan' => $n->pesan,
                'waktu' => $n->created_at?->diffForHumans(),
                'baru' => $n->dibaca_at === null,
                'url' => route('notifikasi.buka', $n->id),
            ])->values(),
        ]);
    }

    public function buka(int $id)
    {
        $n = Notifikasi::where('user_id', auth()->id())->findOrFail($id);
        if (!$n->dibaca_at) $n->update(['dibaca_at' => now()]);
        return redirect($n->sppd_id && \App\Models\Sppd::whereKey($n->sppd_id)->exists() ? route('sppd.show', $n->sppd_id) : route('home'));
    }

    public function bacaSemua()
    {
        Notifikasi::where('user_id', auth()->id())->whereNull('dibaca_at')->update(['dibaca_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
