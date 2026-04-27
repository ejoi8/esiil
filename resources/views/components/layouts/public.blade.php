@props([
    'title' => config('app.name'),
    'eyebrow' => 'eSIJIL',
    'heading' => 'Semakan dan Muat Turun Sijil',
    'description' => 'Platform semakan sijil, pendaftaran program, dan muat turun sijil digital eSIJIL PUSPANITA.',
    'canonical' => null,
    'robots' => 'index,follow',
    'ogType' => 'website',
    'ogImage' => '/images/og/esijil-share.svg',
    'ogImageAlt' => 'eSIJIL PUSPANITA',
])

@php
    $siteName = config('app.name');
    $metaTitle = $title === $siteName ? $siteName : "{$title} | {$siteName}";
    $metaDescription = trim((string) $description);
    $metaRobots = trim((string) $robots);
    $metaCanonical = $canonical === false ? null : ($canonical ?: url()->current());
    $metaUrl = $canonical === false
        ? request()->fullUrl()
        : ($metaCanonical ?: url()->current());
    $metaOgImage = str_starts_with((string) $ogImage, 'http')
        ? (string) $ogImage
        : url((string) $ogImage);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <meta name="robots" content="{{ $metaRobots }}">
        <meta name="theme-color" content="#a85a00">
        @if ($metaCanonical)
            <link rel="canonical" href="{{ $metaCanonical }}">
        @endif

        <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:type" content="{{ $ogType }}">
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:url" content="{{ $metaUrl }}">
        <meta property="og:image" content="{{ $metaOgImage }}">
        <meta property="og:image:alt" content="{{ $ogImageAlt }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        <meta name="twitter:image" content="{{ $metaOgImage }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|sora:500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-background font-sans text-text antialiased">
        <div class="relative isolate min-h-screen overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-80 bg-linear-to-b from-accent/45 via-background to-background"></div>
            <div class="absolute inset-x-0 top-0 h-px bg-white/80"></div>
            <div class="absolute inset-x-0 top-28 h-px bg-text/10"></div>

            <div class="relative mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <header class="flex flex-col gap-5 border-b border-text/10 pb-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0 space-y-2">
                        <p class="text-xs font-bold uppercase tracking-[0.28em] text-primary">{{ $eyebrow }}</p>
                        <h1 class="font-display text-2xl font-semibold text-text sm:text-3xl">{{ $heading }}</h1>
                    </div>

                </header>

                <main class="min-w-0 flex-1 py-8 lg:py-10">
                    {{ $slot }}
                </main>

                <footer class="border-t border-text/10 pt-6 text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-text/55 sm:text-sm">
                        ICT PUSPAINTA @ 2026
                    </p>
                </footer>
            </div>
        </div>
    </body>
</html>
