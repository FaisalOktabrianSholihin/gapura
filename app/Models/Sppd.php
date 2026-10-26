<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Sppd extends Model
{
    protected $guarded = [];
    protected $casts = ['fasilitas' => 'array', 'tanggal_berangkat' => 'date', 'tanggal_kembali' => 'date',
                        'tanggal_terbit' => 'date', 'approved_at' => 'datetime'];

    public function user()  { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(SppdItem::class)->orderBy('urutan'); }
    public function daftar(string $tipe) { return $this->items->where('tipe', $tipe)->pluck('teks')->values(); }

    public function getStatusLabelAttribute(): string
    {
        return ['diajukan'=>'Menunggu Persetujuan','disetujui'=>'Disetujui','revisi'=>'Perlu Revisi','ditolak'=>'Ditolak'][$this->status] ?? $this->status;
    }
    public function getBisaDiubahAttribute(): bool { return $this->status !== 'disetujui'; }
}
