<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Crea tu web en minutos</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased">

    {{-- ============================================================ --}}
    {{-- NAVBAR --}}
    {{-- ============================================================ --}}
    <nav class="sticky top-0 z-50 bg-white/80 dark:bg-zinc-950/80 backdrop-blur border-b border-zinc-200 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
            <a href="/" class="flex items-center gap-2 text-xl font-bold text-primary-600 dark:text-primary-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
                {{ config('app.name') }}
            </a>

            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-zinc-600 dark:text-zinc-400">
                <a href="#features" class="hover:text-primary-600 dark:hover:text-primary-400 transition">Características</a>
                <a href="#how-it-works" class="hover:text-primary-600 dark:hover:text-primary-400 transition">Cómo funciona</a>
                <a href="#pricing" class="hover:text-primary-600 dark:hover:text-primary-400 transition">Planes</a>
            </div>

            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-primary-600 dark:hover:text-primary-400 transition">
                            Iniciar sesión
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-sm font-medium px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition">
                                Registrarse gratis
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    {{-- ============================================================ --}}
    {{-- HERO --}}
    {{-- ============================================================ --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-50 via-white to-secondary-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-950 -z-10"></div>
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-primary-200/30 dark:bg-primary-900/20 rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-secondary-200/30 dark:bg-secondary-900/20 rounded-full blur-3xl -z-10"></div>

        <div class="max-w-7xl mx-auto px-6 py-24 lg:py-36 flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
            <div class="flex-1 text-center lg:text-start">
                <span class="inline-block px-4 py-1.5 text-xs font-semibold rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300 mb-6">
                    Tu WordPress simplificado
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight tracking-tight">
                    Crea sitios web
                    <span class="text-primary-600 dark:text-primary-400">profesionales</span>
                    en minutos
                </h1>
                <p class="mt-6 text-lg text-zinc-600 dark:text-zinc-400 max-w-xl mx-auto lg:mx-0">
                    Cada usuario crea sus propios tenants con subdominio personalizado. Publica páginas y posts de forma sencilla, sin complicaciones.
                    Como WordPress, pero más simple.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="w-full sm:w-auto text-center px-8 py-3.5 rounded-xl bg-primary-600 text-white font-semibold hover:bg-primary-700 shadow-lg shadow-primary-600/25 transition">
                            Comenzar gratis
                        </a>
                    @endif
                    <a href="#how-it-works" class="w-full sm:w-auto text-center px-8 py-3.5 rounded-xl border border-zinc-300 dark:border-zinc-700 font-semibold hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        Ver cómo funciona
                    </a>
                </div>
                <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-500">Sin tarjeta de crédito · Configuración en 30 segundos</p>
            </div>

            <div class="flex-1 w-full max-w-lg lg:max-w-xl">
                <div class="relative">
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-2xl shadow-primary-900/10 overflow-hidden">
                        <div class="flex items-center gap-2 px-4 py-3 bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex gap-1.5">
                                <div class="size-3 rounded-full bg-red-400"></div>
                                <div class="size-3 rounded-full bg-amber-400"></div>
                                <div class="size-3 rounded-full bg-green-400"></div>
                            </div>
                            <div class="flex-1 mx-4">
                                <div class="bg-white dark:bg-zinc-700 rounded-md px-3 py-1 text-xs text-zinc-500 dark:text-zinc-400 text-center">
                                    mi-negocio.mycms.app
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="h-4 w-32 bg-primary-200 dark:bg-primary-800 rounded mb-3"></div>
                            <div class="h-3 w-full bg-zinc-100 dark:bg-zinc-800 rounded mb-2"></div>
                            <div class="h-3 w-4/5 bg-zinc-100 dark:bg-zinc-800 rounded mb-6"></div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="h-20 bg-secondary-100 dark:bg-secondary-900/30 rounded-lg"></div>
                                <div class="h-20 bg-primary-100 dark:bg-primary-900/30 rounded-lg"></div>
                            </div>
                            <div class="mt-4 h-3 w-3/4 bg-zinc-100 dark:bg-zinc-800 rounded"></div>
                            <div class="mt-2 h-3 w-1/2 bg-zinc-100 dark:bg-zinc-800 rounded"></div>
                        </div>
                    </div>
                    <div class="absolute -bottom-4 -left-4 px-4 py-2 bg-secondary-500 text-white text-sm font-semibold rounded-xl shadow-lg">
                        ✓ Subdominio propio
                    </div>
                    <div class="absolute -top-4 -right-4 px-4 py-2 bg-primary-500 text-white text-sm font-semibold rounded-xl shadow-lg">
                        ⚡ Listo al instante
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- FEATURES --}}
    {{-- ============================================================ --}}
    <section id="features" class="py-24 bg-zinc-50 dark:bg-zinc-900">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-sm font-semibold text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">Características</span>
                <h2 class="mt-3 text-3xl md:text-4xl font-bold">Todo lo que necesitas para publicar</h2>
                <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                    Herramientas potentes en una interfaz simple. Sin curva de aprendizaje.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Feature 1 --}}
                <div class="group p-6 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-lg transition">
                    <div class="size-12 flex items-center justify-center rounded-xl bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Multi-tenant con subdominios</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Cada usuario puede crear múltiples sitios web, cada uno con su propio subdominio personalizado listo al instante.
                    </p>
                </div>

                {{-- Feature 2 --}}
                <div class="group p-6 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-lg transition">
                    <div class="size-12 flex items-center justify-center rounded-xl bg-secondary-100 dark:bg-secondary-900/40 text-secondary-600 dark:text-secondary-400 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Editor de posts sencillo</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Crea y publica posts y páginas con un editor intuitivo. Sin bloques complicados, solo escribe y publica.
                    </p>
                </div>

                {{-- Feature 3 --}}
                <div class="group p-6 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-lg transition">
                    <div class="size-12 flex items-center justify-center rounded-xl bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm10 0a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Páginas personalizables</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Diseña páginas estáticas con un constructor visual. Ideal para landing pages, portfolios y más.
                    </p>
                </div>

                {{-- Feature 4 --}}
                <div class="group p-6 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-lg transition">
                    <div class="size-12 flex items-center justify-center rounded-xl bg-secondary-100 dark:bg-secondary-900/40 text-secondary-600 dark:text-secondary-400 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Rápido y ligero</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Sin plugins pesados ni bases de datos enormes. Tu sitio carga en milisegundos y ofrece una gran experiencia.
                    </p>
                </div>

                {{-- Feature 5 --}}
                <div class="group p-6 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-lg transition">
                    <div class="size-12 flex items-center justify-center rounded-xl bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Seguro por defecto</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Aislamiento completo entre tenants. Cada sitio tiene su propia base de datos y configuración independiente.
                    </p>
                </div>

                {{-- Feature 6 --}}
                <div class="group p-6 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-lg transition">
                    <div class="size-12 flex items-center justify-center rounded-xl bg-secondary-100 dark:bg-secondary-900/40 text-secondary-600 dark:text-secondary-400 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Personalización total</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Colores, tipografías y configuración del sitio a tu medida. Haz que cada tenant refleje tu marca.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- HOW IT WORKS --}}
    {{-- ============================================================ --}}
    <section id="how-it-works" class="py-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">Cómo funciona</span>
                <h2 class="mt-3 text-3xl md:text-4xl font-bold">De cero a publicado en 3 pasos</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                <div class="text-center">
                    <div class="size-16 mx-auto flex items-center justify-center rounded-2xl bg-primary-600 text-white text-2xl font-bold mb-6">1</div>
                    <h3 class="text-xl font-semibold mb-3">Regístrate gratis</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Crea tu cuenta en segundos. Sin tarjeta de crédito, sin compromisos. El plan gratuito incluye un tenant.
                    </p>
                </div>
                <div class="text-center">
                    <div class="size-16 mx-auto flex items-center justify-center rounded-2xl bg-primary-600 text-white text-2xl font-bold mb-6">2</div>
                    <h3 class="text-xl font-semibold mb-3">Crea tu tenant</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Elige un nombre para tu subdominio y tu sitio estará listo al instante. Personaliza colores y configuración.
                    </p>
                </div>
                <div class="text-center">
                    <div class="size-16 mx-auto flex items-center justify-center rounded-2xl bg-primary-600 text-white text-2xl font-bold mb-6">3</div>
                    <h3 class="text-xl font-semibold mb-3">Publica contenido</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Escribe posts, crea páginas y comparte tu contenido con el mundo. Así de fácil.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- PRICING --}}
    {{-- ============================================================ --}}
    <section id="pricing" class="py-24 bg-zinc-50 dark:bg-zinc-900">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-sm font-semibold text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">Planes y precios</span>
                <h2 class="mt-3 text-3xl md:text-4xl font-bold">Un plan para cada etapa</h2>
                <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                    Empieza gratis y escala cuando lo necesites. Sin sorpresas.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                {{-- Free Plan --}}
                <div class="flex flex-col p-8 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold">Gratis</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Perfecto para probar la plataforma</p>
                    <div class="mt-6 flex items-baseline gap-1">
                        <span class="text-4xl font-bold">$0</span>
                        <span class="text-zinc-500 dark:text-zinc-400">/mes</span>
                    </div>
                    <ul class="mt-8 flex flex-col gap-3 text-sm text-zinc-700 dark:text-zinc-300 flex-1">
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0 text-secondary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            1 tenant / subdominio
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0 text-secondary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            10 posts publicados
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0 text-secondary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            3 páginas estáticas
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0 text-secondary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Subdominio .mycms.app
                        </li>
                        <li class="flex items-center gap-2 text-zinc-400">
                            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            Sin dominio personalizado
                        </li>
                    </ul>
                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="mt-8 block text-center px-6 py-3 rounded-xl border border-zinc-300 dark:border-zinc-600 font-semibold hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
                        Empezar gratis
                    </a>
                </div>

                {{-- Pro Plan --}}
                <div class="flex flex-col p-8 rounded-2xl bg-primary-600 text-white relative overflow-hidden shadow-xl shadow-primary-600/20">
                    <div class="absolute top-0 right-0 px-3 py-1 text-xs font-bold bg-white/20 rounded-bl-xl">POPULAR</div>
                    <h3 class="text-lg font-semibold">Pro</h3>
                    <p class="mt-1 text-sm text-primary-100">Para creadores y emprendedores</p>
                    <div class="mt-6 flex items-baseline gap-1">
                        <span class="text-4xl font-bold">$9</span>
                        <span class="text-primary-200">/mes</span>
                    </div>
                    <ul class="mt-8 flex flex-col gap-3 text-sm text-primary-50 flex-1">
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            5 tenants / subdominios
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Posts ilimitados
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Páginas ilimitadas
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Dominio personalizado
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            SSL gratuito
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Soporte prioritario
                        </li>
                    </ul>
                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="mt-8 block text-center px-6 py-3 rounded-xl bg-white text-primary-700 font-semibold hover:bg-primary-50 transition">
                        Comenzar con Pro
                    </a>
                </div>

                {{-- Business Plan --}}
                <div class="flex flex-col p-8 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold">Business</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Para agencias y equipos</p>
                    <div class="mt-6 flex items-baseline gap-1">
                        <span class="text-4xl font-bold">$29</span>
                        <span class="text-zinc-500 dark:text-zinc-400">/mes</span>
                    </div>
                    <ul class="mt-8 flex flex-col gap-3 text-sm text-zinc-700 dark:text-zinc-300 flex-1">
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0 text-secondary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Tenants ilimitados
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0 text-secondary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Posts y páginas ilimitados
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0 text-secondary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Dominios personalizados ilimitados
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0 text-secondary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            API de acceso completo
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0 text-secondary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            White-label (tu marca)
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-5 shrink-0 text-secondary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Soporte dedicado
                        </li>
                    </ul>
                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="mt-8 block text-center px-6 py-3 rounded-xl border border-zinc-300 dark:border-zinc-600 font-semibold hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
                        Contactar ventas
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- CTA --}}
    {{-- ============================================================ --}}
    <section class="py-24">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <div class="p-12 lg:p-16 rounded-3xl bg-gradient-to-br from-primary-600 to-primary-800 text-white">
                <h2 class="text-3xl md:text-4xl font-bold">¿Listo para crear tu sitio?</h2>
                <p class="mt-4 text-lg text-primary-100 max-w-xl mx-auto">
                    Únete a cientos de creadores que ya publican contenido con {{ config('app.name') }}. Gratis, sin complicaciones.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-xl bg-white text-primary-700 font-semibold hover:bg-primary-50 shadow-lg transition">
                            Crear mi cuenta gratis
                        </a>
                    @endif
                    <a href="#pricing" class="px-8 py-3.5 rounded-xl border border-white/30 font-semibold hover:bg-white/10 transition">
                        Ver planes
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- FOOTER --}}
    {{-- ============================================================ --}}
    <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950">
        <div class="max-w-7xl mx-auto px-6 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <a href="/" class="flex items-center gap-2 text-lg font-bold text-primary-600 dark:text-primary-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        {{ config('app.name') }}
                    </a>
                    <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400 max-w-sm">
                        La forma más sencilla de crear sitios web con subdominios propios. Publica contenido como en WordPress, pero sin la complejidad.
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold text-sm mb-4">Producto</h4>
                    <ul class="flex flex-col gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                        <li><a href="#features" class="hover:text-primary-600 dark:hover:text-primary-400 transition">Características</a></li>
                        <li><a href="#pricing" class="hover:text-primary-600 dark:hover:text-primary-400 transition">Precios</a></li>
                        <li><a href="#how-it-works" class="hover:text-primary-600 dark:hover:text-primary-400 transition">Cómo funciona</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-sm mb-4">Legal</h4>
                    <ul class="flex flex-col gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                        <li><a href="#" class="hover:text-primary-600 dark:hover:text-primary-400 transition">Términos de servicio</a></li>
                        <li><a href="#" class="hover:text-primary-600 dark:hover:text-primary-400 transition">Política de privacidad</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-800 text-center text-sm text-zinc-400">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
            </div>
        </div>
    </footer>

</body>
</html>
