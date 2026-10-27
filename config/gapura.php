<?php
// Konfigurasi & master data GAPURA (Gerbang Administrasi & Pelayanan Umum Terpadu)
return [
    'perusahaan' => [
        'nama'   => 'PT Mitratani Dua Tujuh',
        'bidang' => 'Pengolahan Makanan / Agroindustri',
        'alamat' => 'Jl. Brawijaya 83 Mangli Jember',
        'kota_terbit' => 'Jember',
    ],
    // Penandatangan default SPPD (bisa di-override lewat tabel settings: sppd_ttd_nama, sppd_ttd_jabatan)
    'penandatangan' => ['nama' => 'Erison Caesar', 'jabatan' => 'Sekretaris Perusahaan'],
    'ipal' => [
        'ph_min' => 6, 'ph_max' => 9,
        'debit_izin' => (float) env('GAPURA_DEBIT_IZIN', 100), // m3/hari, sesuaikan dengan izin
    ],
    'kapasitas_tps_b3' => (float) env('GAPURA_KAPASITAS_TPS', 500), // kg

    'jenis_perjalanan' => ['Dalam Negeri', 'Luar Negeri'],
    'transportasi' => ['Kendaraan Dinas', 'Kendaraan Pribadi', 'Pesawat', 'Kereta Api', 'Bus', 'Travel'],
    'fasilitas' => [
        'antar_asal'    => 'Antar ke Bandara/Stasiun Asal Dinas',
        'jemput_asal'   => 'Jemput ke Bandara/Stasiun Asal Dinas',
        'operasional'   => 'Operasional saat Hari Dinas',
        'antar_tujuan'  => 'Antar ke Bandara/Stasiun Tujuan Dinas',
        'jemput_tujuan' => 'Jemput ke Bandara/Stasiun Tujuan Dinas',
    ],

    'cemaran_items' => [
        ['Area Produksi','Ada kain majun tercecer/terbuang sembarangan','teknik'],
        ['Area Produksi','Ada ceceran oli/minyak di lantai','teknik'],
        ['Area Produksi','Ada tumpahan bahan kimia (pelarut, thinner, dll)','teknik'],
        ['Area Produksi','Ada sisa kemasan bahan kimia (kaleng, drum, jerigen)','teknik'],
        ['Area Produksi','Ada kertas/kardus terkontaminasi bahan kimia','teknik'],
        ['Area Penyimpanan Bahan Kimia','Ada kebocoran pada wadah penyimpanan','gudang'],
        ['Area Penyimpanan Bahan Kimia','Ada tumpahan di sekitar area penyimpanan','gudang'],
        ['Area Penyimpanan Bahan Kimia','Wadah tidak tertutup rapat','gudang'],
        ['Area Penyimpanan Bahan Kimia','Label B3 tidak terpasang/tidak jelas','gudang'],
        ['TPS B3','Ada limbah B3 di luar wadah/container','umum'],
        ['TPS B3','Container tidak tertutup rapat','umum'],
        ['TPS B3','Ada tumpahan oli/cairan B3 di lantai TPS','umum'],
        ['TPS B3','Label/simbol B3 tidak terpasang','umum'],
        ['Area IPAL','Ada ceceran sludge/lumpur IPAL','umum'],
        ['Area IPAL','Ada tumpahan bahan kimia pengolahan (kaporit, PAC)','umum'],
        ['Area IPAL','Wadah bahan kimia tidak tertutup','umum'],
        ['Area Gudang','Ada kardus/kemasan terkontaminasi oli','gudang'],
        ['Area Gudang','Ada kain majun bekas berserakan','gudang'],
        ['Area Parkir & Jalan','Ada noda oli di lantai/asphalt','umum'],
        ['Area Parkir & Jalan','Ada sisa cairan radiator/aki di jalan','umum'],
        ['Area Perkantoran','Ada lampu TL bekas tidak terkumpul','umum'],
        ['Area Perkantoran','Ada baterai/aki bekas berserakan','umum'],
        ['Area Perkantoran','Ada toner cartridge bekas tidak terkumpul','umum'],
    ],
    'jenis_b3' => ['Oli Bekas','Kain Majun Terkontaminasi','Lampu TL','Kemasan Bahan Kimia','Aki Bekas','Filter Bekas','Sludge IPAL','Lampu Inseckiler','Baterai Bekas','Sealant','Bulpoint','Travo','Termometer'],
    'sumber_b3' => ['Pabrik','Kantor','Laboratorium','Gudang','Bengkel','IPAL'],
    'tps_items' => [
        'Simbol/Label B3 terpasang dengan benar','Palet tidak rusak dan bersih','Spill kit tersedia dan lengkap',
        'APAR di TPS tersedia dan valid','Lantai TPS tidak bocor/retak','Ventilasi TPS berfungsi baik','Wastafel dan shower darurat berfungsi',
    ],
    'jenis_organik' => ['Kulit Edamame','Sayuran Sortiran','Sisa Makanan','Dedaunan','Rumput'],
    'tujuan_organik' => ['Kompos','Pakan Ternak','TPA'],
    'jenis_domestik' => ['Plastik','Kertas','Logam','Kaca','Campuran'],

    // Daftar izin (permission) yang bisa diberikan ke role
    'izin' => [
        'sppd.ajukan'       => 'Mengajukan SPPD',
        'sppd.semua'        => 'Melihat SPPD semua divisi',
        'sppd.setujui'      => 'ACC / Revisi / Tolak SPPD (khusus role Penyetuju)',
        'lingkungan.lihat'  => 'Melihat Monitoring Lingkungan',
        'lingkungan.kelola' => 'Tambah / ubah / hapus data lingkungan',
        'lingkungan.import' => 'Import data lingkungan (Excel/CSV)',
        'user.kelola'       => 'Kelola user',
        'role.kelola'       => 'Kelola roles',
        'log.lihat'         => 'Melihat log aktivitas',
    ],

    // Definisi form log generik (dipakai LogController): [kolom, label, tipe, (req)]
    'logs' => [
        'ipal' => ['table'=>'env_ipal','back'=>'ipal','title'=>'Data Harian IPAL','icon'=>'droplets','sub'=>'Catat hasil pengukuran kualitas air limbah IPAL',
          'groups'=>[['Waktu & Petugas','clock',['tanggal','jam','petugas']],['Kualitas Air','droplets',['ph_inlet','ph_outlet','debit','suhu']],['Beban Pencemar','clipboard',['cod','bod','tss']],['Catatan','file',['keterangan']]],
          'fields'=>[
            ['tanggal','Tanggal','date','req'],['jam','Jam','time'],['petugas','Petugas','text'],
            ['ph_inlet','pH Inlet','number'],['ph_outlet','pH Outlet','number','req'],['debit','Debit (m³)','number','req'],
            ['suhu','Suhu (°C)','number'],['cod','COD (mg/L)','number'],['bod','BOD (mg/L)','number'],['tss','TSS (mg/L)','number'],
            ['keterangan','Keterangan','text'],
        ]],
        'b3-masuk' => ['table'=>'env_b3_masuk','back'=>'b3','title'=>'A. Limbah Masuk','icon'=>'archive','sub'=>'Catat limbah B3 yang masuk ke TPS','fields'=>[
            ['tanggal','Tanggal Masuk','date','req'],['jenis','Jenis Limbah B3','select:jenis_b3','req'],['sumber','Sumber Limbah','select:sumber_b3'],
            ['jumlah','Jumlah (kg)','number','req'],['keterangan','Keterangan','text'],
        ]],
        'b3-keluar' => ['table'=>'env_b3_keluar','back'=>'b3','title'=>'B. Limbah Keluar','icon'=>'archive','sub'=>'Catat limbah B3 yang diserahkan ke vendor pengangkut','fields'=>[
            ['tanggal','Tanggal Keluar','date','req'],['jenis','Jenis Limbah B3','select:jenis_b3','req'],['jumlah','Jumlah (kg)','number','req'],
            ['tujuan','Tujuan (Vendor)','text'],['manifest','No. Manifest/Festronik','text'],
        ]],
        'organik' => ['table'=>'env_organik','back'=>'nonb3','title'=>'A. Limbah Organik (Sisa Produksi)','icon'=>'leaf','sub'=>'Catat limbah organik sisa produksi dan tujuannya','fields'=>[
            ['tanggal','Tanggal','date','req'],['jenis','Jenis Limbah','select:jenis_organik','req'],['berat','Berat (kg)','number','req'],
            ['tujuan','Tujuan','select:tujuan_organik'],['dokumen','No. Dokumen','text'],['keterangan','Keterangan','text'],
        ]],
        'domestik' => ['table'=>'env_domestik','back'=>'nonb3','title'=>'B. Limbah Domestik (Sampah Kantor/Rumah Tangga)','icon'=>'leaf','sub'=>'Catat sampah kantor / rumah tangga yang diangkut','fields'=>[
            ['tanggal','Tanggal','date','req'],['jenis','Jenis Sampah','select:jenis_domestik','req'],['volume','Volume (m³)','number','req'],
            ['pengangkut','Pengangkut','text'],['dokumen','No. Dokumen','text'],['keterangan','Keterangan','text'],
        ]],
    ],
];
