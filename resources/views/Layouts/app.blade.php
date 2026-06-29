<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Angkringan Songolimo')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-amber-50 font-sans">

{{-- Header --}}
<header class="bg-amber-800 text-white shadow-lg sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🍢</span>
            <div>
                <h1 class="font-bold text-lg leading-tight">Angkringan Songolimo</h1>
                <p class="text-amber-200 text-xs">Pesan di sini, bayar di kasir</p>
            </div>
        </div>
        <nav class="flex items-center gap-2">
            <a href="{{ route('menu.index') }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium
                      {{ request()->routeIs('menu.index') ? 'bg-amber-600' : 'hover:bg-amber-700' }}
                      transition">
                🍽 Menu
            </a>
            <a href="{{ route('orders.riwayat') }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium
                      {{ request()->routeIs('orders.riwayat') ? 'bg-amber-600' : 'hover:bg-amber-700' }}
                      transition">
                📋 Riwayat
            </a>
        </nav>
    </div>
</header>

{{-- Flash message --}}
@if (session('success'))
<div class="bg-green-50 border-b border-green-200 text-green-800 text-sm px-4 py-2.5 text-center" id="flash-msg">
    ✅ {{ session('success') }}
</div>
<script>setTimeout(() => document.getElementById('flash-msg')?.remove(), 4000)</script>
@endif

<main class="max-w-7xl mx-auto px-4 py-6">
    @yield('content')
</main>

<footer class="text-center text-xs text-amber-600 py-4 mt-8">
    © {{ date('Y') }} Angkringan Songolimo — Sistem Kasir
</footer>

@stack('scripts')
</body>
</html>
