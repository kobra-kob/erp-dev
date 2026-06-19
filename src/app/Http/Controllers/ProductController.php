<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockReplenishmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        return view('products.create', [
            'product' => new Product(['unit' => 'u', 'min_stock' => 1, 'tax_rate' => 20, 'kind' => 'purchased', 'is_sellable' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image_path'] = $this->storeImage($request);

        Product::create($data);

        return redirect()->route('products.index')->with('status', 'Produit ajouté.');
    }

    /** Fiche produit détaillée. */
    public function show(Product $product): View
    {
        $product->load(['purchaseOrders' => fn ($q) => $q->limit(5)]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);

        if ($path = $this->storeImage($request)) {
            $this->deleteImage($product);
            $data['image_path'] = $path;
        }

        $product->update($data);

        return redirect()->route('products.show', $product)->with('status', 'Produit mis à jour.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->deleteImage($product);
        $product->delete();

        return redirect()->route('products.index')->with('status', 'Produit supprimé.');
    }

    /** Enregistre la photo téléversée (disque public) et renvoie son chemin, ou null. */
    private function storeImage(Request $request): ?string
    {
        return $request->hasFile('image')
            ? $request->file('image')->store('products', 'public')
            : null;
    }

    /** Supprime l'éventuelle photo existante du disque. */
    private function deleteImage(Product $product): void
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
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
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'reference'        => ['nullable', 'string', 'max:100'],
            'category'         => ['nullable', 'string', 'max:100'],
            'kind'             => ['nullable', \Illuminate\Validation\Rule::in(array_keys(Product::KINDS))],
            'unit'             => ['required', 'string', 'max:20'],
            'purchase_price'   => ['required', 'numeric', 'min:0'],
            'sale_price'       => ['required', 'numeric', 'min:0'],
            'tax_rate'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stock'            => ['required', 'numeric', 'min:0'],
            'min_stock'        => ['required', 'numeric', 'min:0'],
            'supplier_name'    => ['nullable', 'string', 'max:255'],
            'supplier_email'   => ['nullable', 'email', 'max:255'],
            'reorder_quantity' => ['nullable', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string'],
            'description'      => ['nullable', 'string'],
            'image'            => ['nullable', 'image', 'max:4096'],
        ]);

        $data['is_sellable'] = $request->boolean('is_sellable');
        $data['kind'] ??= 'purchased';
        $data['tax_rate'] ??= 20;
        unset($data['image']); // géré séparément (upload)

        return $data;
    }
}
