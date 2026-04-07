<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @php
            $pageMetaTitle = $metaTitle ?? null;
            $pageMetaDescription = $metaDescription ?? null;
            $resolvedMetaTitle = $pageMetaTitle ? $pageMetaTitle.' | '.$siteName : $siteName;
            $resolvedMetaDescription = $pageMetaDescription ?: $siteDescription;
            $resolvedMetaUrl = $metaUrl ?? url()->current();
            $resolvedMetaType = $metaType ?? 'website';
            $resolvedMetaImage = $metaImage ?? null;
            $resolvedMetaImageAlt = $metaImageAlt ?? ($pageMetaTitle ?: $siteName);
            $resolvedMetaImageWidth = $metaImageWidth ?? null;
            $resolvedMetaImageHeight = $metaImageHeight ?? null;
            $resolvedMetaImageType = $metaImageType ?? null;
        @endphp
        <meta name="description" content="{{ $resolvedMetaDescription }}">
        <link rel="canonical" href="{{ $resolvedMetaUrl }}">

        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:type" content="{{ $resolvedMetaType }}">
        <meta property="og:title" content="{{ $resolvedMetaTitle }}">
        <meta property="og:description" content="{{ $resolvedMetaDescription }}">
        <meta property="og:url" content="{{ $resolvedMetaUrl }}">

        @if ($resolvedMetaImage)
            <meta property="og:image" content="{{ $resolvedMetaImage }}">
            <meta property="og:image:secure_url" content="{{ $resolvedMetaImage }}">
            <meta property="og:image:alt" content="{{ $resolvedMetaImageAlt }}">
            @if ($resolvedMetaImageWidth)
                <meta property="og:image:width" content="{{ $resolvedMetaImageWidth }}">
            @endif
            @if ($resolvedMetaImageHeight)
                <meta property="og:image:height" content="{{ $resolvedMetaImageHeight }}">
            @endif
            @if ($resolvedMetaImageType)
                <meta property="og:image:type" content="{{ $resolvedMetaImageType }}">
            @endif
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:image" content="{{ $resolvedMetaImage }}">
        @else
            <meta name="twitter:card" content="summary">
        @endif

        <meta name="twitter:title" content="{{ $resolvedMetaTitle }}">
        <meta name="twitter:description" content="{{ $resolvedMetaDescription }}">

        <title>{{ $resolvedMetaTitle }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-100 text-stone-900 antialiased">
        <div class="relative isolate overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.22),_transparent_28%),radial-gradient(circle_at_top_right,_rgba(14,116,144,0.18),_transparent_22%),linear-gradient(180deg,_#fafaf9_0%,_#f5f5f4_100%)]">
            <header class="border-b border-stone-200/80 bg-white/85 backdrop-blur">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-5 lg:px-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="space-y-2">
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 text-sm font-semibold uppercase tracking-[0.35em] text-stone-950">
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-stone-950 text-base text-white">TS</span>
                                <span>{{ $siteName }}</span>
                            </a>
                            @if ($siteTagline)
                                <p class="max-w-2xl text-sm text-stone-600">{{ $siteTagline }}</p>
                            @endif
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <form action="{{ route('search.index') }}" method="GET" class="flex items-center gap-2 rounded-full border border-stone-300 bg-white px-3 py-2 shadow-sm">
                                <input
                                    type="search"
                                    name="q"
                                    value="{{ request('q') }}"
                                    placeholder="Cari berita..."
                                    class="w-full min-w-0 border-none bg-transparent text-sm text-stone-800 outline-none focus:ring-0 sm:w-64"
                                >
                                <button type="submit" class="rounded-full bg-stone-950 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-stone-800">
                                    Cari
                                </button>
                            </form>

                            @auth
                                <a href="{{ route('dashboard') }}" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-stone-950 hover:text-stone-950">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-stone-950 hover:text-stone-950">
                                    Login Admin
                                </a>
                            @endauth
                        </div>
                    </div>

                    @if ($frontCategories->isNotEmpty())
                        <nav class="flex flex-wrap items-center gap-2">
                            @foreach ($frontCategories as $navCategory)
                                <a
                                    href="{{ $navCategory->publicUrl() }}"
                                    class="rounded-full border border-stone-300/80 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-stone-700 transition hover:border-amber-500 hover:text-stone-950"
                                >
                                    {{ $navCategory->name }}
                                </a>
                            @endforeach
                        </nav>
                    @endif
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-6 py-8 lg:px-8 lg:py-10">
                @yield('content')
            </main>

            <footer class="border-t border-stone-200/80 bg-white/90">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-8 text-sm text-stone-600 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div>
                        <p class="font-semibold text-stone-900">{{ $siteName }}</p>
                        <p>{{ $siteDescription }}</p>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('home') }}" class="transition hover:text-stone-950">Homepage</a>
                        <a href="{{ route('search.index') }}" class="transition hover:text-stone-950">Pencarian</a>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
