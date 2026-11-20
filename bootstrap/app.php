<?php

use App\Exceptions\ApiExceptionRenderer;
use App\Http\PartialResponse;
use App\Support\SesiLogin;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Batas permintaan API: limiter "api" di AppServiceProvider (429 jika terlampaui).
        $middleware->throttleApi();

        // Dua pintu login: Admin di panel Filament, Guru/Siswa di portal Blade (/login).
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('portal', 'portal/*')
            ? route('login')
            : route('filament.admin.auth.login'));

        // Pengguna yang sudah login dan membuka /login diarahkan ke beranda perannya.
        $middleware->redirectUsersTo(fn (Request $request) => route($request->user()?->role->homeRoute() ?? 'login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API: error JSON {message} berbahasa Indonesia (401/403/404/405/409/429/500).
        $exceptions->render(new ApiExceptionRenderer);

        // Tamu ditolak di halaman terlindungi (portal maupun panel Admin): bila sebelumnya pernah
        // login, beri tahu bahwa session berakhir (PRD 4.6 poin 3). null = redirect bawaan tetap berjalan.
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            SesiLogin::umumkanJikaBerakhir($request);

            return null;
        });

        // Permintaan parsial (fetch fragmen) menerima error dalam format JSON standar Laravel
        // agar frontend dapat menanganinya seragam: 422 {message, errors}, 500 {message}.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
                || $request->expectsJson()
                || PartialResponse::isPartial($request),
        );
    })->create();
