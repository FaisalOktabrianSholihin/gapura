<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $t) {
            $t->id(); $t->string('slug')->unique(); $t->string('nama'); $t->string('deskripsi')->nullable();
            $t->json('permissions'); $t->boolean('is_sistem')->default(false); $t->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id')->nullable()->index(); $t->string('user_nama')->nullable();
            $t->string('aksi', 30); $t->string('modul', 40); $t->text('deskripsi')->nullable(); $t->string('ip', 45)->nullable();
            $t->timestamp('created_at')->useCurrent()->index();
        });
        Schema::table('akun_users', function (Blueprint $t) { $t->unsignedBigInteger('role_id')->nullable()->after('password'); });

        $now = now();
        $umum = ['sppd.ajukan','sppd.semua','sppd.setujui','lingkungan.lihat','lingkungan.kelola','lingkungan.import','log.lihat'];
        foreach ([['super_admin','Super Admin','Akses penuh ke seluruh fitur',['*']], ['umum','Bagian Umum','Memproses SPPD & mengelola monitoring lingkungan',$umum], ['user','Pegawai','Mengajukan & memantau SPPD sendiri',['sppd.ajukan']]] as [$slug,$nama,$d,$p]) {
            DB::table('roles')->insert(['slug'=>$slug,'nama'=>$nama,'deskripsi'=>$d,'permissions'=>json_encode($p),'is_sistem'=>true,'created_at'=>$now,'updated_at'=>$now]);
        }
        $id = fn($s) => DB::table('roles')->where('slug', $s)->value('id');
        DB::table('akun_users')->where('role', 'umum')->update(['role_id' => $id('umum')]);
        DB::table('akun_users')->whereNull('role_id')->update(['role_id' => $id('user')]);
        DB::table('akun_users')->where('nik', 'administrator@gmail.com')->update(['role_id' => $id('super_admin')]);
    }

    public function down(): void
    {
        Schema::table('akun_users', function (Blueprint $t) { $t->dropColumn('role_id'); });
        Schema::dropIfExists('activity_logs'); Schema::dropIfExists('roles');
    }
};
