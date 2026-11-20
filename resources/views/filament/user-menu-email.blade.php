{{-- Email Admin di bawah nama pada dropdown akun (PRD 17.1). Disisipkan lewat render hook USER_MENU_PROFILE_AFTER. --}}
<p class="truncate px-3 pb-2 text-sm text-ink-muted">{{ filament()->auth()->user()?->email }}</p>
