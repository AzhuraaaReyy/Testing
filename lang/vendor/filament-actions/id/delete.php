<?php

// Override terjemahan Filament (digabung dengan bawaan): hapus = pindah ke Trash (PRD 13, 14.3).
return [
    'single' => [
        'label' => 'Hapus',
        'modal' => [
            'heading' => 'Hapus :label',
            'actions' => [
                'delete' => ['label' => 'Ya, Hapus'],
            ],
        ],
        'notifications' => [
            'deleted' => ['title' => 'Data berhasil dipindahkan ke Trash.'],
        ],
    ],
];
