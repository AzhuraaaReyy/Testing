<?php

// Override terjemahan Filament (digabung dengan bawaan): istilah PRD 14.2 "Tambah".
return [
    'single' => [
        'label' => 'Tambah :label',
        'modal' => [
            'heading' => 'Tambah :label',
            'actions' => [
                'create' => ['label' => 'Simpan'],
                'create_another' => ['label' => 'Simpan & tambah lainnya'],
            ],
        ],
        'notifications' => [
            'created' => ['title' => 'Data berhasil ditambahkan.'],
        ],
    ],
];
