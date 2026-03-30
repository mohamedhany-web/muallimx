@props(['type' => 'info'])

@php
    $map = [
        'success' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-900'],
        'error' => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'text' => 'text-rose-900'],
        'warning' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-900'],
        'info' => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'text' => 'text-slate-900'],
    ];
    $c = $map[$type] ?? $map['info'];
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl {$c['bg']} border {$c['border']} {$c['text']} px-4 py-3 text-sm"]) }}>
    {{ $slot }}
</div>
