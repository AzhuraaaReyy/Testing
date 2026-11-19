import { HttpError, fetchFragment } from '../services/http';
import type { FragmentRequest } from '../types/contracts';
import { closestFromEvent, query, queryAll } from '../lib/dom';

/**
 * Pagination tanpa reload penuh untuk [data-table-region] (PRD 16.1–16.3).
 * Komponen ini hanya mengurus DOM & state; komunikasi server ada di services/http.ts.
 * Tanpa JavaScript, tautan pagination tetap berfungsi sebagai navigasi biasa.
 */

type RegionState = 'ready' | 'loading' | 'error';

interface Region {
    root: HTMLElement;
    content: HTMLElement;
    status: HTMLElement | null;
    errorTemplate: HTMLTemplateElement;
    fragment: FragmentRequest;
}

/** Indikator loading baru tampil jika request lebih lama dari ini, agar tidak berkedip. */
const LOADING_DELAY_MS = 150;

function resolve(root: HTMLElement): Region | null {
    const content = query(root, '[data-region-content]', HTMLElement);
    const errorTemplate = query(root, 'template[data-region-error]', HTMLTemplateElement);
    const header = root.dataset['fragmentHeader'];
    const name = root.dataset['fragmentName'];

    if (!content || !errorTemplate || !header || !name) return null;

    return {
        root,
        content,
        status: query(root, '[data-region-status]', HTMLElement),
        errorTemplate,
        fragment: { header, name },
    };
}

function setState(region: Region, state: RegionState): void {
    region.root.dataset['state'] = state;
}

function announce(region: Region, message: string): void {
    if (region.status) region.status.textContent = message;
}

/** Error di area tabel saja; data lama diganti pesan agar tidak dikira valid (PRD 16.3). */
function showError(region: Region, error: HttpError, retry: () => void): void {
    setState(region, 'error');
    region.content.replaceChildren(region.errorTemplate.content.cloneNode(true));

    // 422: tampilkan pesan validasi dari server; selain itu pakai pesan default PRD di template.
    const validationMessage = error.kind === 'validation' ? error.firstValidationMessage() : null;
    const messageElement = query(region.content, '[data-error-message]', HTMLElement);
    if (validationMessage && messageElement) messageElement.textContent = validationMessage;

    query(region.content, '[data-region-retry]', HTMLButtonElement)?.addEventListener('click', retry, { once: true });
}

function bind(region: Region): void {
    let inFlight: AbortController | null = null;

    async function load(url: string, push: boolean): Promise<void> {
        inFlight?.abort();
        const controller = new AbortController();
        inFlight = controller;

        const loadingTimer = window.setTimeout(() => setState(region, 'loading'), LOADING_DELAY_MS);
        region.root.setAttribute('aria-busy', 'true');

        try {
            const result = await fetchFragment(url, region.fragment, controller.signal);

            if (result.kind === 'navigate') {
                window.location.assign(result.url);
                return;
            }

            region.content.innerHTML = result.html;
            setState(region, 'ready');
            if (push) window.history.pushState({ tableRegion: true }, '', result.url);

            announce(region, 'Data tabel diperbarui.');
            region.root.focus({ preventScroll: true });
            if (region.root.getBoundingClientRect().top < 0) region.root.scrollIntoView({ block: 'start' });
        } catch (error) {
            if (controller.signal.aborted) return;

            if (error instanceof HttpError && error.kind === 'session-expired') {
                window.location.reload();
                return;
            }

            const httpError = error instanceof HttpError ? error : new HttpError('server', null, String(error));
            showError(region, httpError, () => void load(url, push));
        } finally {
            window.clearTimeout(loadingTimer);
            if (inFlight === controller) {
                inFlight = null;
                region.root.removeAttribute('aria-busy');
            }
        }
    }

    region.root.addEventListener('click', (event) => {
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const link = closestFromEvent(event, 'a[data-page-link]', HTMLAnchorElement);
        if (!link) return;

        event.preventDefault();
        void load(link.href, true);
    });

    window.addEventListener('popstate', () => void load(window.location.href, false));
}

export function initTableRegions(): void {
    for (const root of queryAll(document, '[data-table-region]', HTMLElement)) {
        const region = resolve(root);
        if (region) bind(region);
    }
}
