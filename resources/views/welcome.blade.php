<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'TodakSiring') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-950 text-stone-100">
    <main class="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-6 py-16 lg:px-10">
        <div class="grid gap-10 lg:grid-cols-[1.4fr_0.9fr] lg:items-center">
            <section class="space-y-6">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-400">Editorial Workspace</p>
                <h1 class="max-w-3xl text-4xl font-bold tracking-tight text-white sm:text-5xl">
                    {{ config('app.brand_name', config('app.name', 'TodakSiring')) }}
                </h1>
                <p class="max-w-2xl text-lg leading-8 text-stone-300">
                    Portal berita modern berbasis Laravel dengan workflow internal untuk admin, editor, dan wartawan.
                    Fase ini sudah menyiapkan login session sebagai fondasi panel redaksi.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">
                        Masuk ke Panel
                    </a>
                    <a href="{{ route('home') }}" class="inline-flex items-center rounded-xl border border-stone-700 px-5 py-3 text-sm font-semibold text-stone-200 transition hover:border-stone-500 hover:bg-stone-900">
                        Halaman Awal
                    </a>
                </div>
            </section>

            <aside class="rounded-3xl border border-stone-800 bg-stone-900/80 p-6 shadow-2xl shadow-black/30 backdrop-blur">
                <h2 class="text-lg font-semibold text-white">Akun Seeder Default</h2>
                <div class="mt-5 space-y-4 text-sm text-stone-300">
                    <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                        <p class="font-medium text-white">Admin</p>
                        <p>`admin@local.test`</p>
                    </div>
                    <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                        <p class="font-medium text-white">Editor</p>
                        <p>`editor@local.test`</p>
                    </div>
                    <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                        <p class="font-medium text-white">Wartawan</p>
                        <p>`wartawan@local.test`</p>
                    </div>
                    <div class="rounded-2xl border border-amber-500/20 bg-amber-500/10 p-4 text-amber-100">
                        <p class="font-medium">Password default</p>
                        <p>`password`</p>
                    </div>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>
