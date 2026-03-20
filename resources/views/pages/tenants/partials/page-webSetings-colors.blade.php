{{-- Estilos para el manejo de colores del sitio web --}}
:root {
    @if ($colors['primary'] ?? null)
        --color-primary: {{ $colors['primary'] }};
        --color-primary-hover: color-mix(in srgb, var(--color-primary), black 10%);
        --color-primary-soft: color-mix(in srgb, var(--color-primary), white 80%);
    @endif
    @if ($colors['secondary'] ?? null)
        --color-secondary: {{ $colors['secondary'] }};
    @endif
    @if ($colors['accent'] ?? null)
        --color-accent: {{ $colors['accent'] }};
    @endif
}

{{-- control de color de fuentes --}}
.text-primary   { color: var(--color-primary); }
.text-secondary { color: var(--color-secondary); }
.text-accent    { color: var(--color-accent); }
.text-on-primary { color: var(--color-on-primary, #ffffff); }
{{-- control de color de fondos --}}
.bg-primary   { background-color: var(--color-primary); }
.bg-secondary { background-color: var(--color-secondary); }
.bg-accent    { background-color: var(--color-accent); }
{{-- control de color de bordes --}}
.border-primary   { border-color: var(--color-primary); }
.border-secondary { border-color: var(--color-secondary); }
.outline-primary  { outline-color: var(--color-primary); }
.outline-secondary { outline-color: var(--color-secondary); }
