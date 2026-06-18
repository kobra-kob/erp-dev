<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Gestion documentaire transverse (contrats, photos, PDF…). Fichiers stockés
 * sur le disque privé `local` et servis via route authentifiée → isolation tenant.
 */
class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        $documents = Document::with('client')
            ->when($category, fn ($q) => $q->where('category', $category))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('documents.index', [
            'documents' => $documents,
            'category'  => $category,
            'clients'   => Client::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'category'  => ['required', Rule::in(array_keys(Document::CATEGORIES))],
            'client_id' => ['nullable', Rule::exists('clients', 'id')->where('company_id', $request->user()->company_id)],
            'file'      => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx'],
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/' . $request->user()->company_id);

        Document::create([
            'company_id'    => $request->user()->company_id,
            'client_id'     => $data['client_id'] ?? null,
            'user_id'       => $request->user()->id,
            'title'         => $data['title'],
            'category'      => $data['category'],
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'mime'          => $file->getClientMimeType(),
            'size'          => $file->getSize(),
        ]);

        return back()->with('status', 'Document ajouté.');
    }

    /** Aperçu inline (pour le visualiseur). */
    public function show(Document $document): StreamedResponse
    {
        abort_unless(Storage::exists($document->path), 404);

        return Storage::response($document->path, $document->original_name);
    }

    /** Téléchargement forcé. */
    public function download(Document $document): StreamedResponse
    {
        abort_unless(Storage::exists($document->path), 404);

        return Storage::download($document->path, $document->original_name);
    }

    public function destroy(Document $document): RedirectResponse
    {
        Storage::delete($document->path);
        $document->delete();

        return back()->with('status', 'Document supprimé.');
    }
}
