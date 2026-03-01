<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $post->title }}</title>

    @foreach ($post->cdns['styles'] ?? [] as $style)
        <link rel="stylesheet" href="{{ $style }}">
    @endforeach
    @foreach ($post->cdns['scripts'] ?? [] as $script)
        @if (str_contains($script, 'tailwindcss'))
            {{-- Tailwind CSS browser debe ir en head para evitar FOUC --}}
            <script src="{{ $script }}"></script>
        @endif
    @endforeach
    <style>
        {!! $post->content_css !!}
    </style>
</head>

<body>
    {!! $post->content_body !!}

    @foreach ($post->cdns['scripts'] ?? [] as $script)
        @unless (str_contains($script, 'tailwindcss'))
            <script src="{{ $script }}" defer></script>
        @endunless
    @endforeach
    <script>
        {!! $post->content_js !!}
    </script>
</body>

</html>
