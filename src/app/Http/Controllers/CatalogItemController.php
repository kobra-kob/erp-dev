<?php

namespace App\Http\Controllers;

use App\Models\CatalogItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Catalogue de prestations (module Bâtiment) : bibliothèque réutilisable dans
 * les devis. Accès conditionné au module « batiment » actif (middleware sector).
 */
class CatalogItemController extends Controller
{
    public function index(Request $request): View
    {
        $trade = $request->query('trade');

        $items = CatalogItem::query()
            ->when($trade, fn ($q) => $q->where('trade', $trade))
            ->orderBy('trade')->orderBy('label')
            ->paginate(20)->withQueryString();

        return view('catalog.index', compact('items', 'trade'));
    }

    public function create(): View
    {
        return view('catalog.create', ['item' => new CatalogItem(['line_type' => 'main_oeuvre', 'unit' => 'u', 'tax_rate' => 20])]);
    }

    public function store(Request $request): RedirectResponse
    {
        CatalogItem::create($this->validated($request));

        return redirect()->route('catalog.index')->with('status', 'Prestation ajoutée au catalogue.');
    }

    public function edit(CatalogItem $catalog): View
    {
        return view('catalog.edit', ['item' => $catalog]);
    }

    public function update(Request $request, CatalogItem $catalog): RedirectResponse
    {
        $catalog->update($this->validated($request));

        return redirect()->route('catalog.index')->with('status', 'Prestation mise à jour.');
    }

    public function destroy(CatalogItem $catalog): RedirectResponse
    {
        $catalog->delete();

        return redirect()->route('catalog.index')->with('status', 'Prestation supprimée.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'trade'      => ['required', Rule::in(array_keys(CatalogItem::TRADES))],
            'line_type'  => ['required', Rule::in(array_keys(CatalogItem::LINE_TYPES))],
            'label'      => ['required', 'string', 'max:255'],
            'unit'       => ['required', 'string', 'max:20'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'tax_rate'   => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
    }
}
