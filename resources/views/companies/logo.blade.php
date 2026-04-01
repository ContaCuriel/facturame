<x-company-panel-layout :company="$company">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-6">
        Logo y Apariencia
    </h2>

    <div class="max-w-2xl">
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                <h3 class="text-lg font-medium">Logo de la Empresa</h3>
                <div class="mt-4 flex items-center space-x-4">
                    @if ($company->logo_path)
                        <img src="{{ Storage::url($company->logo_path) }}" alt="Logo Actual" class="h-20 w-20 object-contain rounded-md bg-gray-200">
                    @else
                        <div class="h-20 w-20 bg-gray-200 dark:bg-gray-700 rounded-md flex items-center justify-center text-gray-400">Sin Logo</div>
                    @endif
                    <form method="POST" action="{{ route('companies.logo.store', $company) }}" enctype="multipart/form-data">
                        @csrf
                        <div>
                            <label for="logo" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Subir nuevo logo</label>
                            <input id="logo" name="logo" type="file" class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-gray-200 dark:file:bg-gray-700" required>
                            <button type="submit" class="mt-2 inline-flex items-center px-4 py-2 bg-gray-800 text-xs text-white uppercase tracking-widest">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-company-panel-layout>