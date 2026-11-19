<?php

use App\Models\User;

test('beranda mengarahkan tamu ke login portal dan pengguna ke berandanya', function () {
    $this->get('/')->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())->get('/')->assertRedirect(route('filament.admin.pages.dashboard'));

    $this->actingAs(User::factory()->siswa()->create())->get('/')->assertRedirect(route('portal.dashboard'));
});
