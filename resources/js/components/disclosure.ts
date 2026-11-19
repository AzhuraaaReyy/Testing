import { closestFromEvent, query, queryAll } from '../lib/dom';

/**
 * Dropdown sederhana: [data-disclosure] berisi [data-disclosure-trigger] dan [data-disclosure-panel].
 * Tertutup saat klik di luar, tombol Escape, atau saat item di dalamnya membuka dialog.
 */

function bind(container: HTMLElement, trigger: HTMLButtonElement, panel: HTMLElement): void {
    const setOpen = (open: boolean): void => {
        panel.hidden = !open;
        trigger.setAttribute('aria-expanded', String(open));
    };

    trigger.addEventListener('click', () => setOpen(panel.hidden));

    document.addEventListener('click', (event) => {
        if (!panel.hidden && event.target instanceof Node && !container.contains(event.target)) setOpen(false);
    });

    container.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) {
            setOpen(false);
            trigger.focus();
        }
    });

    panel.addEventListener('click', (event) => {
        if (closestFromEvent(event, '[data-dialog-open]', HTMLElement)) setOpen(false);
    });
}

export function initDisclosures(): void {
    for (const container of queryAll(document, '[data-disclosure]', HTMLElement)) {
        const trigger = query(container, '[data-disclosure-trigger]', HTMLButtonElement);
        const panel = query(container, '[data-disclosure-panel]', HTMLElement);

        if (trigger && panel) bind(container, trigger, panel);
    }
}
