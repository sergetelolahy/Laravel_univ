<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Gestion universitaire') }}</title>
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/main.jsx'])
    </head>
    <body class="bg-slate-50">
        <div id="app"></div>
    </body>
</html>
