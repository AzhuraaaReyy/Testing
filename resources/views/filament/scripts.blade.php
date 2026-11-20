{{--
    Disisipkan di akhir <body> panel Admin (render hook BODY_END).
    Template error tabel = komponen yang sama dengan portal (PRD 16.3), dikloning oleh
    resources/js/components/livewire-table-error.ts.
--}}
<template data-table-error-template>
    <x-molecules.table-error />
</template>

@vite('resources/js/filament/admin.ts')
