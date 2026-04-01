<x-company-panel-layout :company="$company">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-6">
        Editando Alumno: {{ $student->name }}
    </h2>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">

            <form method="POST" action="{{ route('students.update', $student) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre del Alumno -->
                    <div class="md:col-span-2">
                        <label for="name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nombre Completo del Alumno*</label>
                        <input id="name" name="name" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('name', $student->name) }}" required />
                    </div>

                    <!-- CURP -->
                    <div>
                        <label for="curp" class="block font-medium text-sm text-gray-700 dark:text-gray-300">CURP*</label>
                        <input id="curp" name="curp" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('curp', $student->curp) }}" required />
                    </div>

                    <!-- RVOE -->
                    <div>
                        <label for="aut_rvoe" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Aut. RVOE*</label>
                        <input id="aut_rvoe" name="aut_rvoe" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" value="{{ old('aut_rvoe', $student->aut_rvoe) }}" required />
                    </div>
                    
                    <!-- Nivel Educativo -->
                    <div class="md:col-span-2">
                        <label for="education_level" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nivel Educativo*</label>
                        <select id="education_level" name="education_level" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-900 dark:border-gray-600" required>
                            <option value="" disabled>-- Selecciona un nivel --</option>
                            @foreach ($educationLevels as $level)
                                <option value="{{ $level }}" @selected(old('education_level', $student->education_level) == $level)>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-8">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">
                        Actualizar Alumno
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-company-panel-layout>
