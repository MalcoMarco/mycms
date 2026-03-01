<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PageBuilder</title>
    <style>
        /* El body y el html deben ocupar toda la pantalla */
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
            /* Evita scrolls dobles extraños */
        }

        /* El contenedor del editor debe ser flexible o tener altura fija */
        #gjs {
            height: 100vh !important;
            width: 100%;
        }
    </style>
    <script>
        window.post = { id: {{ $post->id }}, title: "{{ $post->title }}", slug: "{{ $post->slug }}", status: "{{ $post->status }}", type_id: "{{ $post->type_id }}", cdns: {!! json_encode($post->cdns) !!} };
        window.subdomain = "{{ tenant('id') }}";
    </script>
</head>

<body>
    <!-- Element where the editor will be rendered -->
    <div id="gjs">
        {!! $post->content_body !!}
    </div>

    
    @vite(['resources/js/grapeJsModule.js'])
</body>

</html>
