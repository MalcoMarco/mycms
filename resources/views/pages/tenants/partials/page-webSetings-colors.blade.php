{{-- Estilos para el manejo de colores del sitio web --}}
:root {
    @if ($webSetting->primary_color)
        --color-primary: {{ $webSetting->primary_color }};
        --color-primary-hover: color-mix(in srgb, var(--color-primary), black 10%);
        --color-primary-soft: color-mix(in srgb, var(--color-primary), white 80%);
    @endif
    @if ($webSetting->secondary_color)
        --color-secondary: {{ $webSetting->secondary_color }};
    @endif
    @if ($webSetting->accent_color)
        --color-accent: {{ $webSetting->accent_color }};
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
