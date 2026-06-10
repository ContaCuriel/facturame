<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Facturame ERP') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-900 selection:bg-indigo-500 selection:text-white">
        <div class="min-h-screen flex">
            
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 justify-center items-center relative overflow-hidden">
                <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-black/20 rounded-full blur-3xl"></div>

                <div class="relative z-10 text-center px-12">
                    <div class="mb-8 inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-md shadow-2xl border border-white/20">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h1 class="text-4xl font-extrabold text-white mb-4 tracking-tight">Bienvenido a Facturame</h1>
                    <p class="text-lg text-indigo-100 font-medium leading-relaxed max-w-md mx-auto">Tu ERP financiero inteligente. Gestiona empresas, timbra facturas y controla tu cobranza en un solo lugar.</p>
                </div>
            </div>

            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 sm:p-12 relative">
                
                <div class="lg:hidden mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                </div>

                <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 px-8 py-10 z-10">
                    {{ $slot }}
                </div>
                
                <p class="mt-8 text-xs text-gray-500 dark:text-gray-500 text-center font-medium">
                    &copy; {{ date('Y') }} Facturame ERP. Todos los derechos reservados.
                </p>
            </div>

        </div>
    </body>
</html>