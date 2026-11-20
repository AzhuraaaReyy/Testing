<?php

use App\Models\User;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Sanctum\PersonalAccessToken;

/*
| SEC-07: token API kedaluwarsa dibersihkan otomatis setiap hari.
*/

it('menjadwalkan pembersihan token kedaluwarsa setiap hari', function () {
    $jadwal = collect(app(Schedule::class)->events())
        ->filter(fn (Event $event): bool => str_contains((string) $event->command, 'sanctum:prune-expired'));

    expect($jadwal)->toHaveCount(1)
        ->and($jadwal->first()->expression)->toBe('0 0 * * *')
        ->and($jadwal->first()->command)->toContain('--hours=24');
});

it('menghapus token yang sudah kedaluwarsa dan mempertahankan token aktif', function () {
    $user = User::factory()->siswa()->create();
    $user->createToken('api');

    // Lewat masa berlaku token (sanctum.expiration menit) ditambah 24 jam.
    $this->travel((int) config('sanctum.expiration') + 24 * 60 + 1)->minutes();
    $aktif = $user->createToken('api')->accessToken;

    $this->artisan('sanctum:prune-expired', ['--hours' => 24])->assertSuccessful();

    expect(PersonalAccessToken::query()->pluck('id')->all())->toBe([$aktif->id]);
});
