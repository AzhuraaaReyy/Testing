@props([
    'colspan',
    'message',
])

{{-- Empty state: header tabel tetap terlihat, pesan di dalam badan tabel (PRD 8.5, 14.9). --}}
<tr>
    <td colspan="{{ $colspan }}" class="px-5 py-14 text-center text-base text-ink-muted">
        {{ $message }}
    </td>
</tr>
