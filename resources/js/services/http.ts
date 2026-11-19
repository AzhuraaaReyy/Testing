import type { FragmentRequest, LaravelErrorResponse, LaravelValidationErrorResponse } from '../types/contracts';

/**
 * Lapisan komunikasi ke server. Komponen UI tidak memanggil fetch langsung,
 * melainkan memakai fungsi di sini yang menormalkan seluruh kegagalan menjadi HttpError.
 */

export type HttpErrorKind = 'validation' | 'session-expired' | 'server' | 'network';

export class HttpError extends Error {
    constructor(
        readonly kind: HttpErrorKind,
        readonly status: number | null,
        message: string,
        readonly errors: Record<string, string[]> = {},
    ) {
        super(message);
        this.name = 'HttpError';
    }

    /** Pesan validasi pertama (422), jika ada. */
    firstValidationMessage(): string | null {
        for (const messages of Object.values(this.errors)) {
            if (messages[0]) return messages[0];
        }
        return null;
    }

    /** Membentuk HttpError dari respons gagal berformat JSON standar Laravel. */
    static async fromResponse(response: Response): Promise<HttpError> {
        const body = await readJson(response);
        const message = isErrorResponse(body) ? body.message : response.statusText;

        if (response.status === 422) {
            return new HttpError('validation', 422, message, isValidationResponse(body) ? body.errors : {});
        }

        // 401 = belum/tidak lagi login, 419 = token CSRF/session kedaluwarsa.
        if (response.status === 401 || response.status === 419) {
            return new HttpError('session-expired', response.status, message);
        }

        return new HttpError('server', response.status, message);
    }
}

export type FragmentResult =
    | { kind: 'fragment'; html: string; url: string }
    /** Server mengarahkan ke path lain (mis. Login setelah session berakhir): navigasi penuh. */
    | { kind: 'navigate'; url: string };

/**
 * GET satu fragmen Blade (kontrak App\Http\PartialResponse).
 * Melempar HttpError untuk respons gagal maupun kegagalan jaringan; AbortError diteruskan apa adanya.
 */
export async function fetchFragment(url: string, fragment: FragmentRequest, signal: AbortSignal): Promise<FragmentResult> {
    let response: Response;

    try {
        response = await fetch(url, {
            headers: {
                [fragment.header]: fragment.name,
                Accept: 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            signal,
        });
    } catch (error) {
        if (signal.aborted) throw error;
        throw new HttpError('network', null, 'Tidak dapat terhubung ke server.');
    }

    if (response.redirected && new URL(response.url).pathname !== new URL(url, window.location.href).pathname) {
        return { kind: 'navigate', url: response.url };
    }

    if (!response.ok) throw await HttpError.fromResponse(response);

    return { kind: 'fragment', html: await response.text(), url: response.url };
}

async function readJson(response: Response): Promise<unknown> {
    if (!response.headers.get('Content-Type')?.includes('application/json')) return null;

    try {
        return await response.json();
    } catch {
        return null;
    }
}

function isErrorResponse(body: unknown): body is LaravelErrorResponse {
    return typeof body === 'object' && body !== null && typeof (body as LaravelErrorResponse).message === 'string';
}

function isValidationResponse(body: unknown): body is LaravelValidationErrorResponse {
    return isErrorResponse(body) && typeof (body as LaravelValidationErrorResponse).errors === 'object';
}
