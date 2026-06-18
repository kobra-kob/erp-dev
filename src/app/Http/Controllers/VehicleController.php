<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Module Concessionnaire : parc de véhicules. */
class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $condition = $request->query('condition');

        $vehicles = Vehicle::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($condition, fn ($q) => $q->where('condition', $condition))
            ->latest()
            ->paginate(12)->withQueryString();

        return view('vehicles.index', compact('vehicles', 'status', 'condition'));
    }

    public function create(): View
    {
        return view('vehicles.create', ['vehicle' => new Vehicle(['energy' => 'essence', 'condition' => 'occasion', 'status' => 'disponible'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Vehicle::create($this->validated($request));

        return redirect()->route('vehicles.index')->with('status', 'Véhicule ajouté au parc.');
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($this->validated($request));

        return redirect()->route('vehicles.index')->with('status', 'Véhicule mis à jour.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('status', 'Véhicule supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'brand'        => ['required', 'string', 'max:100'],
            'model'        => ['required', 'string', 'max:100'],
            'vin'          => ['nullable', 'string', 'max:30'],
            'registration' => ['nullable', 'string', 'max:15'],
            'year'         => ['nullable', 'integer', 'min:1950', 'max:' . (now()->year + 1)],
            'mileage'      => ['nullable', 'integer', 'min:0'],
            'energy'       => ['required', Rule::in(array_keys(Vehicle::ENERGIES))],
            'condition'    => ['required', Rule::in(['neuf', 'occasion'])],
            'status'       => ['required', Rule::in(array_keys(Vehicle::STATUSES))],
            'price'        => ['required', 'numeric', 'min:0'],
            'description'  => ['nullable', 'string'],
        ]);
    }
}
