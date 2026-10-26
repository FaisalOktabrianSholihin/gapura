# GAPURA — Gerbang Administrasi & Pelayanan Umum Terpadu
Website Bagian Umum PT Mitratani Dua Tujuh (Laravel + MySQL, terhubung ke `db_indukk`).

Modul: **SPPD** (pengajuan → ACC/revisi/tolak → cetak dengan QR validasi) dan **Monitoring Lingkungan**
(Dashboard, IPAL, Checklist Cemaran B3, Logbook Limbah B3, Limbah Non-B3).

## Instalasi (folder ini adalah "overlay" untuk proyek Laravel baru)
```bash
composer create-project laravel/laravel gapura
cd gapura
composer require bacon/bacon-qr-code            # pembuat QR (SVG, tanpa ekstensi tambahan)

# salin isi folder ini ke dalam proyek (timpa jika ditanya): app/ config/ database/ public/ resources/ routes/
```
`.env`:
```
APP_NAME=GAPURA
APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=gapura          # tabel GAPURA; data induk dibaca via koneksi "indukk" (db_indukk)
DB_USERNAME=root
DB_PASSWORD=
GAPURA_DEBIT_IZIN=100        # batas debit limbah (m3/hari) sesuai izin
GAPURA_KAPASITAS_TPS=500     # kapasitas TPS B3 (kg)
```
```bash
php artisan migrate                       # hanya MEMBUAT tabel baru; karyawans/divisis/jabatans/settings tidak diubah
php artisan storage:link                  # untuk foto temuan cemaran
php artisan gapura:sync-users --umum=NIK_ADMIN1 --umum=NIK_ADMIN2   # akun dari tabel karyawans
php artisan serve
```
Login: **username = NIK, password awal = NIK** (segera ganti lewat menu "Ganti password" di Beranda).
NIK yang diberi `--umum` menjadi admin Bagian Umum (bisa ACC SPPD & membuka Monitoring Lingkungan).
Jalankan `gapura:sync-users` lagi kapan saja untuk menambah karyawan baru (akun lama tidak ditimpa).

## Alur SPPD
1. Pegawai mengajukan SPPD (data diri otomatis dari `karyawans`; tujuan, keperluan, rombongan, dan **perusahaan tujuan bisa ditambah dinamis**).
2. Bagian Umum membuka SPPD → **ACC / Revisi / Tolak** (nomor surat otomatis, bisa diubah).
3. Revisi/Tolak muncul di Beranda & detail pemohon beserta catatan; pemohon memperbaiki lalu ajukan ulang.
4. Setelah ACC, pemohon bisa **Cetak** (A4). Blok pengesahan no. 1 (PT Mitratani Dua Tujuh) dan no. 2+ (perusahaan tujuan) dikosongkan untuk TTD & stempel basah.
5. QR di surat membuka halaman publik `/verifikasi/{token}` → "TELAH DIVALIDASI" (atau "TIDAK VALID" bila dibatalkan).
Penandatangan (Erison Caesar, Sekretaris Perusahaan) bisa diganti lewat tabel `settings`
(key `sppd_ttd_nama`, `sppd_ttd_jabatan`) atau `config/gapura.php`.

## Catatan
- Ganti `public/images/logo.png` dengan logo asli beresolusi tinggi (bawaan dipotong dari gambar contoh, agak buram).
- Daftar master (area/parameter cemaran, jenis limbah B3, dll) ada di `config/gapura.php`.
- Tailwind/Alpine/Chart.js dimuat via CDN agar tanpa `npm build`; untuk produksi tanpa internet, unduh & host lokal.
- Kode ditulis tanpa bisa dijalankan di lingkungan pembuatnya (tanpa PHP) — jalankan `php artisan migrate` & uji alur sekali sebelum dipakai.

## Akun administrator (untuk pengembangan)
```bash
php artisan tinker
>>> App\Models\User::updateOrCreate(['nik'=>'administrator@gmail.com'],['name'=>'Administrator','password'=>'password','role_id'=>App\Models\Role::where('slug','super_admin')->value('id')]);
```
Login `administrator@gmail.com` / `password` (Super Admin). Ganti password sebelum dipakai sungguhan.

## Roles & izin
Tiga role bawaan: **Super Admin** (semua izin), **Bagian Umum** (proses SPPD + monitoring lingkungan + log aktivitas), **Pegawai** (ajukan SPPD).
Menu **Roles** untuk membuat role baru dan mencentang izin; menu **User** untuk mengatur role tiap akun;
menu **Log Aktivitas** merekam login, SPPD, data lingkungan, import, user & role.
Setelah `php artisan migrate`, akun lama otomatis dipetakan (role `umum` -> Bagian Umum, `administrator@gmail.com` -> Super Admin, sisanya Pegawai).

## Import data lingkungan
Tombol **Import** ada di setiap tabel (IPAL, Limbah Masuk/Keluar B3, Organik, Domestik) dan di Checklist Cemaran B3.
Mendukung `.xlsx` (butuh ekstensi PHP `zip`, sudah aktif di XAMPP) dan `.csv`. Judul kolom dikenali otomatis sehingga Excel lama bisa dipakai;
untuk file `.xls` lama simpan ulang sebagai `.xlsx`. Template CSV bisa diunduh dari halaman import.
Checklist TPS B3 diisi lewat form (tidak bisa diimpor); foto temuan cemaran tidak ikut diimpor.
