<?php
use App\Http\Controllers\{ActivityController, AuthController, HomeController, ImportController, LingkunganController, LogController, NotifikasiController, RoleController, SppdController, UserController};
use App\Http\Middleware\{Izin, Penyetuju};
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('home'));
Route::get('/login', [AuthController::class, 'form'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/verifikasi/{token}', [SppdController::class, 'verifikasi'])->name('sppd.verifikasi'); // publik (hasil scan QR)

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/password', [AuthController::class, 'password'])->name('password');
    Route::get('/beranda', [HomeController::class, 'index'])->name('home');

    // SPPD
    Route::get('/sppd/{sppd}/cetak', [SppdController::class, 'cetak'])->name('sppd.cetak');
    Route::resource('sppd', SppdController::class);
    Route::post('/sppd/{sppd}/keputusan', [SppdController::class, 'keputusan'])->name('sppd.keputusan')->middleware(Penyetuju::class); // hanya role Penyetuju

    // Notifikasi (lonceng di header)
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::get('/notifikasi/{id}/buka', [NotifikasiController::class, 'buka'])->whereNumber('id')->name('notifikasi.buka');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca');

    // Monitoring Lingkungan
    Route::prefix('lingkungan')->middleware(Izin::class . ':lingkungan.lihat')->group(function () {
        Route::get('/', [LingkunganController::class, 'dashboard'])->name('lingkungan');
        Route::get('/ipal', [LingkunganController::class, 'ipal'])->name('ipal');
        Route::get('/cemaran', [LingkunganController::class, 'cemaranIndex'])->name('cemaran.index');
        Route::get('/limbah-b3', [LingkunganController::class, 'b3'])->name('b3');
        Route::get('/limbah-non-b3', [LingkunganController::class, 'nonb3'])->name('nonb3');

        Route::middleware(Izin::class . ':lingkungan.kelola')->group(function () {
            Route::get('/cemaran/baru', [LingkunganController::class, 'cemaranCreate'])->name('cemaran.create');
            Route::post('/cemaran', [LingkunganController::class, 'cemaranStore'])->name('cemaran.store');
            Route::delete('/cemaran/{id}', [LingkunganController::class, 'cemaranDestroy'])->name('cemaran.destroy');
            Route::get('/limbah-b3/tps/baru', [LingkunganController::class, 'tpsForm'])->name('b3.tps.form');
            Route::post('/limbah-b3/tps', [LingkunganController::class, 'b3Tps'])->name('b3.tps');
            Route::get('/log/{key}/baru', [LogController::class, 'create'])->name('log.create');
            Route::post('/log/{key}', [LogController::class, 'store'])->name('log.store');
            Route::get('/log/{key}/{id}/ubah', [LogController::class, 'edit'])->name('log.edit');
            Route::put('/log/{key}/{id}', [LogController::class, 'update'])->name('log.update');
            Route::delete('/log/{key}/{id}', [LogController::class, 'destroy'])->name('log.destroy');
        });
        Route::get('/cemaran/{id}', [LingkunganController::class, 'cemaranShow'])->whereNumber('id')->name('cemaran.show');

        Route::middleware(Izin::class . ':lingkungan.import,lingkungan.kelola')->prefix('import')->group(function () {
            Route::get('/{key}', [ImportController::class, 'form'])->name('import.form');
            Route::get('/{key}/template', [ImportController::class, 'template'])->name('import.template');
            Route::post('/{key}', [ImportController::class, 'store'])->name('import.store');
        });
    });

    // Administrasi
    Route::middleware(Izin::class . ':user.kelola')->group(function () {
        Route::post('/users/sinkron', [UserController::class, 'sinkron'])->name('users.sinkron');
        Route::post('/users/{user}/password', [UserController::class, 'lihatPassword'])->name('users.password'); // khusus Super Admin
        Route::resource('users', UserController::class)->except('show');
    });
    Route::middleware(Izin::class . ':role.kelola')->resource('roles', RoleController::class)->except('show');
    Route::get('/log-aktivitas', [ActivityController::class, 'index'])->name('activity')->middleware(Izin::class . ':log.lihat');
});
