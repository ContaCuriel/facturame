<x-company-panel-layout :company="$company">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-6">
        Configuración de Sellos Digitales (CSD)
    </h2>

    <div class="max-w-2xl">
        {{-- ✅ --- BLOQUE AÑADIDO PARA MOSTRAR MENSAJES --- ✅ --}}
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md relative dark:bg-green-900 dark:border-green-600 dark:text-green-300" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md relative dark:bg-red-900 dark:border-red-600 dark:text-red-300" role="alert">
                <strong class="font-bold">¡Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
             <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md" role="alert">
                <strong class="font-bold">Por favor, corrige los siguientes errores:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Sube aquí los archivos de tu Certificado de Sello Digital (.cer y .key) y su contraseña para poder emitir facturas. Estos archivos se guardarán de forma segura.
                </p>

                <form method="POST" action="{{ route('companies.csd.store', $company) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-6">
                        <!-- Archivo .cer -->
                        <div>
                            <label for="csd_cer" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Archivo de Certificado (.cer)</label>
                            <input id="csd_cer" name="csd_cer" type="file" class="block mt-1 w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-gray-200 file:text-gray-700
                                dark:file:bg-gray-700 dark:file:text-gray-200
                                hover:file:bg-gray-300 dark:hover:file:bg-gray-600" required>
                        </div>

                        <!-- Archivo .key -->
                        <div>
                            <label for="csd_key" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Archivo de Llave Privada (.key)</label>
                            <input id="csd_key" name="csd_key" type="file" class="block mt-1 w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-gray-200 file:text-gray-700
                                dark:file:bg-gray-700 dark:file:text-gray-200
                                hover:file:bg-gray-300 dark:hover:file:bg-gray-600" required>
                        </div>

                        <!-- Contraseña -->
                        <div>
                            <label for="csd_password" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Contraseña de la Llave Privada</label>
                            <input id="csd_password" name="csd_password" type="password" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" required>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">
                            Guardar Certificados
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-company-panel-layout>
