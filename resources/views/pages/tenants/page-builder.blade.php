<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PageBuilder-GrapesJs | {{ $webSetting->meta_title }} - {{ $post->title }}</title>
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }
        #gjs {
            height: 100vh !important;
            width: 100%;
        }
    </style>
        {{-- Obtener los colores del sitio web desde @theme en el contenido --}}
    @php
        $colors = [];
        $rawContent = $post->content ?? '';

        if (preg_match('/<style[^>]*type=["\']text\/tailwindcss["\'][^>]*>.*?@theme\s*\{([^}]*)\}/s', $rawContent, $themeMatch)) {
            preg_match_all('/--color-(\w[\w-]*)\s*:\s*([^;\n]+)/', $themeMatch[1], $colorMatches, PREG_SET_ORDER);
            foreach ($colorMatches as $match) {
                $colors[trim($match[1])] = trim($match[2]);
            }
        }

        // Detectar si el contenido incluye el CDN de Tailwind
        $tailwindCdn = null;
        if (preg_match('/<script[^>]+src=["\']([^"\'>]*tailwindcss[^"\'>]*)["\'][^>]*>/', $rawContent, $cdnMatch)) {
            $tailwindCdn = $cdnMatch[1];
        }
    @endphp
    <script>
        window.post = {
            id: {{ $post->id }},
            slug: "{{ $post->slug }}",
        };
        window.webSettings = {
            @if(count($colors) > 0)
             canvas_styles: @json(view('pages.tenants.partials.page-webSetings-colors', ['colors' => $colors])->render()),
            @endif
          };
        window.subdomain = "{{ tenant('id') }}";
        @if($tailwindCdn)
        window.tailwindCdn = @json($tailwindCdn);
        @endif
        
    </script>
</head>

<body>
    {{-- Extraer el body y sus atributos (clases, etc.) --}}
    @php
        $bodyContent = $post->content ?? '';
        $bodyAttrs = '';
        $bodyAttrClass = '';
        if (preg_match('/<body([^>]*)>(.*)<\/body>/s', $post->content, $bodyMatch)) {
            $bodyAttrs = trim($bodyMatch[1]);
            $bodyContent = $bodyMatch[2];

            if (preg_match('/class=["\']([^"\']*)["\']/', $bodyAttrs, $classMatch)) {
                $bodyAttrClass = $classMatch[1];
            }
        }

        // Extraer <script> tags del body para preservarlos (GrapesJS los elimina)
        $bodyScripts = '';
        if (preg_match_all('/<script[\s\S]*?<\/script>/i', $bodyContent, $scriptMatches)) {
            $bodyScripts = implode("\n", $scriptMatches[0]);
            $bodyContent = preg_replace('/<script[\s\S]*?<\/script>/i', '', $bodyContent);
        }
    @endphp
    <!-- Element where the editor will be rendered -->
    <div id="gjs" data-body-class="{{ e($bodyAttrClass) }}">
        {!! $bodyContent !!}
    </div>


    <script>
    window.bodyScripts = @json($bodyScripts);
    </script>

    @vite(['resources/js/grapeJsModule.js'])
</body>

</html>
