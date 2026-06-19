@csrf
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Nom <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="form-control @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Référence</label>
                <input type="text" name="reference" value="{{ old('reference', $product->reference) }}" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Catégorie</label>
                <input type="text" name="category" value="{{ old('category', $product->category) }}" class="form-control" placeholder="Ex : Quincaillerie, Menuiserie…">
            </div>
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <select name="kind" class="form-select">
                    @foreach(\App\Models\Product::KINDS as $val => $label)
                        <option value="{{ $val }}" @selected(old('kind', $product->kind ?? 'purchased') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_sellable" name="is_sellable" value="1"
                           @checked(old('is_sellable', $product->is_sellable ?? true))>
                    <label class="form-check-label" for="is_sellable">Vendable (proposé dans les devis/factures)</label>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Unité</label>
                <input type="text" name="unit" value="{{ old('unit', $product->unit) }}" class="form-control" placeholder="u, m, kg…">
            </div>
            <div class="col-md-3">
                <label class="form-label">Prix d'achat (€)</label>
                <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price ?? 0) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Prix de vente (€)</label>
                <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? 0) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">TVA (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', $product->tax_rate ?? 20) }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Stock actuel</label>
                <input type="number" step="0.01" min="0" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Stock minimum (alerte)</label>
                <input type="number" step="0.01" min="0" name="min_stock" value="{{ old('min_stock', $product->min_stock ?? 0) }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Photo du produit</label>
                <input type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if($product->imageUrl())
                    <div class="mt-2 d-flex align-items-center gap-2">
                        <img src="{{ $product->imageUrl() }}" alt="" class="rounded border" style="height:64px;width:64px;object-fit:cover;">
                        <span class="text-muted small">Photo actuelle — choisir un fichier la remplacera.</span>
                    </div>
                @endif
            </div>

            <div class="col-12"><hr class="my-1"><span class="text-muted small text-uppercase">Réapprovisionnement fournisseur</span></div>
            <div class="col-md-5">
                <label class="form-label">Fournisseur</label>
                <input type="text" name="supplier_name" value="{{ old('supplier_name', $product->supplier_name) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">E-mail fournisseur</label>
                <input type="email" name="supplier_email" value="{{ old('supplier_email', $product->supplier_email) }}"
                       class="form-control @error('supplier_email') is-invalid @enderror" placeholder="commande@fournisseur.fr">
                @error('supplier_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Requis pour la commande automatique.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Quantité de réappro</label>
                <input type="number" step="0.01" min="0" name="reorder_quantity" value="{{ old('reorder_quantity', $product->reorder_quantity ?? 0) }}" class="form-control">
            </div>

            <div class="col-12"><hr class="my-1"><span class="text-muted small text-uppercase">Fiche produit</span></div>
            <div class="col-12">
                <label class="form-label">Description (fiche produit)</label>
                <textarea name="description" rows="3" class="form-control" placeholder="Caractéristiques, matériaux, dimensions, conseils d'utilisation…">{{ old('description', $product->description) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Notes internes</label>
                <textarea name="notes" rows="2" class="form-control">{{ old('notes', $product->notes) }}</textarea>
            </div>
        </div>
    </div>
</div>
