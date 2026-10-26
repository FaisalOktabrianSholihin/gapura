<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Hanya membuat tabel baru. Tabel karyawans/divisis/jabatans/settings dari db_indukk TIDAK disentuh.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('akun_users', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('karyawan_id')->nullable()->index();
            $t->string('nik')->unique();
            $t->string('name');
            $t->string('password');
            $t->string('role')->default('user'); // user | umum
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('sppds', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->unsignedBigInteger('karyawan_id')->nullable();
            $t->string('nomor')->nullable()->unique();
            $t->string('nomor_surat_tugas')->nullable();
            $t->string('nama'); $t->string('nik');
            $t->string('jabatan')->nullable(); $t->string('divisi')->nullable();
            $t->string('jenis_perjalanan');
            $t->date('tanggal_berangkat'); $t->date('tanggal_kembali');
            $t->string('transportasi')->nullable();
            $t->json('fasilitas')->nullable();
            $t->string('status')->default('diajukan'); // diajukan|disetujui|revisi|ditolak
            $t->text('catatan_admin')->nullable();
            $t->uuid('token')->unique();
            $t->unsignedBigInteger('approved_by')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->date('tanggal_terbit')->nullable();
            $t->timestamps();
        });

        // Daftar dinamis: tujuan, keperluan, rombongan, perusahaan (instansi yang dituju)
        Schema::create('sppd_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('sppd_id')->index();
            $t->string('tipe', 20);
            $t->unsignedSmallInteger('urutan')->default(0);
            $t->string('teks');
        });

        Schema::create('env_ipal', function (Blueprint $t) {
            $t->id(); $t->date('tanggal'); $t->time('jam')->nullable(); $t->string('petugas')->nullable();
            $t->decimal('ph_inlet',5,2)->nullable(); $t->decimal('ph_outlet',5,2)->nullable();
            $t->decimal('debit',10,2)->nullable(); $t->decimal('suhu',5,1)->nullable();
            $t->decimal('cod',10,2)->nullable(); $t->decimal('bod',10,2)->nullable(); $t->decimal('tss',10,2)->nullable();
            $t->string('keterangan')->nullable(); $t->timestamps();
        });

        Schema::create('env_cemaran', function (Blueprint $t) {
            $t->id(); $t->date('tanggal'); $t->string('petugas'); $t->string('shift')->nullable();
            $t->unsignedBigInteger('created_by')->nullable(); $t->timestamps();
        });
        Schema::create('env_cemaran_items', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('cemaran_id')->index(); $t->unsignedSmallInteger('no');
            $t->string('area'); $t->string('parameter'); $t->string('status')->default('bersih');
            $t->string('foto')->nullable(); $t->string('tindakan')->nullable(); $t->string('rekomendasi')->nullable(); $t->string('pic')->nullable();
        });

        Schema::create('env_b3_masuk', function (Blueprint $t) {
            $t->id(); $t->date('tanggal'); $t->string('jenis'); $t->string('sumber')->nullable();
            $t->decimal('jumlah',10,2)->default(0); $t->string('keterangan')->nullable(); $t->timestamps();
        });
        Schema::create('env_b3_keluar', function (Blueprint $t) {
            $t->id(); $t->date('tanggal'); $t->string('jenis'); $t->decimal('jumlah',10,2)->default(0);
            $t->string('tujuan')->nullable(); $t->string('manifest')->nullable(); $t->timestamps();
        });
        Schema::create('env_b3_tps', function (Blueprint $t) {
            $t->id(); $t->date('tanggal'); $t->string('petugas')->nullable(); $t->json('items'); $t->timestamps();
        });

        Schema::create('env_organik', function (Blueprint $t) {
            $t->id(); $t->date('tanggal'); $t->string('jenis'); $t->decimal('berat',10,2)->default(0);
            $t->string('tujuan')->nullable(); $t->string('dokumen')->nullable(); $t->string('keterangan')->nullable(); $t->timestamps();
        });
        Schema::create('env_domestik', function (Blueprint $t) {
            $t->id(); $t->date('tanggal'); $t->string('jenis'); $t->decimal('volume',10,2)->default(0);
            $t->string('pengangkut')->nullable(); $t->string('dokumen')->nullable(); $t->string('keterangan')->nullable(); $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['env_domestik','env_organik','env_b3_tps','env_b3_keluar','env_b3_masuk','env_cemaran_items','env_cemaran','env_ipal','sppd_items','sppds','akun_users'] as $t) Schema::dropIfExists($t);
    }
};
