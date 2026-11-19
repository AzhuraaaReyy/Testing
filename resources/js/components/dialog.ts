import { closestFromEvent } from '../lib/dom';

/**
 * Dialog konfirmasi berbasis <dialog> native: fokus terkunci dan Escape ditangani browser.
 * Pembuka: [data-dialog-open="<id>"]. Penutup di dalam dialog: [data-dialog-close].
 */

function isOutside(dialog: HTMLDialogElement, event: MouseEvent): boolean {
    const box = dialog.getBoundingClientRect();
    return event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom;
}

export function initDialogs(): void {
    document.addEventListener('click', (event) => {
        const opener = closestFromEvent(event, '[data-dialog-open]', HTMLElement);
        if (opener) {
            const dialog = document.getElementById(opener.dataset['dialogOpen'] ?? '');
            if (dialog instanceof HTMLDialogElement) dialog.showModal();
            return;
        }

        const closer = closestFromEvent(event, '[data-dialog-close]', HTMLElement);
        if (closer) {
            closer.closest('dialog')?.close();
            return;
        }

        // Klik pada latar (di luar kotak dialog) menutup dialog.
        if (event.target instanceof HTMLDialogElement && event.target.open && isOutside(event.target, event)) {
            event.target.close();
        }
    });
}
