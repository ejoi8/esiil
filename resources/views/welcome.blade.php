<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>
        <meta name="description" content="Platform eSIJIL PUSPANITA untuk semakan sijil digital, pendaftaran program, dan muat turun sijil peserta.">
        <meta name="robots" content="index,follow">
        <meta name="theme-color" content="#a85a00">
        <link rel="canonical" href="{{ route('home') }}">
        <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ config('app.name') }}">
        <meta property="og:description" content="Platform eSIJIL PUSPANITA untuk semakan sijil digital, pendaftaran program, dan muat turun sijil peserta.">
        <meta property="og:url" content="{{ route('home') }}">
        <meta property="og:image" content="{{ url('/images/og/esijil-share.svg') }}">
        <meta property="og:image:alt" content="eSIJIL PUSPANITA">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ config('app.name') }}">
        <meta name="twitter:description" content="Platform eSIJIL PUSPANITA untuk semakan sijil digital, pendaftaran program, dan muat turun sijil peserta.">
        <meta name="twitter:image" content="{{ url('/images/og/esijil-share.svg') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|sora:500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-background font-sans text-text antialiased">
        <main class="flex min-h-screen items-center justify-center px-4">
            <h1 class="font-display text-4xl font-semibold text-text sm:text-5xl">
                {{ config('app.name') }}
            </h1>
        </main>
    </body>
</html>
