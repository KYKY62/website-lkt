<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $siteData['identity']['name'] }}</title>
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
