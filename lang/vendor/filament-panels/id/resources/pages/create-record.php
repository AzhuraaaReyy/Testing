<?php

// Override terjemahan Filament (digabung dengan bawaan): halaman Tambah (PRD 5.4, 14.6).
return [
    'title' => 'Tambah :label',
    'breadcrumb' => 'Tambah',
    'form' => [
        'actions' => [
            'create' => ['label' => 'Simpan'],
            'create_another' => ['label' => 'Simpan & tambah lainnya'],
        ],
    ],
    'notifications' => [
        'created' => ['title' => 'Data berhasil ditambahkan.'],
    ],
];
