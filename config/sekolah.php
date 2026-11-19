<?php

return [

    /*
    | Identitas yang ditampilkan di Navbar (PRD 17.1). Identitas sekolah hanya muncul di Navbar.
    */

    'nama_sistem' => env('APP_NAME', 'Sistem Manajemen Sekolah'),

    'nama_sekolah' => env('SEKOLAH_NAMA'),

    // Path relatif terhadap folder public, mis. "images/logo.png". Kosong = placeholder.
    'logo' => env('SEKOLAH_LOGO'),

];
