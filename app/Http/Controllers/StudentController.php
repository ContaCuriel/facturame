<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StudentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);
        $company = Company::findOrFail($request->query('company_id'));
        $this->authorize('view', $company);

        $students = Student::where('company_id', $company->id)->get();

        return view('students.index', compact('students', 'company'));
    }

    public function create(Request $request)
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);
        $company = Company::findOrFail($request->query('company_id'));
        $this->authorize('view', $company);

        $educationLevels = [
            'Preescolar', 'Primaria', 'Secundaria', 'Profesional técnico', 'Bachillerato o su equivalente',
        ];

        return view('students.create', compact('company', 'educationLevels'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'curp' => 'required|string|size:18|unique:students,curp,NULL,id,company_id,'.$request->company_id,
            'education_level' => 'required|string',
            'aut_rvoe' => 'required|string|max:100',
        ]);

        $company = Company::findOrFail($validatedData['company_id']);
        $this->authorize('update', $company);

        $company->students()->create($validatedData);

        return redirect()->route('students.index', ['company_id' => $company->id])
                         ->with('success', 'Alumno registrado exitosamente!');
    }

    /**
     * Muestra el formulario para editar un alumno.
     */
    public function edit(Student $student)
    {
        $this->authorize('update', $student->company);

        $educationLevels = [
            'Preescolar', 'Primaria', 'Secundaria', 'Profesional técnico', 'Bachillerato o su equivalente',
        ];

        return view('students.edit', [
            'student' => $student,
            'company' => $student->company,
            'educationLevels' => $educationLevels,
        ]);
    }

    /**
     * Actualiza un alumno existente.
     */
    public function update(Request $request, Student $student)
    {
        $this->authorize('update', $student->company);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'curp' => 'required|string|size:18|unique:students,curp,'.$student->id.',id,company_id,'.$student->company_id,
            'education_level' => 'required|string',
            'aut_rvoe' => 'required|string|max:100',
        ]);

        $student->update($validatedData);

        return redirect()->route('students.index', ['company_id' => $student->company_id])
                         ->with('success', 'Alumno actualizado exitosamente!');
    }
}
