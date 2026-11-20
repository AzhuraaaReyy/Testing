import { query } from '../lib/dom';
import type { LivewireGlobal, MessageInterceptor } from '../types/livewire';

/**
 * Error state tabel untuk halaman Livewire/Filament (PRD 16.3), padanan table-region.ts di portal.
 *
 * Jika permintaan MEMUAT data tabel gagal (server 5xx atau jaringan putus), area tabel diganti
 * pesan "Data gagal dimuat. Silakan coba lagi." + tombol Coba Lagi; judul tabel & halaman tetap.
 * Markup diambil dari <template data-table-error-template> (komponen Blade molecules/table-error),
 * jadi tampilannya identik dengan portal. Morph Livewire berikutnya membersihkan state ini.
 */

/** Aksi yang berarti "memuat data tabel"; kegagalan aksi lain (simpan, hapus) tidak ditampilkan sebagai error tabel. */
const LOAD_ACTIONS = new Set(['gotoPage', 'previousPage', 'nextPage', 'setPage', '$refresh', '$set', 'resetTable']);

/** Area tabel: tabel Filament, atau tabel Blade di halaman Livewire ([data-table-area]). */
const TABLE_AREA = '[data-table-area], .fi-ta-content-ctn';

function isLoadMessage(message: MessageInterceptor['message']): boolean {
    // Tanpa aksi = hanya pembaruan properti (mis. pindah tab Aktif/Trash).
    return [...message.actions].every((action) => LOAD_ACTIONS.has(action.name));
}

function showError(root: HTMLElement, retry: () => void): void {
    const area = query(root, TABLE_AREA, HTMLElement);
    const template = query(document, 'template[data-table-error-template]', HTMLTemplateElement);
    if (!area || !template) return;

    query(root, '[data-table-error]', HTMLElement)?.remove();

    const error = document.createElement('div');
    error.dataset['tableError'] = '';
    error.append(template.content.cloneNode(true));
    area.before(error);

    // CSS tema menyembunyikan area tabel & pagination selama state error.
    root.dataset['tableState'] = 'error';

    query(error, '[data-region-retry]', HTMLButtonElement)?.addEventListener('click', retry, { once: true });
}

export function initLivewireTableErrors(livewire: LivewireGlobal): void {
    livewire.interceptMessage(({ message, onError, onFailure }) => {
        const root = message.component.el;
        if (!root.querySelector(TABLE_AREA) || !isLoadMessage(message)) return;

        const retry = (): void => void message.component.$wire.$refresh();

        onError(({ response, preventDefault }) => {
            // 401/419 ditangani sebagai session berakhir (lihat filament/admin.ts).
            if (response.status < 500) return;

            preventDefault();
            showError(root, retry);
        });

        onFailure(() => showError(root, retry));
    });
}
