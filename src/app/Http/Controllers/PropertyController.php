<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Module Immobilier : biens. */
class PropertyController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $transaction = $request->query('transaction');

        $properties = Property::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($transaction, fn ($q) => $q->where('transaction', $transaction))
            ->latest()
            ->paginate(12)->withQueryString();

        return view('properties.index', compact('properties', 'status', 'transaction'));
    }

    public function create(): View
    {
        return view('properties.create', ['property' => new Property(['type' => 'appartement', 'transaction' => 'vente', 'status' => 'disponible'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Property::create($this->validated($request));

        return redirect()->route('properties.index')->with('status', 'Bien ajouté.');
    }

    public function edit(Property $property): View
    {
        return view('properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $property->update($this->validated($request));

        return redirect()->route('properties.index')->with('status', 'Bien mis à jour.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $property->delete();

        return redirect()->route('properties.index')->with('status', 'Bien supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'reference'   => ['nullable', 'string', 'max:100'],
            'type'        => ['required', Rule::in(array_keys(Property::TYPES))],
            'transaction' => ['required', Rule::in(['vente', 'location'])],
            'status'      => ['required', Rule::in(array_keys(Property::STATUSES))],
            'price'       => ['required', 'numeric', 'min:0'],
            'surface'     => ['nullable', 'numeric', 'min:0'],
            'rooms'       => ['nullable', 'integer', 'min:0'],
            'dpe'         => ['nullable', Rule::in(['A', 'B', 'C', 'D', 'E', 'F', 'G'])],
            'address'     => ['nullable', 'string', 'max:255'],
            'city'        => ['nullable', 'string', 'max:120'],
            'zip'         => ['nullable', 'string', 'max:10'],
            'owner_name'  => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
