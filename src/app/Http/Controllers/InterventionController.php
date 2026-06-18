<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Intervention;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InterventionController extends Controller
{
    /** Vue calendrier (jour / semaine / mois). */
    public function index(): View
    {
        return view('interventions.index');
    }

    /** Évènements consommés par FullCalendar (JSON). */
    public function events(Request $request): JsonResponse
    {
        $interventions = Intervention::with('technician', 'client')
            ->when($request->query('start'), fn ($q, $s) => $q->where('end_at', '>=', $s))
            ->when($request->query('end'), fn ($q, $e) => $q->where('start_at', '<=', $e))
            ->get();

        return response()->json($interventions->map(fn (Intervention $i) => [
            'id'    => $i->id,
            'title' => $i->title . ($i->technician ? ' — ' . $i->technician->name : ''),
            'start' => $i->start_at->toIso8601String(),
            'end'   => $i->end_at->toIso8601String(),
            'color' => $i->color(),
            'url'   => route('interventions.show', $i),
        ]));
    }

    public function create(Request $request): View
    {
        $intervention = new Intervention([
            'start_at' => $request->query('date')
                ? $request->query('date') . ' 09:00'
                : now()->setTime(9, 0)->format('Y-m-d H:i'),
            'end_at'   => $request->query('date')
                ? $request->query('date') . ' 11:00'
                : now()->setTime(11, 0)->format('Y-m-d H:i'),
        ]);

        return view('interventions.create', $this->formData($intervention));
    }

    public function store(Request $request): RedirectResponse
    {
        $intervention = Intervention::create($this->validated($request));

        return redirect()->route('interventions.show', $intervention)->with('status', 'Intervention planifiée.');
    }

    public function show(Intervention $intervention): View
    {
        $intervention->load('technician', 'client', 'project');

        return view('interventions.show', compact('intervention'));
    }

    public function edit(Intervention $intervention): View
    {
        return view('interventions.edit', $this->formData($intervention));
    }

    public function update(Request $request, Intervention $intervention): RedirectResponse
    {
        $intervention->update($this->validated($request));

        return redirect()->route('interventions.show', $intervention)->with('status', 'Intervention mise à jour.');
    }

    public function destroy(Intervention $intervention): RedirectResponse
    {
        $intervention->delete();

        return redirect()->route('interventions.index')->with('status', 'Intervention supprimée.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Intervention $intervention): array
    {
        return [
            'intervention' => $intervention,
            'clients'      => Client::orderBy('name')->get(),
            'projects'     => Project::orderBy('name')->get(),
            'technicians'  => User::where('company_id', auth()->user()->company_id)
                ->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $companyId = $request->user()->company_id;

        return $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'status'        => ['required', Rule::in(array_keys(Intervention::STATUSES))],
            'client_id'     => ['nullable', Rule::exists('clients', 'id')->where('company_id', $companyId)],
            'project_id'    => ['nullable', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'technician_id' => ['nullable', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'address'       => ['nullable', 'string', 'max:255'],
            'start_at'      => ['required', 'date'],
            'end_at'        => ['required', 'date', 'after:start_at'],
            'notes'         => ['nullable', 'string'],
        ]);
    }
}
