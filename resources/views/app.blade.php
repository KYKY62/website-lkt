<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @php
            $metaImage = $meta['image'] ?? Vite::asset('resources/img/logo_langkat.png');
        @endphp
        <title>{{ $meta['title'] }}</title>
        <meta name="description" content="{{ $meta['description'] }}">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ $meta['url'] }}">
        <meta property="og:locale" content="id_ID">
        <meta property="og:site_name" content="{{ $meta['site_name'] }}">
        <meta property="og:type" content="{{ $meta['type'] }}">
        <meta property="og:title" content="{{ $meta['title'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
        <meta property="og:url" content="{{ $meta['url'] }}">
        <meta property="og:image" content="{{ $metaImage }}">
        <meta property="og:image:alt" content="{{ $meta['title'] }}">
        @if (! empty($meta['published_time']))
            <meta property="article:published_time" content="{{ $meta['published_time'] }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $meta['title'] }}">
        <meta name="twitter:description" content="{{ $meta['description'] }}">
        <meta name="twitter:image" content="{{ $metaImage }}">
        <link rel="icon" type="image/png" href="{{ Vite::asset('resources/img/logo_langkat.png') }}">
        <link rel="apple-touch-icon" href="{{ Vite::asset('resources/img/logo_langkat.png') }}">
        <script>
            window.__SITE_DATA__ = @json($siteData);
            window.Laravel = {
                csrfToken: @json(csrf_token()),
                currentPath: @json($currentPath),
            };
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
