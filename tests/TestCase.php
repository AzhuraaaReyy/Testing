<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Sleep;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Jeda Timebox login (SEC-02) dicatat tanpa benar-benar menunggu agar suite tetap cepat.
        Sleep::fake();

        // Daftar host tepercaya (SEC-06) disimpan statis oleh Symfony; jangan terbawa antar-test.
        Request::setTrustedHosts([]);
    }
}
