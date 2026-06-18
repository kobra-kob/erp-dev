@csrf
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                <input type="text" name="label" value="{{ old('label', $expense->label) }}" required
                       class="form-control @error('label') is-invalid @enderror" placeholder="Ex : Plein gasoil camionnette">
                @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Montant TTC (€) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $expense->amount) }}" required
                       class="form-control @error('amount') is-invalid @enderror">
                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">TVA %</label>
                <select name="vat_rate" class="form-select">
                    @foreach(['20','10','5.5','0'] as $r)
                        <option value="{{ $r }}" @selected((float) old('vat_rate', $expense->vat_rate ?? 20) === (float) $r)>{{ $r }} %</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Catégorie</label>
                <select name="category" class="form-select">
                    @foreach(\App\Models\Expense::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" @selected(old('category', $expense->category) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="spent_at" value="{{ old('spent_at', optional($expense->spent_at)->format('Y-m-d')) }}" required class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Fournisseur</label>
                <input type="text" name="supplier" value="{{ old('supplier', $expense->supplier) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Chantier (optionnel)</label>
                <select name="project_id" class="form-select">
                    <option value="">— Aucun —</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected(old('project_id', $expense->project_id) == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Devis rattaché (matériel / déplacement)</label>
                <select name="quote_id" class="form-select">
                    <option value="">— Aucun —</option>
                    @foreach($quotes as $q)
                        <option value="{{ $q->id }}" @selected(old('quote_id', $expense->quote_id) == $q->id)>{{ $q->number }} — {{ $q->client?->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Liaison stock : une dépense « Matériel » liée à un produit réapprovisionne le stock --}}
            <div class="col-md-8">
                <label class="form-label">Produit (stock) — pour le matériel</label>
                <select name="product_id" class="form-select">
                    <option value="">— Aucun —</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}" @selected(old('product_id', $expense->product_id) == $prod->id)>{{ $prod->name }} ({{ rtrim(rtrim(number_format($prod->stock,2,',',' '),'0'),',') }} {{ $prod->unit }} en stock)</option>
                    @endforeach
                </select>
                <div class="form-text">Si renseigné avec une quantité, le stock du produit est augmenté automatiquement.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Quantité (réappro stock)</label>
                <input type="number" step="0.01" min="0" name="quantity" value="{{ old('quantity', $expense->quantity) }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Justificatif (image ou PDF)</label>
                <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror">
                @error('receipt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if($expense->hasReceipt())
                    <div class="form-text">Actuel : {{ $expense->receipt_name }} (remplacé si vous en choisissez un nouveau).</div>
                @endif
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-control">{{ old('notes', $expense->notes) }}</textarea>
            </div>
        </div>
    </div>
</div>
