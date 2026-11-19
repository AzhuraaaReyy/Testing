/**
 * Akses localStorage yang aman: mode privat atau data situs yang diblokir dapat membuat
 * localStorage melempar error. Kegagalan diabaikan karena data ini hanya preferensi tampilan.
 * (Pembacaan state Sidebar dilakukan skrip inline di layouts/app.blade.php sebelum render.)
 */
export function writePreference(key: string, value: string): void {
    try {
        window.localStorage.setItem(key, value);
    } catch {
        // Preferensi tetap berlaku untuk halaman ini saja.
    }
}
