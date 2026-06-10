<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight">Editar Empresa: {{ $company->commercial_name ?? $company->name }}</h2>
    </x-slot>

    <div class="py-12 max-w-3xl mx-auto">
        <div class="bg-white p-8 rounded-2xl shadow-sm">
            <form method="POST" action="{{ route('companies.update', $company) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Razón Social (Legal) *</label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" class="w-full rounded-lg border-gray-300" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Nombre Comercial (Opcional)</label>
                    <input type="text" name="commercial_name" value="{{ old('commercial_name', $company->commercial_name) }}" class="w-full rounded-lg border-gray-300">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">RFC *</label>
                    <input type="text" name="rfc" value="{{ old('rfc', $company->rfc) }}" class="w-full rounded-lg border-gray-300 uppercase" required maxlength="13">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Régimen Fiscal *</label>
                        <input type="text" name="fiscal_regime" value="{{ old('fiscal_regime', $company->fiscal_regime) }}" class="w-full rounded-lg border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Código Postal *</label>
                        <input type="text" name="zip_code" value="{{ old('zip_code', $company->zip_code) }}" class="w-full rounded-lg border-gray-300" required maxlength="5">
                    </div>
                </div>

                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-indigo-700">Actualizar Datos</button>
            </form>
        </div>
    </div>
</x-app-layout>