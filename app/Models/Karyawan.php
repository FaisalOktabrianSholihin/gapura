<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $connection = 'indukk';
    protected $table = 'karyawans'; // tabel milik db_indukk
    public function jabatan() { return $this->belongsTo(Jabatan::class, 'jabatan_id'); }
    public function divisi()  { return $this->belongsTo(Divisi::class, 'divisi_id'); }
}
