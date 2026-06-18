<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Module Opticien : ordonnances optiques. */
class PrescriptionController extends Controller
{
    public function index(): View
    {
        $prescriptions = Prescription::with('client')->latest('prescribed_at')->latest('id')->paginate(20);

        return view('prescriptions.index', compact('prescriptions'));
    }

    public function create(): View
    {
        return view('prescriptions.create', [
            'prescription' => new Prescription(['prescribed_at' => now()->toDateString()]),
            'clients'      => Client::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Prescription::create($this->validated($request));

        return redirect()->route('prescriptions.index')->with('status', 'Ordonnance enregistrée.');
    }

    public function edit(Prescription $prescription): View
    {
        return view('prescriptions.edit', [
            'prescription' => $prescription,
            'clients'      => Client::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Prescription $prescription): RedirectResponse
    {
        $prescription->update($this->validated($request));

        return redirect()->route('prescriptions.index')->with('status', 'Ordonnance mise à jour.');
    }

    public function destroy(Prescription $prescription): RedirectResponse
    {
        $prescription->delete();

        return redirect()->route('prescriptions.index')->with('status', 'Ordonnance supprimée.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $deg = ['nullable', 'integer', 'min:0', 'max:180'];
        $dpt = ['nullable', 'numeric', 'min:-30', 'max:30'];

        return $request->validate([
            'client_id'          => ['required', Rule::exists('clients', 'id')->where('company_id', $request->user()->company_id)],
            'prescriber'         => ['nullable', 'string', 'max:255'],
            'prescribed_at'      => ['required', 'date'],
            'od_sphere'          => $dpt,
            'od_cylinder'        => $dpt,
            'od_axis'            => $deg,
            'od_addition'        => ['nullable', 'numeric', 'min:0', 'max:10'],
            'og_sphere'          => $dpt,
            'og_cylinder'        => $dpt,
            'og_axis'            => $deg,
            'og_addition'        => ['nullable', 'numeric', 'min:0', 'max:10'],
            'pupillary_distance' => ['nullable', 'integer', 'min:40', 'max:90'],
            'notes'              => ['nullable', 'string'],
        ]);
    }
}
