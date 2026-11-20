<?php

/*
| Pesan validasi Bahasa Indonesia (APP_LOCALE=id). Hanya aturan yang dipakai aplikasi;
| aturan lain jatuh ke bahasa fallback. :attribute = label field form.
*/

return [
    'email' => 'Format :attribute tidak valid.',
    'in' => ':attribute yang dipilih tidak valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'max' => [
        'string' => ':attribute maksimal :max karakter.',
    ],
    'min' => [
        'string' => ':attribute minimal :min karakter.',
    ],
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':attribute wajib diisi.',
    'unique' => ':attribute sudah digunakan.',
];
