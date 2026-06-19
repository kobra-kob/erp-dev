@php
    // Lignes initiales : priorité aux anciennes saisies (erreur de validation),
    // sinon celles du document existant, sinon vide (JS ajoutera une ligne).
    $rows = old('lines');
    if ($rows === null) {
        $rows = $document->exists
            ? $document->lines->map(fn ($l) => [
                'type' => $l->type, 'description' => $l->description,
                'quantity' => $l->quantity, 'unit_price' => $l->unit_price, 'tax_rate' => $l->tax_rate,
            ])->values()->all()
            : [];
    }
    $types = ['main_oeuvre' => "Main d'œuvre", 'materiel' => 'Matériel', 'deplacement' => 'Déplacement', 'autre' => 'Autre'];
    $catalog = $catalog ?? collect();
    $products = $products ?? collect();
@endphp

@error('lines')<div class="alert alert-danger py-2">Ajoutez au moins une ligne de prestation.</div>@enderror

@if($catalog->isNotEmpty())
    <div class="input-group input-group-sm mb-2" style="max-width:560px;">
        <span class="input-group-text"><i class="bi bi-bricks me-1"></i>Catalogue</span>
        <select id="catalogPicker" class="form-select">
            <option value="">— Choisir une prestation… —</option>
            @foreach($catalog->groupBy('trade') as $trade => $group)
                <optgroup label="{{ \App\Models\CatalogItem::TRADES[$trade] ?? 'Général' }}">
                    @foreach($group as $ci)
                        <option value="{{ $ci->id }}"
                                data-type="{{ $ci->line_type }}"
                                data-label="{{ $ci->label }}"
                                data-price="{{ $ci->unit_price }}"
                                data-tax="{{ $ci->tax_rate }}">{{ $ci->label }} — @eur($ci->unit_price)</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        <button type="button" class="btn btn-outline-primary" id="addFromCatalog"><i class="bi bi-plus-lg me-1"></i>Insérer</button>
    </div>
@endif

@if($products->isNotEmpty())
    <div class="input-group input-group-sm mb-2" style="max-width:560px;">
        <span class="input-group-text"><i class="bi bi-box-seam me-1"></i>Produit</span>
        <select id="productPicker" class="form-select">
            <option value="">— Choisir un produit du stock… —</option>
            @foreach($products->groupBy(fn ($p) => $p->category ?: 'Sans catégorie') as $cat => $group)
                <optgroup label="{{ $cat }}">
                    @foreach($group as $p)
                        <option value="{{ $p->id }}"
                                data-label="{{ $p->name }}{{ $p->reference ? ' ('.$p->reference.')' : '' }}"
                                data-price="{{ $p->sale_price }}"
                                data-tax="{{ $p->tax_rate }}">{{ $p->name }} — @eur($p->sale_price)</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        <button type="button" class="btn btn-outline-primary" id="addFromProduct"><i class="bi bi-plus-lg me-1"></i>Insérer</button>
    </div>
@endif

<div class="table-responsive">
    <table class="table align-middle" id="lineTable">
        <thead class="table-light">
            <tr>
                <th style="width:140px;">Nature</th>
                <th>Description</th>
                <th style="width:90px;">Qté</th>
                <th style="width:120px;">P.U. HT (€)</th>
                <th style="width:90px;">TVA %</th>
                <th style="width:120px;" class="text-end">Total HT</th>
                <th style="width:40px;"></th>
            </tr>
        </thead>
        <tbody id="lineRows">
            @foreach($rows as $i => $row)
                <tr class="line-row">
                    <td>
                        <select name="lines[{{ $i }}][type]" class="form-select form-select-sm">
                            @foreach($types as $val => $label)
                                <option value="{{ $val }}" @selected(($row['type'] ?? '') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="lines[{{ $i }}][description]" value="{{ $row['description'] ?? '' }}" class="form-control form-control-sm" required></td>
                    <td><input type="number" step="0.01" min="0" name="lines[{{ $i }}][quantity]" value="{{ $row['quantity'] ?? 1 }}" class="form-control form-control-sm l-qty"></td>
                    <td><input type="number" step="0.01" min="0" name="lines[{{ $i }}][unit_price]" value="{{ $row['unit_price'] ?? 0 }}" class="form-control form-control-sm l-price"></td>
                    <td><input type="number" step="0.01" min="0" max="100" name="lines[{{ $i }}][tax_rate]" value="{{ $row['tax_rate'] ?? 20 }}" class="form-control form-control-sm l-tax"></td>
                    <td class="text-end l-line-ht">0,00</td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger l-remove"><i class="bi bi-trash"></i></button></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addLine"><i class="bi bi-plus-lg me-1"></i>Ajouter une ligne</button>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="row justify-content-end">
    <div class="col-md-5 col-lg-4">
        <table class="table table-sm">
            <tr><td class="text-muted">Total HT</td><td class="text-end fw-semibold" id="totHt">0,00 €</td></tr>
            <tr><td class="text-muted">TVA</td><td class="text-end" id="totTax">0,00 €</td></tr>
            <tr class="border-top"><td class="fw-bold">Total TTC</td><td class="text-end fw-bold fs-5" id="totTtc">0,00 €</td></tr>
        </table>
    </div>
</div>

{{-- Modèle de ligne pour les ajouts JS --}}
<template id="lineTemplate">
    <tr class="line-row">
        <td>
            <select name="lines[__I__][type]" class="form-select form-select-sm">
                @foreach($types as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
            </select>
        </td>
        <td><input type="text" name="lines[__I__][description]" class="form-control form-control-sm" required></td>
        <td><input type="number" step="0.01" min="0" name="lines[__I__][quantity]" value="1" class="form-control form-control-sm l-qty"></td>
        <td><input type="number" step="0.01" min="0" name="lines[__I__][unit_price]" value="0" class="form-control form-control-sm l-price"></td>
        <td><input type="number" step="0.01" min="0" max="100" name="lines[__I__][tax_rate]" value="20" class="form-control form-control-sm l-tax"></td>
        <td class="text-end l-line-ht">0,00</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger l-remove"><i class="bi bi-trash"></i></button></td>
    </tr>
</template>

@push('scripts')
<script>
(function () {
    const tbody = document.getElementById('lineRows');
    const tpl = document.getElementById('lineTemplate');
    let counter = {{ count($rows) }};

    const eur = n => n.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function recompute() {
        let ht = 0, tax = 0;
        tbody.querySelectorAll('.line-row').forEach(row => {
            const q = parseFloat(row.querySelector('.l-qty').value) || 0;
            const p = parseFloat(row.querySelector('.l-price').value) || 0;
            const t = parseFloat(row.querySelector('.l-tax').value) || 0;
            const lineHt = q * p;
            ht += lineHt;
            tax += lineHt * t / 100;
            row.querySelector('.l-line-ht').textContent = eur(lineHt);
        });
        document.getElementById('totHt').textContent = eur(ht) + ' €';
        document.getElementById('totTax').textContent = eur(tax) + ' €';
        document.getElementById('totTtc').textContent = eur(ht + tax) + ' €';
    }

    function addRow(values) {
        const html = tpl.innerHTML.replace(/__I__/g, counter++);
        tbody.insertAdjacentHTML('beforeend', html);
        const row = tbody.lastElementChild;
        if (values) {
            if (values.type)  row.querySelector('select').value = values.type;
            if (values.label) row.querySelector('input[type="text"]').value = values.label;
            if (values.price !== undefined) row.querySelector('.l-price').value = values.price;
            if (values.tax !== undefined)   row.querySelector('.l-tax').value = values.tax;
        }
        recompute();
    }

    document.getElementById('addLine').addEventListener('click', () => addRow());

    // Insertion d'une prestation du catalogue (module Bâtiment)
    const catalogBtn = document.getElementById('addFromCatalog');
    if (catalogBtn) {
        catalogBtn.addEventListener('click', function () {
            const sel = document.getElementById('catalogPicker');
            const opt = sel.selectedOptions[0];
            if (!opt || !opt.value) return;
            addRow({
                type: opt.dataset.type,
                label: opt.dataset.label,
                price: opt.dataset.price,
                tax: opt.dataset.tax,
            });
            sel.value = '';
        });
    }

    // Insertion d'un produit du stock (ligne « matériel »)
    const productBtn = document.getElementById('addFromProduct');
    if (productBtn) {
        productBtn.addEventListener('click', function () {
            const sel = document.getElementById('productPicker');
            const opt = sel.selectedOptions[0];
            if (!opt || !opt.value) return;
            addRow({
                type: 'materiel',
                label: opt.dataset.label,
                price: opt.dataset.price,
                tax: opt.dataset.tax,
            });
            sel.value = '';
        });
    }
    tbody.addEventListener('input', recompute);
    tbody.addEventListener('click', e => {
        if (e.target.closest('.l-remove')) {
            e.target.closest('.line-row').remove();
            recompute();
        }
    });

    if (!tbody.querySelector('.line-row')) addRow();
    recompute();
})();
</script>
@endpush
