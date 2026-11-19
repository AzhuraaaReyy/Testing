<?php

use App\Http\PartialResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Dua pintu login: Admin di panel Filament, Guru/Siswa di portal Blade (/login).
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('portal', 'portal/*')
            ? route('login')
            : route('filament.admin.auth.login'));

        // Pengguna yang sudah login dan membuka /login diarahkan ke beranda perannya.
        $middleware->redirectUsersTo(fn (Request $request) => route($request->user()->role->homeRoute()));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Permintaan parsial (fetch fragmen) menerima error dalam format JSON standar Laravel
        // agar frontend dapat menanganinya seragam: 422 {message, errors}, 500 {message}.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
                || $request->expectsJson()
                || PartialResponse::isPartial($request),
        );
    })->create();
