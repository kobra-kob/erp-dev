<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockReplenishmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $lowOnly = $request->boolean('low');

        $products = Product::query()
            ->when($search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")->orWhere('reference', 'like', "%{$search}%");
            }))
            ->when($lowOnly, fn ($q) => $q->whereColumn('stock', '<=', 'min_stock'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $lowCount = Product::whereColumn('stock', '<=', 'min_stock')->count();

        return view('products.index', compact('products', 'search', 'lowOnly', 'lowCount'));
    }

    public function create(): View
    {
        return view('products.create', ['product' => new Product(['unit' => 'u', 'min_stock' => 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Product::create($this->validated($request));

        return redirect()->route('products.index')->with('status', 'Produit ajouté.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request));

        return redirect()->route('products.index')->with('status', 'Produit mis à jour.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('status', 'Produit supprimé.');
    }

    /** Entrée/sortie rapide de stock (+/-). */
    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'delta' => ['required', 'numeric'],
        ]);

        $product->stock = max(0, (float) $product->stock + (float) $data['delta']);
        $product->save();

        return back()->with('status', 'Stock mis à jour : ' . $product->name . ' = ' . rtrim(rtrim(number_format($product->stock, 2, ',', ' '), '0'), ','));
    }

    /** Passe une commande de réapprovisionnement pour un produit. */
    public function replenish(StockReplenishmentService $service, Product $product): RedirectResponse
    {
        if (! $product->canReorder()) {
            return back()->withErrors(['stock' => 'Réappro impossible : stock suffisant ou e-mail fournisseur manquant.']);
        }

        $order = $service->replenish($product, 'manual');

        return back()->with('status', "Commande passée : {$order->quantity} {$product->unit} de {$product->name} (fournisseur prévenu, stock réapprovisionné).");
    }

    /** Réapprovisionne tous les produits en stock faible (fournisseur connu). */
    public function replenishAll(StockReplenishmentService $service): RedirectResponse
    {
        $orders = $service->replenishLowStock('manual');

        if ($orders->isEmpty()) {
            return back()->withErrors(['stock' => 'Aucun produit réapprovisionnable (e-mail fournisseur manquant ?).']);
        }

        return back()->with('status', $orders->count() . ' commande(s) de réapprovisionnement passée(s).');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'reference'        => ['nullable', 'string', 'max:100'],
            'unit'             => ['required', 'string', 'max:20'],
            'purchase_price'   => ['required', 'numeric', 'min:0'],
            'sale_price'       => ['required', 'numeric', 'min:0'],
            'stock'            => ['required', 'numeric', 'min:0'],
            'min_stock'        => ['required', 'numeric', 'min:0'],
            'supplier_name'    => ['nullable', 'string', 'max:255'],
            'supplier_email'   => ['nullable', 'email', 'max:255'],
            'reorder_quantity' => ['nullable', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string'],
        ]);
    }
}
