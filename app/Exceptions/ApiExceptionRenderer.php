<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Error API dalam format JSON standar Laravel {message} dengan pesan Bahasa Indonesia
 * (lang/id/kesalahan.php → api). Validasi (422) tidak diubah: pesannya sudah dari FormRequest.
 * Pesan eksplisit dari aturan bisnis (mis. abort(409, 'Kelas masih memiliki Siswa aktif.')) dipertahankan.
 */
final class ApiExceptionRenderer
{
    public function __invoke(Throwable $exception, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*') || $exception instanceof ValidationException) {
            return null;
        }

        [$status, $pesan] = match (true) {
            $exception instanceof AuthenticationException => [401, __('kesalahan.api.401')],
            $exception instanceof AuthorizationException => [403, __('kesalahan.api.403')],
            $exception instanceof ModelNotFoundException,
            $exception->getPrevious() instanceof ModelNotFoundException => [404, __('kesalahan.api.404_data')],
            $exception instanceof HttpExceptionInterface => [$exception->getStatusCode(), $this->pesanHttp($exception)],
            default => [500, null],
        };

        // Exception tak terduga: biarkan Laravel menampilkan detail saat debug.
        if ($pesan === null) {
            if (config('app.debug')) {
                return null;
            }

            $pesan = __('kesalahan.api.500');
        }

        return response()->json(['message' => $pesan], $status);
    }

    private function pesanHttp(HttpExceptionInterface $exception): string
    {
        $status = $exception->getStatusCode();
        $kunci = "kesalahan.api.{$status}";

        // Pesan eksplisit dari kode aplikasi (abort dengan pesan) — kecuali pesan bawaan framework
        // berbahasa Inggris untuk kode yang punya terjemahan.
        if ($exception->getMessage() !== '' && ! trans()->has($kunci)) {
            return $exception->getMessage();
        }

        return trans()->has($kunci) ? __($kunci) : __('kesalahan.api.'.($status >= 500 ? '5xx' : '4xx'));
    }
}
