<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PageBuilder | {{ $webSetting->meta_title }} - {{ $post->title }}</title>
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
    <script>
        window.post = {
            id: {{ $post->id }},
            title: "{{ $post->title }}",
            slug: "{{ $post->slug }}",
            status: "{{ $post->status }}",
            type_id: "{{ $post->type_id }}",
            cdns: {!! json_encode($post->cdns) !!}
        };
        window.webSettings = {
            google_analytics_id: "{{ $webSetting->google_analytics_id ?? '' }}",
            global_cdn_styles: {!! json_encode($webSetting->global_cdn_urls['styles'] ?? []) !!},
            global_cdn_scripts: {!! json_encode($webSetting->global_cdn_urls['scripts'] ?? []) !!},
            custom_head_scripts: `{!! addslashes($webSetting->custom_head_scripts ?? '') !!}`,
            custom_body_scripts: `{!! addslashes($webSetting->custom_body_scripts ?? '') !!}`
        };
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
