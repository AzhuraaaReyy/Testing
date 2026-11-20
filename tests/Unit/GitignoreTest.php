<?php

/*
| File rahasia/lokal tidak boleh bisa ter-commit, sedangkan file yang dibutuhkan proyek tetap ikut.
| Memakai `git check-ignore` agar hasilnya sama persis dengan perilaku Git.
*/

function diabaikanGit(string $path): bool
{
    exec('git -C '.escapeshellarg(dirname(__DIR__, 2)).' check-ignore -q --no-index '.escapeshellarg($path), result_code: $kode);

    return $kode === 0;
}

beforeEach(function () {
    exec('git -C '.escapeshellarg(dirname(__DIR__, 2)).' rev-parse --is-inside-work-tree', result_code: $kode);

    if ($kode !== 0) {
        $this->markTestSkipped('Bukan repository Git.');
    }
});

it('mengabaikan file rahasia dan file lokal', function (string $path) {
    expect(diabaikanGit($path))->toBeTrue("{$path} seharusnya diabaikan Git");
})->with([
    '.env', '.env.local', '.env.testing', '.env.staging', '.env.production', '.env.backup',
    'auth.json', 'storage/oauth-private.key', 'server.pem', 'sertifikat.p12',
    'backup.sql', 'dump.sql.gz', 'database/database.sqlite',
    '.claude/settings.local.json', '.mcp.json', 'boost.json', 'CLAUDE.md', 'AGENTS.md',
    '.codebase-memory/graph.db.zst', 'docs/PRD.md', 'lokal.postman_environment.json',
    'storage/logs/laravel.log', 'coverage/index.html', 'node_modules/x/index.js', 'vendor/autoload.php',
]);

it('tetap menyertakan file yang dibutuhkan proyek', function (string $path) {
    expect(diabaikanGit($path))->toBeFalse("{$path} seharusnya ikut di-commit");
})->with([
    '.env.example', 'docs/api/openapi.yaml', 'composer.lock', 'package-lock.json',
    '.github/workflows/ci.yml', 'storage/logs/.gitignore', 'app/Http/Middleware/SecurityHeaders.php',
]);
