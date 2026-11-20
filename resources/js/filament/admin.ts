import { initLivewireTableErrors } from '../components/livewire-table-error';

/**
 * Entry JavaScript panel Admin (Filament), dimuat lewat render hook BODY_END.
 */
document.addEventListener('livewire:init', () => {
    const livewire = window.Livewire;
    if (!livewire) return;

    // Session/CSRF kedaluwarsa (419) atau tidak lagi login (401): muat ulang halaman, server lalu
    // mengarahkan ke Login dengan pesan "Sesi Anda telah berakhir" (PRD 4.6). Menggantikan dialog
    // bawaan Livewire yang berbahasa Inggris.
    livewire.interceptRequest(({ onError }) => {
        onError(({ response, preventDefault }) => {
            if (response.status === 401 || response.status === 419) {
                preventDefault();
                window.location.reload();
            }
        });
    });

    initLivewireTableErrors(livewire);
});
