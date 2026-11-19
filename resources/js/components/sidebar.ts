import { closestFromEvent, query } from '../lib/dom';
import { writePreference } from '../lib/storage';

/**
 * Sidebar desktop (ciut/lebar, diingat per-browser) dan drawer mobile (PRD 17.2–17.3).
 * State ditulis ke <html data-sidebar|data-drawer>; tampilan diatur di css/components/shell.css.
 * Nilai awal data-sidebar dipulihkan oleh skrip inline di layouts/app.blade.php (cegah kedip).
 */

type SidebarState = 'expanded' | 'collapsed';
type DrawerState = 'open' | 'closed';

export const SIDEBAR_STORAGE_KEY = 'sidebar';

const root = document.documentElement;

const sidebarState = (): SidebarState => (root.dataset['sidebar'] === 'collapsed' ? 'collapsed' : 'expanded');

function setDrawer(state: DrawerState): void {
    root.dataset['drawer'] = state;
}

function initCollapseToggle(toggle: HTMLButtonElement): void {
    const sync = (): void => {
        const collapsed = sidebarState() === 'collapsed';
        toggle.setAttribute('aria-expanded', String(!collapsed));
        toggle.setAttribute('aria-label', collapsed ? 'Lebarkan sidebar' : 'Ciutkan sidebar');
    };

    sync();
    toggle.addEventListener('click', () => {
        const next: SidebarState = sidebarState() === 'collapsed' ? 'expanded' : 'collapsed';
        root.dataset['sidebar'] = next;
        writePreference(SIDEBAR_STORAGE_KEY, next);
        sync();
    });
}

function initDrawer(sidebar: HTMLElement, opener: HTMLButtonElement, closer: HTMLButtonElement, backdrop: HTMLElement): void {
    const isOpen = (): boolean => root.dataset['drawer'] === 'open';

    const open = (): void => {
        setDrawer('open');
        backdrop.hidden = false;
        opener.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        closer.focus();
    };

    const close = (): void => {
        if (!isOpen()) return;
        setDrawer('closed');
        backdrop.hidden = true;
        opener.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        opener.focus();
    };

    opener.addEventListener('click', open);
    closer.addEventListener('click', close);
    backdrop.addEventListener('click', close);

    // Memilih menu juga menutup drawer (PRD 17.3).
    sidebar.addEventListener('click', (event) => {
        if (closestFromEvent(event, '[data-nav-link]', HTMLAnchorElement)) close();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });

    // Drawer tidak relevan di desktop: tutup bila layar melebar saat terbuka.
    window.matchMedia('(width >= 64rem)').addEventListener('change', (event) => {
        if (event.matches) close();
    });
}

export function initSidebar(): void {
    const toggle = query(document, '[data-sidebar-toggle]', HTMLButtonElement);
    if (toggle) initCollapseToggle(toggle);

    const sidebar = query(document, '#sidebar', HTMLElement);
    const opener = query(document, '[data-drawer-open]', HTMLButtonElement);
    const closer = query(document, '[data-drawer-close]', HTMLButtonElement);
    const backdrop = query(document, '[data-drawer-backdrop]', HTMLElement);

    if (sidebar && opener && closer && backdrop) initDrawer(sidebar, opener, closer, backdrop);
}
