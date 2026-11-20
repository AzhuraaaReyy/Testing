<?php

// Override terjemahan Filament (digabung dengan bawaan): pemulihan dari Trash (PRD 13.2, 14.4).
return [
    'single' => [
        'label' => 'Pulihkan',
        'modal' => [
            'heading' => 'Pulihkan :label',
            'actions' => [
                'restore' => ['label' => 'Ya, Pulihkan'],
            ],
        ],
        'notifications' => [
            'restored' => ['title' => 'Data berhasil dipulihkan.'],
        ],
    ],
];
