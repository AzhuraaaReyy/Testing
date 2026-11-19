/**
 * Kontrak data Frontend ↔ Backend.
 *
 * Setiap tipe di sini adalah cermin presisi dari kelas PHP yang disebut pada komentarnya.
 * Kesesuaian nama field & nilai enum diverifikasi oleh tests/Unit/ContractSyncTest.php,
 * jadi perubahan di salah satu sisi tanpa sisi lainnya akan menggagalkan test.
 */

/** Cermin App\Enums\JenisLaporan (nilai = segmen URL /laporan/{jenis}). */
export const JENIS_LAPORAN = ['siswa-per-kelas', 'guru-per-kelas', 'siswa-kelas-guru'] as const;
export type JenisLaporan = (typeof JENIS_LAPORAN)[number];

/** Cermin App\Enums\Role. */
export const ROLE = ['admin', 'guru', 'siswa'] as const;
export type Role = (typeof ROLE)[number];

/** Cermin rules() App\Http\Requests\Auth\PortalLoginRequest (POST /login). */
export interface PortalLoginPayload {
    /** required, format email. */
    email: string;
    /** required. */
    password: string;
}

/** Cermin messages() App\Http\Requests\Auth\PortalLoginRequest — dipakai validasi sisi klien. */
export const PORTAL_LOGIN_MESSAGES = {
    'email.required': 'Email wajib diisi.',
    'email.email': 'Format email tidak valid.',
    'password.required': 'Password wajib diisi.',
} as const;

/** Cermin App\DTOs\Dashboard\RingkasanSekolahData. */
export interface RingkasanSekolahData {
    totalSiswa: number;
    totalKelas: number;
    totalGuru: number;
}

/** Cermin App\Enums\Tingkat. */
export const TINGKAT = ['X', 'XI', 'XII'] as const;
export type Tingkat = (typeof TINGKAT)[number];

/** Cermin App\DTOs\Laporan\KelasRingkasanData (satu baris laporan = satu Kelas aktif). */
export interface KelasRingkasanData {
    id: number;
    namaKelas: string;
    tingkat: Tingkat;
    jurusan: string | null;
    /** Nama Siswa aktif, terurut A–Z. */
    siswa: string[];
    /** Nama Guru aktif, terurut A–Z. */
    guru: string[];
}

/** Cermin rules() App\Http\Requests\LaporanRequest (query string GET /laporan/{jenis}). */
export interface LaporanQuery {
    /** integer, min 1. Opsional; default 1. */
    page?: number;
}

/**
 * Cermin App\Http\PartialResponse. Nilainya dibaca dari atribut data-* yang dirender server
 * (data-fragment-header, data-fragment-name), sehingga tidak ada string yang diduplikasi di sini.
 */
export interface FragmentRequest {
    header: string;
    name: string;
}

/** Format error standar Laravel untuk HTTP 4xx/5xx (JSON). */
export interface LaravelErrorResponse {
    message: string;
}

/** Format error validasi standar Laravel (HTTP 422). */
export interface LaravelValidationErrorResponse extends LaravelErrorResponse {
    errors: Record<string, string[]>;
}
