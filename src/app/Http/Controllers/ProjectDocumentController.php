<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pièces jointes d'un chantier (photos, documents). Les fichiers sont stockés
 * sur le disque privé `local` et servis via une route authentifiée (jamais
 * exposés publiquement) → isolation par entreprise garantie.
 */
class ProjectDocumentController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', // 10 Mo
                'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx'],
        ]);

        $file = $request->file('file');
        $path = $file->store("projects/{$project->id}"); // disque local (privé)

        $project->documents()->create([
            'company_id'    => $project->company_id,
            'user_id'       => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'mime'          => $file->getClientMimeType(),
            'size'          => $file->getSize(),
        ]);

        return back()->with('status', 'Fichier ajouté.');
    }

    /** Aperçu inline (pour le visualiseur). */
    public function show(Project $project, ProjectDocument $document): StreamedResponse
    {
        abort_unless($document->project_id === $project->id, 404);
        abort_unless(Storage::exists($document->path), 404);

        return Storage::response($document->path, $document->original_name);
    }

    /** Téléchargement forcé. */
    public function download(Project $project, ProjectDocument $document): StreamedResponse
    {
        abort_unless($document->project_id === $project->id, 404);
        abort_unless(Storage::exists($document->path), 404);

        return Storage::download($document->path, $document->original_name);
    }

    public function destroy(Project $project, ProjectDocument $document): RedirectResponse
    {
        abort_unless($document->project_id === $project->id, 404);

        Storage::delete($document->path);
        $document->delete();

        return back()->with('status', 'Fichier supprimé.');
    }
}
