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

/** Cermin rules() App\Http\Requests\Admin\BuatAdminRequest (perintah `php artisan sekolah:buat-admin`). */
export interface BuatAdminPayload {
    /** required, maks. 255. */
    name: string;
    /** required, format email, unik di users. */
    email: string;
    /** required, min. 8, wajib dikonfirmasi (password_confirmation). */
    password: string;
}

/** Cermin rules() App\Http\Requests\DataMaster\KelasRequest (form Kelas, panel Admin). */
export interface KelasPayload {
    /** required, maks. 100, unik termasuk data di Trash. */
    nama_kelas: string;
    tingkat: Tingkat;
    /** opsional, maks. 100. */
    jurusan?: string | null;
    keterangan?: string | null;
}

/** Cermin rules() App\Http\Requests\DataMaster\SiswaRequest (form Siswa, panel Admin). */
export interface SiswaPayload {
    /** required, teks (nol di awal terjaga), maks. 30, unik termasuk data di Trash. */
    nis: string;
    nama: string;
    jenis_kelamin: JenisKelamin;
    alamat: string;
    /** id Kelas aktif. */
    kelas_id: number;
}

/** Cermin rules() App\Http\Requests\DataMaster\GuruRequest (form Guru, panel Admin). */
export interface GuruPayload {
    /** opsional, unik bila diisi (termasuk data di Trash). */
    nip?: string | null;
    nama: string;
    jenis_kelamin: JenisKelamin;
    alamat: string;
    /** format 08… atau +628…. */
    no_telepon: string;
    /** minimal satu id Kelas aktif. */
    kelas: number[];
}

/** Cermin rules() App\Http\Requests\DataMaster\AkunPortalRequest (bagian Akun Portal form Siswa/Guru). */
export interface AkunPortalPayload {
    /** opsional; kosong = tanpa akun. */
    email?: string | null;
    /** min. 8; wajib saat akun baru dibuat. */
    password?: string | null;
}

/** Cermin App\DTOs\Dashboard\RingkasanSekolahData. */
export interface RingkasanSekolahData {
    totalSiswa: number;
    totalKelas: number;
    totalGuru: number;
}

/** Cermin App\Enums\Tingkat. */
export const TINGKAT = ['X', 'XI', 'XII'] as const;
export type Tingkat = (typeof TINGKAT)[number];

/** Cermin App\Enums\JenisKelamin (L = Laki-laki, P = Perempuan). */
export const JENIS_KELAMIN = ['L', 'P'] as const;
export type JenisKelamin = (typeof JENIS_KELAMIN)[number];

/** Cermin App\Enums\Icon (nama ikon semantik; SVG Heroicons dirender server). */
export const ICON = [
    'layout-dashboard',
    'users',
    'building',
    'presentation',
    'clipboard-list',
    'square-user',
    'table',
    'panel-left',
    'menu',
    'x',
    'log-out',
    'chevron-down',
    'chevron-left',
    'chevron-right',
    'triangle-alert',
    'info',
    'rotate-cw',
    'eye',
    'eye-off',
    'circle-check',
    'loader-circle',
] as const;
export type Icon = (typeof ICON)[number];

/** Cermin App\DTOs\Navigation\NavItemData (item menu Sidebar portal yang sudah siap dirender). */
export interface NavItemData {
    label: string;
    icon: Icon;
    url: string;
    active: boolean;
}

/** Cermin App\DTOs\Navigation\NavGroupData. */
export interface NavGroupData {
    /** null = grup tanpa judul (mis. Beranda). */
    label: string | null;
    items: NavItemData[];
}

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
