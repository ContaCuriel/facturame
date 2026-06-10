<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            Certificados SAT: <span class="text-indigo-600 dark:text-indigo-400">{{ $company->commercial_name ?? $company->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Sello Digital (CSD)</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Requerido para timbrar facturas.</p>
                        </div>
                    </div>

                    @if($company->csd_cer_path && $company->csd_expires_at)
                        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                            <p class="text-sm text-green-800 dark:text-green-400 font-bold flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                CSD Activo
                            </p>
                            <p class="text-xs text-green-600 dark:text-green-500 mt-1">
                                Caduca el: {{ \Carbon\Carbon::parse($company->csd_expires_at)->format('d/m/Y') }}
                            </p>
                        </div>
                    @endif

                    <form action="{{ route('companies.storeCsd', $company) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Archivo .CER</label>
                            <input type="file" name="csd_cer" accept=".cer" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Archivo .KEY</label>
                            <input type="file" name="csd_key" accept=".key" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Contraseña del CSD</label>
                            <input type="password" name="csd_password" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm" required placeholder="••••••••">
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-md">
                            Subir / Actualizar CSD
                        </button>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">e.firma (FIEL)</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Requerido para descargas automáticas del SAT.</p>
                        </div>
                    </div>

                    @if($company->fiel_cer_path && $company->fiel_expires_at)
                        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                            <p class="text-sm text-green-800 dark:text-green-400 font-bold flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                e.firma Activa
                            </p>
                            <p class="text-xs text-green-600 dark:text-green-500 mt-1">
                                Caduca el: {{ \Carbon\Carbon::parse($company->fiel_expires_at)->format('d/m/Y') }}
                            </p>
                        </div>
                    @endif

                    <form action="{{ route('companies.fiel.store', $company) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Archivo .CER</label>
                            <input type="file" name="fiel_cer" accept=".cer" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Archivo .KEY</label>
                            <input type="file" name="fiel_key" accept=".key" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100" required>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Contraseña de la e.firma</label>
                            <input type="password" name="fiel_password" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-purple-500 focus:border-purple-500 shadow-sm" required placeholder="••••••••">
                        </div>
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-md">
                            Subir / Actualizar e.firma
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>