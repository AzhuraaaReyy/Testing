<?php

/*
| Teks halaman error HTTP (resources/views/errors). Kode tanpa entri memakai '4xx' / '5xx'.
*/

return [
    '401' => ['judul' => 'Perlu login', 'pesan' => 'Silakan login untuk mengakses halaman ini.'],
    '402' => ['judul' => 'Pembayaran diperlukan', 'pesan' => 'Akses ke halaman ini memerlukan pembayaran.'],
    '403' => ['judul' => 'Akses ditolak', 'pesan' => 'Anda tidak memiliki akses ke halaman ini.'],
    '404' => ['judul' => 'Halaman tidak ditemukan', 'pesan' => 'Halaman yang Anda cari tidak ada atau telah dipindahkan.'],
    '405' => ['judul' => 'Permintaan tidak diizinkan', 'pesan' => 'Cara mengakses halaman ini tidak diizinkan.'],
    '419' => ['judul' => 'Halaman kedaluwarsa', 'pesan' => 'Sesi halaman telah berakhir. Muat ulang halaman lalu coba lagi.'],
    '429' => ['judul' => 'Terlalu banyak permintaan', 'pesan' => 'Silakan tunggu sebentar lalu coba lagi.'],
    '500' => ['judul' => 'Terjadi kesalahan', 'pesan' => 'Terjadi kesalahan pada server. Silakan coba lagi beberapa saat lagi.'],
    '503' => ['judul' => 'Sedang dalam pemeliharaan', 'pesan' => 'Sistem sedang dalam pemeliharaan. Silakan coba lagi nanti.'],
    '4xx' => ['judul' => 'Permintaan tidak dapat diproses', 'pesan' => 'Periksa kembali alamat halaman lalu coba lagi.'],
    '5xx' => ['judul' => 'Terjadi kesalahan', 'pesan' => 'Terjadi kesalahan pada server. Silakan coba lagi beberapa saat lagi.'],
    'kembali' => 'Kembali ke beranda',
    'login' => 'Ke halaman login',
];
