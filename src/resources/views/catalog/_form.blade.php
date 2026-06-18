@csrf
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Prestation <span class="text-danger">*</span></label>
                <input type="text" name="label" value="{{ old('label', $item->label) }}" required
                       class="form-control @error('label') is-invalid @enderror" placeholder="Ex : Pose de prise électrique">
                @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Métier</label>
                <select name="trade" class="form-select">
                    @foreach(\App\Models\CatalogItem::TRADES as $k => $l)
                        <option value="{{ $k }}" @selected(old('trade', $item->trade) === $k)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nature</label>
                <select name="line_type" class="form-select">
                    @foreach(\App\Models\CatalogItem::LINE_TYPES as $k => $l)
                        <option value="{{ $k }}" @selected(old('line_type', $item->line_type) === $k)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Unité</label>
                <input type="text" name="unit" value="{{ old('unit', $item->unit) }}" class="form-control" placeholder="u, m, m², h…">
            </div>
            <div class="col-md-4">
                <label class="form-label">Prix unitaire HT (€)</label>
                <input type="number" step="0.01" min="0" name="unit_price" value="{{ old('unit_price', $item->unit_price ?? 0) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">TVA %</label>
                <select name="tax_rate" class="form-select">
                    @foreach(['20','10','5.5','0'] as $r)
                        <option value="{{ $r }}" @selected((float) old('tax_rate', $item->tax_rate ?? 20) === (float) $r)>{{ $r }} %</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
