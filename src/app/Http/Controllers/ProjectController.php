<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $projects = Project::with('client')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('start_date')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('projects.index', compact('projects', 'status'));
    }

    public function create(): View
    {
        return view('projects.create', [
            'project' => new Project(['status' => 'planned', 'progress' => 0]),
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $project = Project::create($this->validated($request));

        return redirect()->route('projects.show', $project)->with('status', 'Chantier créé.');
    }

    public function show(Project $project): View
    {
        $project->load(['client', 'comments.user', 'documents', 'interventions' => fn ($q) => $q->orderBy('start_at')]);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        return view('projects.edit', [
            'project' => $project,
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project->update($this->validated($request));

        return redirect()->route('projects.show', $project)->with('status', 'Chantier mis à jour.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')->with('status', 'Chantier supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'client_id'   => ['nullable', Rule::exists('clients', 'id')->where('company_id', $request->user()->company_id)],
            'status'      => ['required', Rule::in(array_keys(Project::STATUSES))],
            'address'     => ['nullable', 'string', 'max:255'],
            'city'        => ['nullable', 'string', 'max:120'],
            'zip'         => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget'      => ['nullable', 'numeric', 'min:0'],
            'progress'    => ['required', 'integer', 'min:0', 'max:100'],
        ]);
    }
}
