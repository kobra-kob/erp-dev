<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Documents RH d'un employé (contrats de travail…). Réservé ADMIN, isolé par entreprise.
 */
class EmployeeDocumentController extends Controller
{
    public function store(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeCompany($employee);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type'  => ['required', Rule::in(array_keys(EmployeeDocument::TYPES))],
            'file'  => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx'],
        ]);

        $file = $request->file('file');
        $path = $file->store('employees/' . $employee->company_id);

        $employee->documents()->create([
            'company_id'    => $employee->company_id,
            'uploaded_by'   => $request->user()->id,
            'title'         => $data['title'],
            'type'          => $data['type'],
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'mime'          => $file->getClientMimeType(),
            'size'          => $file->getSize(),
        ]);

        return back()->with('status', 'Document ajouté à la fiche employé.');
    }

    public function show(User $employee, EmployeeDocument $document): StreamedResponse
    {
        $this->guard($employee, $document);

        return Storage::response($document->path, $document->original_name);
    }

    public function download(User $employee, EmployeeDocument $document): StreamedResponse
    {
        $this->guard($employee, $document);

        return Storage::download($document->path, $document->original_name);
    }

    public function destroy(User $employee, EmployeeDocument $document): RedirectResponse
    {
        $this->guard($employee, $document);

        Storage::delete($document->path);
        $document->delete();

        return back()->with('status', 'Document supprimé.');
    }

    private function authorizeCompany(User $employee): void
    {
        abort_unless($employee->company_id === Auth::user()->company_id, 404);
    }

    private function guard(User $employee, EmployeeDocument $document): void
    {
        $this->authorizeCompany($employee);
        abort_unless($document->user_id === $employee->id && Storage::exists($document->path), 404);
    }
}
