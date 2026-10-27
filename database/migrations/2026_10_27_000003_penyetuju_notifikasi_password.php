<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Crypt, DB, Hash, Schema};

// 1) kolom password_enc (salinan password terenkripsi, hanya bisa dilihat Super Admin)
// 2) tabel notifikasi SPPD
// 3) role "Penyetuju" + izin ACC/Revisi/Tolak dicabut dari role "Bagian Umum"
return new class extends Migration {
    public function up(): void
    {
        Schema::table('akun_users', function (Blueprint $t) { $t->text('password_enc')->nullable()->after('password'); });

        Schema::create('notifikasis', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();       // penerima
            $t->unsignedBigInteger('sppd_id')->nullable()->index();
            $t->string('tipe', 30);                            // diajukan | disetujui | revisi | ditolak
            $t->string('judul');
            $t->text('pesan')->nullable();
            $t->timestamp('dibaca_at')->nullable();
            $t->timestamps();
        });

        $now = now();
        if (!DB::table('roles')->where('slug', 'penyetuju')->exists()) {
            DB::table('roles')->insert([
                'slug' => 'penyetuju', 'nama' => 'Penyetuju', 'deskripsi' => 'Satu-satunya role yang dapat ACC / Revisi / Tolak SPPD',
                'permissions' => json_encode(['sppd.ajukan', 'sppd.semua', 'sppd.setujui']), 'is_sistem' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $umum = DB::table('roles')->where('slug', 'umum')->first();
        if ($umum) {
            $p = array_values(array_diff(json_decode($umum->permissions, true) ?: [], ['sppd.setujui']));
            DB::table('roles')->where('id', $umum->id)->update(['permissions' => json_encode($p), 'updated_at' => $now]);
        }

        // Akun yang password-nya masih sama dengan NIK (hasil sinkron) langsung dicatat agar bisa dilihat Super Admin.
        foreach (DB::table('akun_users')->get(['id', 'nik', 'password']) as $u) {
            if ($u->nik && $u->password && Hash::check((string) $u->nik, $u->password)) {
                DB::table('akun_users')->where('id', $u->id)->update(['password_enc' => Crypt::encryptString((string) $u->nik)]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
        Schema::table('akun_users', function (Blueprint $t) { $t->dropColumn('password_enc'); });
        DB::table('roles')->where('slug', 'penyetuju')->delete();
    }
};
