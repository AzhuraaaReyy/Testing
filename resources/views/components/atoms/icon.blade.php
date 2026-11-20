@props(['icon'])

{{--
    Ikon dekoratif (Heroicons via App\Enums\Icon): tersembunyi dari screen reader.
    Beri label pada elemen induknya. @var \App\Enums\Icon $icon
--}}
{{ svg($icon->svgName(), '', $attributes->class(['size-5 shrink-0'])->merge(['aria-hidden' => 'true', 'focusable' => 'false'])->getAttributes()) }}
