<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $webSetting->meta_title }} - {{ $post->title }}</title>
    {{-- Agregrar styles Globales y del post --}}
    @foreach ($webSetting->global_cdn_urls['styles'] ?? [] as $styleGlobal)
        <link rel="stylesheet" href="{{ $styleGlobal }}">        
    @endforeach
    @foreach ($post->cdns['styles'] ?? [] as $style)
        <link rel="stylesheet" href="{{ $style }}">
    @endforeach

    {{-- Tailwind CSS browser debe ir en head para evitar FOUC --}}
    @foreach ($webSetting->global_cdn_urls['scripts'] ?? [] as $scriptGlobal)
        @if (str_contains($scriptGlobal, 'tailwindcss'))
            <script src="{{ $scriptGlobal }}"></script>
        @endif        
    @endforeach
    @foreach ($post->cdns['scripts'] ?? [] as $script)
        @if (str_contains($script, 'tailwindcss'))
            <script src="{{ $script }}"></script>
        @endif
    @endforeach

    {!! $webSetting->custom_head_scripts !!}
    
    <style>
        @include('pages.tenants.partials.page-webSetings-colors')
        {!! $post->content_css !!}
    </style>
</head>


    {{-- BODY DEL POST --}}
    {!! $post->content_body !!}

    {{-- CDNs Scripts Globales --}}
    @foreach ($webSetting->global_cdn_urls['scripts'] ?? [] as $scriptGlobal)
        @unless (str_contains($scriptGlobal, 'tailwindcss'))
            <script src="{{ $scriptGlobal }}" defer></script>
        @endunless
    @endforeach
    {{-- CDNs Scripts del post --}}
    @foreach ($post->cdns['scripts'] ?? [] as $script)
        @unless (str_contains($script, 'tailwindcss'))
            <script src="{{ $script }}" defer></script>
        @endunless
    @endforeach

    {!! $webSetting->custom_body_scripts !!}

    @php
        $contentJs = trim($post->content_js ?? '');
    @endphp

    @if ($contentJs !== '')
        @if (str_contains($contentJs, '<script'))
            {!! $contentJs !!}
        @else
            <script>
                {!! $contentJs !!}
            </script>
        @endif
    @endif



</html>
